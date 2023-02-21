<?php

class MkpointImage extends Model {
    
    protected $table = 'img_mkpoint';
    protected $container_name = "scenery-photos";
    public $id;
    public $mkpoint_id;
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
        $this->mkpoint_id = intval($data['mkpoint_id']);
        $this->user_id    = intval($data['user_id']);
        $this->date       = $data['date'];
        $datetime = new DateTime($data['date']);
        $this->month      = intval($datetime->format('m'));
        $this->period     = $this->getPeriod();
        $this->likes      = intval($data['likes']);
        $this->filename   = $data['filename'];
        $this->url        = $this->getUrl();
    }

    private function getPeriod() {
        
        // Get part of the month from the day
        $day = date("d", strtotime($this->date));
        if ($day < 10) $third = "上旬";
        else if (($day >= 10) AND ($day <= 20)) $third = "中旬";
        else if ($day > 20) $third = "下旬";

        // Get month in letters
        switch (date("n", strtotime($this->date))) {
            case 1: $month = "1月"; break;
            case 2: $month = "2月"; break;
            case 3: $month = "3月"; break;
            case 4: $month = "4月"; break;
            case 5: $month = "5月"; break;
            case 6: $month = "6月"; break;
            case 7: $month = "7月"; break;
            case 8: $month = "8月"; break;
            case 9: $month = "9月"; break;
            case 10: $month = "10月"; break;
            case 11: $month = "11月"; break;
            case 12: $month = "12月"; 
        }

        return $month . $third;
    }

    private function getUrl () {
        // Connect to blob storage
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/blobStorageAction.php';

        // Retrieve blob url
        return $blobClient->getBlobUrl($this->container_name, $this->filename);
    }

    public function delete () {
        // Connect to blob storage and delete blob
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/blobStorageAction.php';
        $blobClient->deleteBlob($this->container_name, $this->filename);

        // Remove database entry
        $removeMkpointPhoto = $this->getPdo()->prepare('DELETE FROM img_mkpoint WHERE id = ?');
        $removeMkpointPhoto->execute(array($this->id));
    }

}