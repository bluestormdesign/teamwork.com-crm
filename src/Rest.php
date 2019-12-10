<?php

namespace BluestormDesign\TeamworkCrm;

use \Exception;

class Rest
{
    const MAX_RETRIES_ON_FAILURE = 5;

    /**
     * @var string this is the API key
     */
    private $key = '';

    /**
     * @var string the url used to access the API
     */
    private $url = '';

    /**
     * @var Request
     */
    private $request = null;

    /**
     * @param string $url
     * @param string $key
     * @throws \Exception
     */
    public function __construct($url, $key)
    {
        if (!isset($url, $key)) {
            throw new Exception('Set your url and api key');
        }

        $this->key = $key;
        $this->url = $url;
        $this->request = new Request;
    }

    /**
     * Shortcut call get method to api
     *
     * @param string $action
     * @param mixed $request
     *
     * @return Response
     * @throws \Exception
     */
    public function get($action, $request = null)
    {
        return $this->execute('GET', $action, $request);
    }

    public function put($action, $request = null)
    {
        return $this->execute('PUT', $action, $request);
    }

    public function post($action, $request = null)
    {
        return $this->execute('POST', $action, $request);
    }

    public function delete($action)
    {
        return $this->execute('DELETE', $action, null);
    }

    /**
     * @param $action
     * @param null $request
     * @return mixed
     * @throws Exception
     */
    public function upload($action, $request = null)
    {
        return $this->execute('UPLOAD', $action, $request);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Call to api
     *
     * @param string $method
     * @param string $action
     * @param mixed $request
     * @return mixed
     * @throws \Exception
     */
    private function execute($method, $action, $request = null)
    {
        $url =  sprintf("%s%s.json", $this->url, $action);
        $headers = ['Authorization: Bearer '. $this->key];
        $request = $this->request->setAction($action)->getParameters($method, $request);
        $ch = static::initCurl($method, $url, $request, $headers);
        $i = 0;
        $status = null;
        while ($i < self::MAX_RETRIES_ON_FAILURE) {
            $data = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = $this->parseHeaders(substr($data, 0, $headerSize));

            if ($status === 400 && (int) $headers['x-rate-limit-remaining'] === 0) {
                $i ++;
                sleep(10);
            } else {
                break;
            }
        }

        $body = substr($data, $headerSize);
        $errorInfo = curl_error($ch);
        $error = curl_errno($ch);
        curl_close($ch);
        if ($error) {
            throw new Exception($errorInfo);
        }

        $headers['Status'] = $status;
        $headers['Method'] = $method;
        $headers['X-Url']  = $url;
        $headers['X-Request'] = $request;
        $headers['X-Action']  = $action;
        // for chrome use
        $headers['X-Authorization'] = 'Bearer ' . $this->key;

        $response = new Response(json_decode($body, true));

        return $response->parse($body, $headers);
    }

    /**
     * @param string $method
     * @param string $url
     * @param $params
     * @param $headers
     * @return false|resource
     */
    private static function initCurl($method, $url, $params, $headers)
    {
        $ch = curl_init();
        switch ($method) {
            case 'GET':
                if (!empty($params)) {
                    $url .= '?' . $params;
                }
                break;
            case 'UPLOAD':
                curl_setopt_array($ch, [
                    CURLOPT_POSTFIELDS => $params,
                    CURLOPT_POST => true,
                ]);
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
            case 'PUT':
            case 'POST':
                if ($method === 'POST') {
                    curl_setopt($ch, CURLOPT_POST, true);
                } else {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                }
                if ($params) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                }
                $headers = array_merge($headers, [
                    'Content-Type: application/json',
                    'Content-Length:' . strlen($params)
                ]);
                break;
        }
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        return $ch;
    }

    /**
     * Parse the headers that are returned from the teamwork API
     *
     * @param string $responseHeaders
     * @return array
     */
    private function parseHeaders($responseHeaders)
    {
        $headers = [];
        $responseHeaders = trim($responseHeaders);
        if ($responseHeaders) {
            $parts = explode("\n", $responseHeaders);
            foreach ($parts as $header) {
                $header = trim($header);
                if ($header && false !== strpos($header, ':')) {
                    list($name, $value) = explode(':', $header, 2);
                    $value = trim($value);
                    $name = trim($name);
                    if (isset($headers[$name])) {
                        if (is_array($headers[$name])) {
                            $headers[$name][] = $value;
                        } else {
                            $_val = $headers[$name];
                            $headers[$name] = [$_val, $value];
                        }
                    } else {
                        $headers[$name] = $value;
                    }
                }
            }
        }

        return $headers;
    }
}
