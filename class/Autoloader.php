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
        $parent_directory = basename($_SERVER['DOCUMENT_ROOT']);
        $class_folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen($parent_directory)) . '/class/';
        require_once $class_folder . $class . '.php';
    }

    static function phpgeo ($class) {
        $parent_directory = basename($_SERVER['DOCUMENT_ROOT']);
        $class_folder = substr($_SERVER['DOCUMENT_ROOT'], - strlen($parent_directory)) . '/class/';
        require_once $class_folder . 'Location/' . $class . '.php';
    }

}