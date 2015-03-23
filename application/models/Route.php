<?php

class Route extends Zend_Controller_Router_Route
{
    public static function getInstance(Zend_Config $config)
    {
        $defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
        return new self($config->route, $defs);
    }

    public function __construct($route, $defaults = array())
    {
        $this->_route = trim($route, $this->_urlDelimiter);
        $this->_defaults = (array)$defaults;

    }

    public function match($path, $partial = false)
    {
        if ($path instanceof Zend_Controller_Request_Http) {
            $path = $path->getPathInfo();
        }

        $path = trim($path, $this->_urlDelimiter);
        $pathBits = explode($this->_urlDelimiter, $path);

        if (count($pathBits) != 1) {
            return false;
        }

        // check database for this user
        $result = Zend_Registry::get('db')->fetchRow('SELECT satici_id, kullanici_adi FROM tbl_satici WHERE kullanici_adi = ?', $pathBits[0]);
        if ($result) {
            // user found
            $values = $this->_defaults + $result;
            return $values;
        }

        return false;
    }

    public function assemble($data = array(), $reset = false, $encode = false)
    {
        return $data['username'];
    }
}