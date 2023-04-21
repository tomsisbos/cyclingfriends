<?php

class RideCheckpoint extends Model {
    
    protected $table = 'ride_checkpoints';
    public $id;
    public $ride;
    public $number;
    public $img;
    
    function __construct ($id = NULL) {
        parent::__construct();
        $this->id            = $id;
        $data = $this->getData($this->table);
        $this->number        = intval($data['checkpoint_id']);
        $this->name          = $data['name'];
        $this->description   = $data['description'];
        $this->img           = new CheckpointImage($data['id']);
        $this->lngLat        = $this->getLngLat();
        $this->elevation     = $data['elevation'];
        $this->distance      = floatval($data['distance']);
        $this->special       = $data['special'];
        $this->city          = $data['city'];
        $this->prefecture    = $data['prefecture'];
    }

    private function getLngLat () {
        $getPointToText = $this->getPdo()->prepare("SELECT ST_AsText(point) FROM {$this->table} WHERE id = ?");
        $getPointToText->execute([$this->id]);
        $point_text = $getPointToText->fetch(PDO::FETCH_COLUMN);
        $lngLat = new LngLat();
        $lngLat->fromWKT($point_text);
        return $lngLat;
    }

}