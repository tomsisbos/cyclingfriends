<?php

class LngLat {
    
    public $lng;
    public $lat;
    
    function __construct ($lng, $lat) {
        $this->lng = floatval($lng);
        $this->lat = floatval($lat);
    }

}