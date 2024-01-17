<?php

class CheckpointImage extends Model {
    
    private $container_name = 'checkpoint-images';
    protected $table = 'ride_checkpoints';
    public $id;
    public $blob;
    public $size;
    public $name;
    public $type;
    public $url;

    function __construct ($id = NULL) {
        parent::__construct();
        $this->id       = $id;
        $data = $this->getData($this->table);
        $this->filename = $data['filename'];
        $this->size     = $data['img_size'];
        $this->name     = $data['img_name'];
        $this->type     = $data['img_type'];
        $this->url      = $this->getUrl();
    }

    private function getUrl() {
        // Connect to blob storage
        require CheckpointImage::$root_folder . '/actions/blobStorage.php';

        // Retrieve blob url
        if (isset($this->filename)) return $blobClient->getBlobUrl($this->container_name, $this->filename);
        else return false;
    }

}