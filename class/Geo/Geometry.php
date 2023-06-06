<?php

namespace Geo;

class Geometry {

    /**
     * @var string
     */
    public $type;

    /**
     * @var array|array[]
     */
    public $coordinates;

    function __construct ($type, $coordinates) {
        $this->type = $type;
        $this->coordinates = $coordinates;
    }
}