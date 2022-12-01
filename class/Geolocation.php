<?php

class Geolocation {

    public $city;
    public $prefecture;

    function __construct($city, $prefecture) {
        $this->city       = $city;
        $this->prefecture = $prefecture;
    }

    public function toString () {
        return $this->city . ' (' . $this->prefecture . ')';
    }

}