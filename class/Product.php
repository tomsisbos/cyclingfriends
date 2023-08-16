<?php

class Product {

    protected $default_name = '商品';
    public $currency = 'jpy';
    public $currency_symbol = '¥';
    public $name;
    public $price;
    public $quantity;

    function __construct ($name, $price, $quantity = 1) {
        if ($name == null) $this->name = $this->default_name;
        else $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }
}