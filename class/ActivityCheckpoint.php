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
        $this->id = $id;
        $data = $this->getData($this->table);
        if ($id != NULL) {
            $this->activity_id = intval($data['activity_id']);
            $this->number      = intval($data['number']);
            $this->name        = $data['name'];
            $this->type        = $data['type'];
            $this->story       = nl2br($data['story']);
            $this->datetime    = new DateTime($data['datetime']);
            $this->geolocation = new Geolocation($data['city'], $data['prefecture']);
            $this->elevation   = intval($data['elevation']);
            $this->distance    = floatval($data['distance']);
            $this->temperature = floatval($data['temperature']);
            $this->lngLat      = $this->getLngLat();
            $this->special     = $data['special'];
        }
    }

    private function getLngLat () {
        $getPointToText = $this->getPdo()->prepare("SELECT ST_AsText(point) FROM {$this->table} WHERE id = ?");
        $getPointToText->execute([$this->id]);
        $point_text = $getPointToText->fetch(PDO::FETCH_COLUMN);
        $lngLat = new LngLat();
        $lngLat->fromWKT($point_text);
        return $lngLat;
    }

    /**
     * Create a checkpoint from data
     * @param array $data An array of properties
     */
    public function create ($data) {

        $lngLat = new LngLat($data['lng'], $data['lat']);
        $point_wkt = $lngLat->toWKT();

        // Insert checkpoints in 'activity_checkpoints' table
        $insert_checkpoints = $this->getPdo()->prepare("INSERT INTO {$this->table}(activity_id, number, name, type, story, datetime, city, prefecture, elevation, distance, temperature, special, point) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ST_GeomFromText(?))");
        $insert_checkpoints->execute(array($data['activity_id'], $data['number'], $data['name'], $data['type'], $data['story'], $data['datetime']->format('Y-m-d H:i:s'), $data['city'], $data['prefecture'], intval($data['elevation']), $data['distance'], $data['temperature'], $data['special'], $point_wkt));
    }

    public function getIcon ($width = 24) {
        switch ($this->type) {
            case 'Start':
                return '<span class="iconify" data-icon="material-symbols:play-circle" style="color: #00e06e" data-inline="true" data-width="' . $width . '"></span>';
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
                return '<span class="iconify" data-icon="material-symbols:stop-circle" style="color: #ff5555" data-inline="true" data-width="' . $width . '"></span>';
        }
    }

    public function getPhotos () {

        // Get all activity photos id with datetime
        $getPhotos = $this->getPdo()->prepare("
            SELECT id, datetime
            FROM activity_photos
            WHERE
                activity_id = :activity_id
            AND
                datetime >= (
                    CASE 
                        WHEN (SELECT COUNT(*) FROM (SELECT datetime FROM activity_checkpoints WHERE activity_id = :activity_id AND number = :previous_number) AS next_datetime) > 0
                        THEN (SELECT datetime FROM activity_checkpoints WHERE activity_id = :activity_id AND number = :previous_number)
                        ELSE :this_datetime
                    END
                )
            AND
                datetime < :this_datetime
            ORDER BY
                datetime
        ");
        $getPhotos->execute([
            ':activity_id' => $this->activity_id,
            ':previous_number' => $this->number - 1,
            ':this_datetime' => $this->datetime->format('Y-m-d H:i:s')
        ]);
        $photo_ids = $getPhotos->fetchAll(PDO::FETCH_ASSOC);
        
        $photos_to_append = [];
        foreach ($photo_ids as $photo_id) {
            array_push($photos_to_append, new ActivityPhoto($photo_id['id']));
        }

        return $photos_to_append;
    }

}