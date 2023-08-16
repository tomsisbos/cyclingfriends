<?php

class Amount {

    public $currency = 'jpy';
    public $currency_symbol = '¥';
    public $products = [];
    public $products_number = 0;
    public $discounts = [];
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
     * Generate a discount from scratch and add it
     * @param int $price
     */
    public function addDiscount ($price, $name) {
        array_push($this->discounts, new Discount($price, $name));
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
        foreach ($this->discounts as $discount) $total += $discount->price;
        return $total;
    }

    public function getProductsNumber () {
        $products_number = 0;
        foreach ($this->products as $product) $products_number + $product->quantity;
        return $products_number;
    }

    /**
     * Use user_id's user points to discount this amount
     * @param int $user_id
     */
    public function useCFPoints ($user_id) {
        $user = new User($user_id);
        $cf_points = $user->getCFPoints();
        if ($cf_points > $this->getTotal()) $cf_points = $this->getTotal() - 500;
        $this->addDiscount($cf_points, 'ポイント利用分');
    }

}