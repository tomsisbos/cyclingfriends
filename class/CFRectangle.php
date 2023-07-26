<?php

class CFRectangle extends Model {
    
    /**
     * @param LngLat[]|array[] $coordinates An array of lngLat or float coordinates
     */
    function __construct ($topleft_lng, $topleft_lat, $bottomright_lng, $bottomright_lat) {
        parent::__construct();
        $this->coordinates = [new LngLat($topleft_lng, $topleft_lat), new LngLat($bottomright_lng, $bottomright_lat)];
    }
    
    /**
     * Returns a polygon at WKT format
     */
    public function toWKT () {
        $polygon_wkt = 'POLYGON((' .$this->coordinates[0]->lng. ' ' .$this->coordinates[0]->lat. ', '  .$this->coordinates[1]->lng. ' ' .$this->coordinates[0]->lat. ', ' .$this->coordinates[1]->lng. ' ' .$this->coordinates[1]->lat. ', '  .$this->coordinates[0]->lng. ' ' .$this->coordinates[1]->lat. ', ' .$this->coordinates[0]->lng. ' ' .$this->coordinates[0]->lat. '))';
        ///var_dump($polygon_wkt); die();
        return $polygon_wkt;
    }

}