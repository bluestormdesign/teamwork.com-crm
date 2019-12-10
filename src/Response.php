<?php

namespace BluestormDesign\TeamworkCrm;

use ArrayObject;
use BluestormDesign\Helpers\Str;
use Exception;

class Response
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->data = $params;
    }

    /**
     * @return array
     */
    public function getDatum()
    {
        return $this->data;
    }

    /**
     * @param null|string $name
     * @return mixed
     */
    public function getData($name = null)
    {
        return isset($this->data[$name]) || array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    /**
     * @param array $data
     * @return Response
     */
    public function setDatum($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return Response
     */
    public function setData($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }
    /**
     * @param $data
     * @param array $headers
     * @return Response|boolean
     * @throws \Exception
     */
    public function parse($data, array $headers)
    {
        $source = json_decode($data);

        $errors = $this->getJsonErrors();
        $this->string = $data;
        if (!$errors) {
            if (!(
                $headers['Status'] === 201 ||
                $headers['Status'] === 200 ||
                $headers['Status'] === 409 ||
                $headers['Status'] === 422 ||
                $headers['Status'] === 400
            )) {
                throw new Exception([
                    'Message' => $errors,
                    'Response' => $data,
                    'Headers' => $headers
                ]);
            }

            if ($headers['Status'] === 201 || $headers['Status'] === 200) {
                return $source;
                switch ($headers['Method']) {
                    case 'UPLOAD':
                        return empty($source->pendingFile->ref) ? null : (string) $source->pendingFile->ref;
                    case 'POST':
                        if (!empty($headers['id'])) {
                            return (int) $headers['id'];
                        } elseif (!empty($source->id)) {
                            return (int) $source->id;
                        }
                    // no break
                    case 'PUT':
                        return isset($source->id) ? $source->id : true;
                    case 'DELETE':
                        var_dump('ff32f23f32');
                        return true;

                    default:
                        if (!empty($source->STATUS)) {
                            unset($source->STATUS);
                        }
                        if (!empty($source->project->files)) {
                            $source = $source->project->files;
                        } elseif (!empty($source->project->notebooks)) {
                            $source = $source->project->notebooks;
                        } elseif (!empty($source->project->links)) {
                            $source = $source->project->links;
                        } elseif (
                            !empty($source->messageReplies) &&
                            preg_match('!messageReplies/(\d+)!', $headers['X-Action'])
                        ) {
                            $source = current($source->messageReplies);
                        } elseif (
                            !empty($source->people) &&
                            preg_match('!projects/(\d+)/people/(\d+)!', $headers['X-Action'])
                        ) {
                            $source = current($source->people);
                        } elseif (
                            !empty($source->project) &&
                            preg_match('!projects/(\d+)/notebooks!', $headers['X-Action'])
                        ) {
                            $source = [];
                        } elseif (
                            isset($source->cards) &&
                            preg_match('!portfolio/columns/(\d+)/cards!', $headers['X-Action'])
                        ) {
                            $source = $source->cards;
                        } else {
                            $source = current($source);
                        }
                        if ($headers['X-Action'] === 'links' || $headers['X-Action'] === 'notebooks') {
                            $_source = [];
                            $wrapper = $headers['X-Action'];
                            foreach ($source as $project) {
                                foreach ($project->$wrapper as $object) {
                                    $_source[] = $object;
                                }
                            }
                            $source = $_source;
                        } elseif (strpos($headers['X-Action'], 'time_entries') !== false && !$source) {
                            $source = [];
                        }
                        $this->headers = $headers;
                        $this->string = json_encode($source);

                        $this->data = self::camelizeObject($source);

                        if (!empty($this->data->id)) {
                            $this->data->id = (int) $this->data->id;
                        }

                        return $this;
                }
            } elseif (!empty($source->MESSAGE)) {
                $errors = $source->MESSAGE;
            } else {
                $errors = null;
            }
        }

        throw new Exception(implode(', ', [
            'Message'  => $errors,
            'Response' => $data,
            'Headers'  => $headers
        ]));
    }

    /**
     * @return string
     */
    protected function getContent()
    {
        $object = json_decode($this->string);

        return json_encode($object, JSON_PRETTY_PRINT);
    }

    /**
     * @param array $source
     *
     * @return ArrayObject
     */
    protected static function camelizeObject($source)
    {
        $destination = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
        foreach ($source as $key => $value) {
            if (ctype_upper($key)) {
                $key = strtolower($key);
            }
            $key = Str::camel($key);
            $destination->$key = is_scalar($value) ? $value : self::camelizeObject($value);
        }

        return $destination;
    }

    /**
     * @codeCoverageIgnore
     */
    private function getJsonErrors()
    {
        $errorCode = json_last_error();
        if (!$errorCode) {
            return;
        }

        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }

        switch ($errorCode) {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
        }
    }
}
