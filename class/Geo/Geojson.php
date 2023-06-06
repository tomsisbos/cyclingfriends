<?php

namespace Geo;

use Geo\Geometry;

class Geojson {

    /**
     * @var string
     */
    public $type = 'Feature';

    /**
     * @var array
     */
    public $properties = [];

    /**
     * @var Geometry
     */
    public $geometry = [];


    /**
     * @param string $type
     * @param array|array[] $coordinates
     * @param array $properties
     */
    function __construct ($type, $coordinates, $properties = []) {
        $this->geometry = new Geometry($type, $coordinates);
        $this->properties = $properties;
    }

}