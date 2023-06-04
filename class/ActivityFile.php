<?php

class ActivityFile extends Model {
    
    protected $table = 'activity_files';
    protected $container_name = 'activity-files';
    public $id;
    public $user_id;
    public $activity_id;
    public $filename;
    public $garmin_activity_id;
    public $ext;

    function __construct ($id = NULL) {
        parent::__construct();
        $this->id = $id;
        /*
        $data = $this->getData($this->table);
        if ($id != NULL) {
        }*/
    }

    /**
     * Retrieve user id from garmin user id
     * @param int $garmin_user_id
     * @return int User id
     */
    private function getUserIdFromGarminId ($garmin_user_id) {
        $getUserId = $this->getPdo()->prepare("SELECT user_id FROM user_garmin WHERE garmin_user_id = ?");
        $getUserId->execute([$garmin_user_id]);
        return intval($getUserId->fetch(PDO::FETCH_COLUMN));
    }

    /**
     * Load file content into instance
     * @param File $content File content
     * @param array $metadata Necessary data to save file (Contains ext, garmin_activity_id, garmin_user_id)
     */
    public function create ($content, $metadata) {
        $this->user_id = $this->getUserIdFromGarminId($metadata['garmin_user_id']);
        $this->ext = $metadata['ext'];
        $this->filename = setFilename('activity', $this->ext);
        $this->garmin_activity_id = $metadata['garmin_activity_id'];
        $this->datetime = (new DateTime(date('Y-m-d H:i:s', $metadata['timestamp']), new DateTimezone('Asia/Tokyo')));
        $this->posting_date = (new DateTime(date('Y-m-d H:i:s'), new DateTimezone('Asia/Tokyo')));

        // Insert file data in table
        $insertFileData = $this->getPdo()->prepare('INSERT INTO activity_files(user_id, activity_id, filename, garmin_activity_id, ext, datetime, posting_date) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $insertFileData->execute([$this->user_id, $this->activity_id, $this->filename, $this->garmin_activity_id, $this->ext, $this->datetime->format('Y-m-d H:i:s'), $this->posting_date->format('Y-m-d H:i:s')]);
        
        // Connect to blob storage
        require ActivityFile::$root_folder . '/actions/blobStorageAction.php';
        
        // Send file to blob storage
        $blobClient->createBlockBlob($this->container_name, $this->filename, $content);
        // Set file metadata
        $metadata = [
            'ext' => $this->ext,
            'user_id' => $this->user_id,
            'garmin_user_id' => $metadata['garmin_user_id'],
            'garmin_activity_id' => $this->garmin_activity_id,
            'timestamp' => $metadata['timestamp']
        ];
        $blobClient->setBlobMetadata($this->container_name, $this->filename, $metadata);
    }

    private function getUrl () {
        // Connect to blob storage
        require ActivityFile::$root_folder . '/actions/blobStorageAction.php';

        // Retrieve blob url
        return $blobClient->getBlobUrl($this->container_name, $this->filename);
    }

}