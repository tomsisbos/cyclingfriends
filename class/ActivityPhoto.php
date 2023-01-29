<?php

class ActivityPhoto extends Model {
    
    protected $table = 'activity_photos';
    protected $container_name = 'activity-photos';
    public $id;
    public $activity_id;
    public $user_id;
    public $datetime;
    public $featured;
    public $filename;
    public $url;

    function __construct ($id = NULL) {
        parent::__construct();
        $this->id          = $id;
        $data = $this->getData($this->table);
        $this->activity_id = $data['activity_id'];
        $this->user_id     = $data['user_id'];
        $this->datetime    = new DateTime($data['datetime']);
        if (intval($data['featured']) == 1) $this->featured = true;
        else $this->featured = false;
        $this->filename    = $data['filename'];
        $this->url         = $this->getUrl();
    }

    private function getUrl () {
        // Connect to blob storage
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/blobStorageAction.php';

        // Retrieve blob url
        return $blobClient->getBlobUrl($this->container_name, $this->filename);
    }

}