<?php

class CFLinestringWithTrackpoints extends CFLinestring {
    
    /**
     * @var Trackpoint[] An array of trackpoints. Can be empty
     */
    public $trackpoints = [];
    
    /**
     * @param LngLat[]|array[] $coordinates An array of lngLat or float coordinates
     */
    function __construct ($coordinates = null, $trackpoints = null) {
        parent::__construct();
        if ($coordinates) {
            forEach($coordinates as $lngLat) {
                if (!($lngLat instanceof LngLat)) $lngLat = new LngLat($lngLat[0], $lngLat[1]);
                array_push($this->coordinates, $lngLat);
            }
            $this->length = count($coordinates);
        }
        $this->trackpoints = $trackpoints;
    }

    /**
     * Save linestring in database
     * @param int $segment_id id of segment to refer to
     */
    protected function saveLinestring ($segment_id) {
        $query_columns = 'segment_id, linestring';
        $query_values  = '?, ST_LineStringFromText(?)';
        $query_params  = [$segment_id, $this->toWKT()];
        foreach ($this->trackpoints[0] as $property => $value) {
            if (!($value instanceof LngLat) && $value !== null) { // If first trackpoint value is not null, save appended data
                $query_columns .= ', ' .$property. '_array';
                $query_values  .= ', ?';
                array_push($query_params, json_encode($this->toDataArray($property)));
            }
        }
        $save = $this->getPdo()->prepare("INSERT INTO {$this->table} ({$query_columns}) VALUES ({$query_values})");
        $save->execute($query_params);
    }

    /**
     * Convert data from Trackpoint instances to linear array of one property data
     * @param string $property Property name to extract
     * @return array An array of all entries of this property in chronological order
     */
    public function toDataArray ($property) {
        $array = [];
        foreach ($this->trackpoints as $trackpoint) {
            array_push($array, $trackpoint->$property);
        }
        return $array;
    }
}