<?php

class Geolocation {

    public $city;
    public $prefecture;

    function __construct($city, $prefecture) {
        $this->city       = $city;
        $this->prefecture = $prefecture;
    }

    public function getString () {
        return $this->city . ' (' . $this->prefecture . ')';
    }

}