<?php

class Trackpoint {

    /**
     * @var LngLat
     */
    public $lngLat;
    
    /**
     * @var DateTime|int
     */
    public $time;
    
    /**
     * @var int
     */
    public $elevation;
    
    /**
     * @var float
     */
    public $distance;

    /**
     * @var int
     */
    public $temperature = null;

    /**
     * @var int
     */
    public $heart_rate = null;

    /**
     * @var int
     */
    public $cadence = null;

    /**
     * @var int
     */
    public $speed = null;

    /**
     * @var int
     */
    public $power = null;
    
    /**
     * @param array $data An array containing properties as indexes
     */
    function __construct ($data) {
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }
    }
}