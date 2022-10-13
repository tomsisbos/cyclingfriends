<?php

class ActivityCheckpoint extends Model {
    
    protected $table = 'activity_checkpoints';
    public $id;
    public $number;
    public $name;
    public $type;
    public $story;
    public $datetime;
    public $city;
    public $prefecture;
    public $elevation;
    public $distance;
    public $temperature;
    public $lngLat;
    public $special;
    
    function __construct ($id = NULL) {
        $this->id          = $id;
        $data = $this->getData($this->table);
        $this->activity_id = $data['activity_id'];
        $this->number      = $data['number'];
        $this->name        = $data['name'];
        $this->type        = $data['type'];
        $this->story       = $data['story'];
        $this->datetime    = new DateTime($data['datetime']);
        $this->geolocation = new Geolocation($data['city'], $data['prefecture']);
        $this->elevation   = $data['elevation'];
        $this->distance    = floatval($data['distance']);
        $this->temperature = $data['temperature'];
        $this->lngLat      = new LngLat($data['lng'], $data['lat']);
        $this->special     = $data['special'];
    }

    public function getIcon ($width = 24) {
        switch ($this->type) {
            case 'Start':
                return '<span class="iconify" data-icon="material-symbols:not-started-rounded" data-inline="true" data-width="' . $width . '"></span>';
            case 'Landscape':
                return '<span class="iconify" data-icon="bxs:landscape" data-inline="true" data-width="' . $width . '"></span>';
            case 'Break':
                return '<span class="iconify" data-icon="ic:round-pause-circle" data-inline="true" data-width="' . $width . '"></span>';
            case 'Restaurant':
                return '<span class="iconify" data-icon="ion:restaurant" data-inline="true" data-width="' . $width . '"></span>';
            case 'Cafe':
                return '<span class="iconify" data-icon="medical-icon:i-coffee-shop" data-inline="true" data-width="' . $width . '"></span>';
            case 'Attraction':
                return '<span class="iconify" data-icon="gis:layer-poi" data-inline="true" data-width="' . $width . '"></span>';
            case 'Event':
                return '<span class="iconify" data-icon="entypo:info-with-circle" data-inline="true" data-width="' . $width . '"></span>';
            case 'Goal':
                return '<span class="iconify" data-icon="gis:finish" data-inline="true" data-width="' . $width . '"></span>';
        }
    }

}