<?php

class CheckpointImage extends Model {
    
    private $container_name = 'checkpoint-images';
    protected $table = 'ride_checkpoints';
    public $id;
    public $blob;
    public $size;
    public $name;
    public $type;

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
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/blobStorageAction.php';

        // Retrieve blob url
        return $blobClient->getBlobUrl($this->container_name, $this->filename);
    }

}