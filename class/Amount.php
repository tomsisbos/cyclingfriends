<?php

class Amount {

    public $currency = 'jpy';
    public $currency_symbol = '¥';
    public $products = [];
    public $products_number = 0;
    public $total;

    function __construct () {
    }

    /**
     * Generate a product from scratch and add it
     * @param string $name
     * @param int $price
     * @param int $quantity
     */
    public function addProduct ($name, $price, $quantity = 1) {
        array_push($this->products, new Product($name, $price, $quantity));
        $this->total = $this->getTotal();
        $this->products_number = $this->getProductsNumber();
    }

    /**
     * Add a product directly from instance
     * @param Product $product
     */
    public function add ($product) {
        array_push($this->products, $product);
        $this->total = $this->getTotal();
        $this->products_number = $this->getProductsNumber();
    }

    public function removeProduct ($name, $number = 1) {
        $removed = false;
        for ($i = 0; $i < count($this->products); $i++) {
            if ($products[$i]->name == $name) {
                if ($products[$i]->quantity > 1) $products[$i]->quantity -= 1;
                else unset($products[$i]);
            }
        }
    }

    public function getTotal () {
        $total = 0;
        foreach ($this->products as $product) $total += ($product->price * $product->quantity);
        return $total;
    }

    public function getProductsNumber () {
        $products_number = 0;
        foreach ($this->products as $product) $products_number + $product->quantity;
        return $products_number;
    }

}