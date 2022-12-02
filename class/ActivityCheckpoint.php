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
        parent::__construct();
        $this->id          = $id;
        $data = $this->getData($this->table);
        $this->activity_id = intval($data['activity_id']);
        $this->number      = intval($data['number']);
        $this->name        = $data['name'];
        $this->type        = $data['type'];
        $this->story       = $data['story'];
        $this->datetime    = new DateTime($data['datetime']);
        $this->geolocation = new Geolocation($data['city'], $data['prefecture']);
        $this->elevation   = intval($data['elevation']);
        $this->distance    = floatval($data['distance']);
        $this->temperature = floatval($data['temperature']);
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

    public function getPhotos () {

        // Get datetime of previous checkpoint
        $getPreviousCheckpointDatetime = $this->getPdo()->prepare('SELECT datetime FROM activity_checkpoints WHERE activity_id = ? AND number = ?');
        $getPreviousCheckpointDatetime->execute(array($this->activity_id, $this->number - 1));
        if ($getPreviousCheckpointDatetime->rowCount() > 0) $previous_checkpoint_datetime = new DateTime($getPreviousCheckpointDatetime->fetch(PDO::FETCH_NUM)[0]);
        else $previous_checkpoint_datetime = $this->datetime;

        // Get all activity photos id with datetime
        $getPhotos = $this->getPdo()->prepare('SELECT id, datetime FROM activity_photos WHERE activity_id = ? AND datetime >= ? AND datetime < ? ORDER BY datetime');
        $getPhotos->execute(array($this->activity_id, $previous_checkpoint_datetime->format('Y-m-d H:i:s'), $this->datetime->format('Y-m-d H:i:s')));
        $photo_ids = $getPhotos->fetchAll(PDO::FETCH_ASSOC);
        
        $photos_to_append = [];
        foreach ($photo_ids as $photo_id) {
            array_push($photos_to_append, new ActivityPhoto($photo_id['id']));
        }

        return $photos_to_append;
    }

}