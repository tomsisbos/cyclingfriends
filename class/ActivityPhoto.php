<?php

class ActivityPhoto extends Model {
    
    protected $table = 'activity_photos';
    protected $container_name = 'activity-photos';
    public $id;
    public $activity_id;
    public $user_id;
    public $datetime;
    public $elevation;
    public $period;
    public $featured;
    public $lngLat;
    public $filename;
    public $url;
    public $privacy;

    function __construct ($id = NULL) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($this->table);
        if ($id != NULL) {
            $this->activity_id = $data['activity_id'];
            $this->user_id     = $data['user_id'];
            $this->datetime    = (new DateTime($data['datetime']))->getTimestamp();
            $this->elevation   = $data['elevation'];
            $this->period      = $this->getPeriod();
            if (intval($data['featured']) == 1) $this->featured = true;
            else $this->featured = false;
            $this->lngLat      = $this->getLngLat();
            $this->filename    = $data['filename'];
            $this->url         = $this->getUrl();
            $this->privacy     = $data['privacy'];
        }
    }

    private function getLngLat () {
        $getPointToText = $this->getPdo()->prepare("SELECT ST_AsText(point) FROM {$this->table} WHERE id = ? AND point IS NOT NULL");
        $getPointToText->execute([$this->id]);
        if ($getPointToText->rowCount() > 0) {
            $point_text = $getPointToText->fetch(PDO::FETCH_COLUMN);
            $lngLat = new LngLat();
            $lngLat->fromWKT($point_text);
            return $lngLat;
        } else return null;
    }

    private function getUrl () {
        // Connect to blob storage
        require ActivityPhoto::$root_folder . '/actions/blobStorage.php';

        // Retrieve blob url
        return $blobClient->getBlobUrl($this->container_name, $this->filename);
    }

    /**
     * Register a new activity photo entry in the database
     * @param array $array containing necessary data (activity photo data, blob metadata)
     * @return string filename
     */
    public function create ($data) {

        // Convert lng and lat to WKT format
        $lngLat = new LngLat($data['lng'], $data['lat']);
        $point_wkt = $lngLat->toWKT();

        // Insert photo in 'activity_photos' table
        $filename = setFilename('img');
        $insert_photos = $this->getPdo()->prepare("INSERT INTO activity_photos(activity_id, user_id, datetime, featured, filename, privacy, point, elevation) VALUES (?, ?, to_timestamp(?), ?, ?, ?, ST_GeomFromText(?), ?)");
        $insert_photos->execute(array($data['activity_id'], $data['user_id'], $data['datetime'], $data['featured'], $filename, $data['privacy'], $point_wkt, $data['elevation']));

        // Connect to blob storage
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/blobStorage.php';

        // Send file to blob storage
        $containername = 'activity-photos';
        $blobClient->createBlockBlob($containername, $filename, $data['blob']);
        // Set file metadata
        $metadata = [
            'file_name' => $data['name'],
            'file_type' => $data['type'],
            'file_size' => $data['size'],
            'activity_id' => $data['activity_id'],
            'author_id' => $data['user_id'],
            'date' => $data['datetime'],
            'lng' => $data['lng'],
            'lat' => $data['lat'],
            'privacy' => $data['privacy']
        ];
        $blobClient->setBlobMetadata($containername, $filename, $metadata);

        return $filename;
    }

}