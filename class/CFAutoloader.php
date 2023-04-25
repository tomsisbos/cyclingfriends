<?php

class CFAutoloader {
    
    /**
     * Register autoloader
     */
    static function register () {
        spl_autoload_register([__CLASS__, 'autoload']);
        ///spl_autoload_register([__CLASS__, 'phpgeo']);
    }

    /**
     * Include the Class file to load
     * @param $class string name of the Class to load
     */
    static function autoload ($class) {
        $class_path = str_replace('\\', '/', $class); // replace namespace separators with directory separators in the relative class name
        $parent_directory = basename($_SERVER['DOCUMENT_ROOT']);
        $class_folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen($parent_directory)) . 'class/';
        if (file_exists($class_folder . $class_path . '.php')) {
            require_once $class_folder . $class_path . '.php';
            return true;
        }
    }

    /*static function phpgeo ($class) {
        
        $parent_directory = basename($_SERVER['DOCUMENT_ROOT']);
        $class_folder = substr($_SERVER['DOCUMENT_ROOT'], - strlen($parent_directory)) . 'class/';
        require_once $class_folder . 'Location/' . $class_path . '.php';
    }*/

}