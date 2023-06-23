<?php

class SceneryImage extends Model {
    
    protected $table = 'scenery_photos';
    protected $container_name = "scenery-photos";
    public $id;
    public $scenery_id;
    public $user_id;
    public $date;
    public $month;
    public $period;
    public $likes;
    public $filename;
    public $url;
    
    function __construct ($id = NULL) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($this->table);
        $this->scenery_id = intval($data['scenery_id']);
        $this->user_id    = intval($data['user_id']);
        $this->date       = $data['date'];
        $datetime = new DateTime($data['date']);
        $this->month      = intval($datetime->format('m'));
        $this->period     = $this->getPeriod();
        $this->likes      = intval($data['likes']);
        $this->filename   = $data['filename'];
        $this->url        = $this->getUrl();
    }

    private function getUrl () {
        // Connect to blob storage
        require SceneryImage::$root_folder . '/actions/blobStorageAction.php';

        // Retrieve blob url
        return $blobClient->getBlobUrl($this->container_name, $this->filename);
    }

    public function delete () {
        // Connect to blob storage and delete blob
        require SceneryImage::$root_folder . '/actions/blobStorageAction.php';
        $blobClient->deleteBlob($this->container_name, $this->filename);

        // Remove database entry
        $removeSceneryPhoto = $this->getPdo()->prepare('DELETE FROM scenery_photos WHERE id = ?');
        $removeSceneryPhoto->execute(array($this->id));
    }

}