<?php

namespace BluestormDesign\TeamworkCrm;

use Exception;
use stdClass;
use BluestormDesign\TeamworkCrm\Helpers\Str;

class Request
{
    private $method = null;
    private $action = null;
    protected $parent = null;
    private $fields = [];

    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    protected function getParent()
    {
        return $this->parent . ($this->actionInclude('/reorder') ? 's' : '');
    }

    /**
     * @param string $field
     * @param array|mixed $options
     * @param array $parameters
     * @return mixed|null
     * @throws Exception
     */
    protected function getValue(&$field, &$options, array $parameters)
    {
        $camelize = [
            'pending_file_attachments' => true,
            'date_format' => true,
            'send_welcome_email' => true,
            'receive_daily_reports' => true,
            'welcome_email_message' => true,
            'auto_give_project_access' => true,
            'open_id' => true,
            'user_language' => true,
            'pending_file_ref' => true,
            'new_company' => true,
        ];
        $yesNoBoolean = [
            'welcome_email_message',
            'send_welcome_email',
            'receive_daily_reports',
            'notes',
            'auto_give_project_access',
        ];
        $preserve = [
            'address_one' => true,
            'address_two' => true,
        ];
        $value = isset($parameters[$field]) ? $parameters[$field] : null;
        if (!is_array($options)) {
            $options = ['required' => $options, 'attributes' => []];
        }
        $isValueNull = null === $value;
        if (strtoupper($this->method) === 'POST' && $options['required']) {
            if ($isValueNull) {
                throw new Exception('Required field ' . $field);
            }
        }

        if (!$isValueNull && isset($options['validate']) && !in_array($value, $options['validate'])) {
            throw new Exception('Invalid value for field ' . $field);
        }

        if (isset($camelize[$field])) {
            if ($field === 'open_id') {
                $field = 'openID';
            } else {
                $field = Str::camel($field);
            }
        } elseif (!isset($preserve[$field])) {
            if ($field === 'company_id') {
                if ($this->action === 'projects') {
                    $field = Str::camel($field);
                } elseif ($this->action == 'people') {
                    $field = Str::dash($field);
                }
            } else {
                $field = Str::dash($field);
            }
        }
        return $value;
    }

    protected function actionInclude($value)
    {
        return false !== strrpos($this->action, $value);
    }

    public function getParameters($method, $parameters)
    {
        if ($parameters) {
            $this->method = $method;
            if ($method === 'GET') {
                if (is_array($parameters)) {
                    $parameters = http_build_query($parameters);
                }
            } elseif ($method === 'POST' || $method === 'PUT') {
                $parameters = $this->parseParameters($parameters);
            }
        } else {
            $parameters = null;
        }
        return $parameters;
    }

    protected function parseParameters($parameters)
    {
        if (!empty($parameters) && is_array($parameters)) {
            $object = new stdClass();
            $parent = $this->getParent();
            $object->$parent = new stdClass();
            $parent = $object->$parent;

            if ($this->actionInclude('/reorder')) {
                foreach ($parameters as $id) {
                    $item = new stdClass();
                    $item->id = $id;
                    $parent->{$this->parent}[] = $item;
                }
            } else {
                foreach ($this->fields as $field=>$options) {
                    $value = $this->getValue($field, $options, $parameters);
                    if (isset($options['attributes'])) {
                        foreach ($options['attributes'] as $name=>$type) {
                            if (null !== $value) {
                                if ($name === 'type') {
                                    if ($type === 'array') {
                                        if (is_string($value) ||
                                            is_numeric($value)) {
                                            $value = (array) $value;
                                        }
                                    } else {
                                        settype($value, $type);
                                    }
                                }
                            }
                        }
                    }
                    if (null !== $value) {
                        if (is_string($value)) {
                            $value = mb_encode_numericentity(
                                $value,
                                [0x80, 0xffff, 0, 0xffff],
                                'utf-8'
                            );
                        }
                        !empty($options['sibling']) ?
                            $object->$field = $value :
                            $parent->$field = $value;
                    }
                }
            }
            $parameters =  json_encode($object);
            $parameters = mb_decode_numericentity(
                $parameters,
                [0x80, 0xffff, 0, 0xffff],
                'utf-8'
            );
        } else {
            $parameters = '{}';
        }
var_dump($parameters);
        return $parameters;
    }
}
