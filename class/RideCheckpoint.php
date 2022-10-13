<?php

class RideCheckpoint extends Model {
    
    protected $table = 'ride_checkpoints';
    public $id;
    public $ride;
    public $number;
    public $img;
    
    function __construct ($id = NULL) {
        $this->id            = $id;
        $data = $this->getData($this->table);
        $this->number        = intval($data['checkpoint_id']);
        $this->name          = $data['name'];
        $this->description   = $data['description'];
        $this->img           = new CheckpointImage($data['id']);
        $this->lngLat        = new LngLat($data['lng'], $data['lat']);
        $this->elevation     = $data['elevation'];
        $this->distance      = floatval($data['distance']);
        $this->special       = $data['special'];
        $this->city          = $data['city'];
        $this->prefecture    = $data['prefecture'];
    }

}