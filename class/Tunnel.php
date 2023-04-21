<?php

class Tunnel extends CFLinestring {
    
    protected $table = 'tunnels';

    function __construct ($coordinates = NULL) {
        parent::__construct($coordinates);
    }

    /**
     * Save tunnel in database
     * @param int $segment_id id of segment to refer to
     */
    public function saveTunnel ($segment_id, $number = NULL) {
        $saveTunnel = $this->getPdo()->prepare("INSERT INTO {$this->table} (segment_id, number, linestring) VALUES (?, ?, ST_LineStringFromText(?))");
        $saveTunnel->execute([$segment_id, $number, $this->toWKT()]);
    }
}