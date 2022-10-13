<?php

class Autoloader {
    
    /**
     * Register autoloader
     */
    static function register () {
        spl_autoload_register([__CLASS__, 'autoload']);
        spl_autoload_register([__CLASS__, 'phpgeo']);
    }

    /**
     * Include the Class file to load
     * @param $class string name of the Class to load
     */
    static function autoload ($class) {
        require_once $_SERVER["DOCUMENT_ROOT"] . '/class/'. $class . '.php';
    }

    static function phpgeo ($class) {
        require_once $_SERVER["DOCUMENT_ROOT"] . '/class/Location/'. $class . '.php';
    }

}