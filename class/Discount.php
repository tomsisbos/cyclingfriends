<?php

class Discount extends Product {

    protected $default_name = '割引';

    function __construct ($price, $name = null) {
        parent::__construct($name, -1 * abs($price), 1);
    }
}