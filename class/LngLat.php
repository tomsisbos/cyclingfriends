<?php

class LngLat {
    
    public $lng;
    public $lat;
    
    function __construct ($lng = NULL, $lat = NULL) {
        if ($lng) $this->lng = floatval($lng);
        if ($lat) $this->lat = floatval($lat);
    }

    /**
     * Converts coordinates to a WKT formatted point
     */
    public function toWKT () {
        return 'POINT(' .$this->lng. ' ' .$this->lat. ')';
    }

    /**
     * Loads coordinates from a WKT formatted point
     */
    public function fromWKT ($point_wkt) {
        $point = geoPHP::load($point_wkt,'wkt');
        $this->lng = $point->getX();
        $this->lat = $point->getY();
    }

    public function getArray () {
        return [$this->lng, $this->lat];
    }

}