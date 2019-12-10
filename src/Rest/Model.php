<?php

namespace BluestormDesign\TeamworkCrm\Rest;

use BluestormDesign\TeamworkCrm\Rest;

abstract class Model
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var Rest|null
     */
    protected $rest = null;

    /**
     * @var string|null
     */
    protected $parent = null;

    /**
     * @var string|null
     */
    protected $action = null;

    /**
     * @var array
     */
    protected $fields = [];
    /**
     *
     * @var string|null
     */
    private $hash = null;

    /**
     * Model constructor.
     *
     * @param string $url
     * @param string $key
     * @param string $class
     * @param string $hash
     *
     * @throws \Exception
     */
    final private function __construct($url, $key, $class, $hash)
    {
        $this->rest = new Rest($url, $key);
        $this->hash = $hash;
        $pieces = explode('\\', $class);
        $this->parent = array_pop($pieces);
        if (method_exists($this, 'init')) {
            $this->init();
        }
        if (null === $this->action) {
            $this->action = strtolower(str_replace('-', '_', $this->parent));
            // pluralize
            if (substr($this->action, -1) === 'y') {
                $this->action = substr($this->action, 0, -1) . 'ies';
            } else {
                $this->action .= 's';
            }
        }

        $this->rest->getRequest()
                    ->setParent($this->parent)
                    ->setFields($this->fields);
    }

    /**
     * @codeCoverageIgnore
     */
    final public function __destruct()
    {
        unset(self::$instances[$this->hash]);
    }

    /**
     * @codeCoverageIgnore
     */
    final protected function __clone()
    {
    }

    /**
     * @param $url
     * @param string $key
     *
     * @return \TeamWorkPm\Model
     */
    final public static function getInstance($url, $key)
    {
        $class = get_called_class();
        $hash = md5($class . '-' . $url . '-' . $key);
        if (!isset(self::$instances[$hash])) {
            self::$instances[$hash] = new $class($url, $key, $class, $hash);
        }

        return self::$instances[$hash];
    }
}
