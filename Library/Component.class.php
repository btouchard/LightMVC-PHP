<?php
namespace Library;

use Library\Utils\Debug;
use Library\Utils\StringUtils;

class Component {

    protected $app;
    private $using = array();
    private $magic = array();
    protected $vars = array();

    public function __construct(Application $app) {
        $this->app = $app;
    }

    private function getUsingClass($name) {
        $class = 'App\\Model\\' . $name;
        if (exist($class)) return $class;
        $class = 'App\\Managers\\' . $name;
        if (exist($class)) return $class;
        return null;
    }

    public function __isset($name) {
        return array_key_exists($name, $this->magic) || array_key_exists($name, $this->using);
    }
    public function __get($name) {
        if (array_key_exists($name, $this->magic)) {
            return $this->magic[$name];
        }
        if (!array_key_exists($name, $this->using) && ($class = $this->getUsingClass($name)) != null) {
            $this->using[$name] = $class;
        }
        if (array_key_exists($name, $this->using)) {
            if (gettype($this->using[$name]) == 'string') $this->using[$name] = new $this->using[$name]($this->app);
            return $this->using[$name];
        }
        return null;
    }
    public function __set($name, $value) {
        if (!array_key_exists($name, $this->using)) {
            $this->magic[$name] = $value;
        }
    }

    public function set($var, $value) {
        if (!is_string($var) || is_numeric($var) || empty($var)) {
            throw new \InvalidArgumentException('Le nom de la variable doit être une chaine de caractère non nulle');
        }
        $this->vars[$var] = $value;
    }

    public function getCaller() {
        //get the trace
        $trace = debug_backtrace();
        //Debug::log($trace);
        // Get the class that is asking for who awoke it
        $class = $trace[1]['class'];
        // +1 to i cos we have to account for calling this function
        for ( $i=1; $i<count( $trace ); $i++ ) {
            if ( isset( $trace[$i] ) && isset( $trace[$i]['class'] ) ) // is it set?
            if ( !StringUtils::startWith('Library', $trace[$i]['class']) && $class != $trace[$i]['class'] ) // is it a different class
                return $trace[$i];
        }
    }

    public function getFields() {
        $names = array();
        $array = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($array as $obj) $names[] = $obj->name;
        foreach (array_keys($this->magic) as $key) $names[] = $key;
        return $names;
    }
}