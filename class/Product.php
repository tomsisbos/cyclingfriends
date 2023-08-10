<?php

class Product {

    public $currency = 'jpy';
    public $currency_symbol = '¥';
    public $name;
    public $price;
    public $quantity;

    function __construct ($name, $price, $quantity = 1) {
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }
}