<?php

use phpGPX\phpGPX;
use adriangibbons\phpFITFileAnalysis;

class ActivityFile extends Model {
    
    protected $table = 'activity_files';
    protected $container_name = 'activity-files';
    public $id;
    public $user_id;
    public $activity_id;
    public $filename;
    public $ext;
    public $data;
    public $latest_error = null;
    public $allowed_extensions = ['gpx', 'fit'];

    function __construct ($id = NULL) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($this->table);
        if ($id != NULL) {
            $this->user_id = $data['user_id'];
            $this->activity_id = $data['activity_id'];
            $this->filename = $data['filename'];
            $this->ext = $data['ext'];
            $this->posting_date = (new DateTime($data['posting_date']))->setTimezone(new DateTimeZone('Asia/Tokyo'))->format('Y-m-d H:i:s');
            $this->latest_error = $data['latest_error'];
        }
    }

    /**
     * Retrieve user id from garmin user id
     * @param int $garmin_user_id
     * @return int User id
     */
    public function getUserIdFromGarminId ($garmin_user_id) {
        $getUserId = $this->getPdo()->prepare("SELECT user_id FROM user_garmin WHERE garmin_user_id = ?");
        $getUserId->execute([$garmin_user_id]);
        return intval($getUserId->fetch(PDO::FETCH_COLUMN));
    }

    /**
     * Load file content into instance
     * @param File $content File content
     * @param array $metadata Necessary data to save file (Contains user_id and ext)
     */
    public function create ($content, $metadata) {
        $this->user_id = $metadata['user_id'];
        $this->ext = $metadata['ext'];
        $this->filename = setFilename('activity', $this->ext);
        $this->posting_date = (new DateTime(date('Y-m-d H:i:s')))->setTimeZone('Asia/Tokyo');
        $this->id = getNextAutoIncrement($this->table);

        // Insert file data in table
        $insertFileData = $this->getPdo()->prepare('INSERT INTO activity_files(user_id, activity_id, filename, ext, posting_date) VALUES (?, ?, ?, ?, ?)');
        $insertFileData->execute([$this->user_id, $this->activity_id, $this->filename, $this->ext, $this->posting_date->format('Y-m-d H:i:s')]);
        
        // Connect to blob storage
        require ActivityFile::$root_folder . '/actions/blobStorage.php';
        
        // Send file to blob storage
        $blobClient->createBlockBlob($this->container_name, $this->filename, $content);
        // Set file metadata
        $metadata = [
            'ext' => $this->ext,
            'user_id' => $this->user_id,
        ];
        $blobClient->setBlobMetadata($this->container_name, $this->filename, $metadata);
    }

    /**
     * Get server file url
     */
    public function getUrl () {
        // Connect to blob storage
        require ActivityFile::$root_folder . '/actions/blobStorage.php';

        // Retrieve blob url
        return $blobClient->getBlobUrl($this->container_name, $this->filename);
    }

    /**
     * Parse file and retrieve activity data
     * @return ActivityData A new instance of Activity Data
     * @throws Exception
     */
    public function parse () {

        // Load parser dependancies
        require ActivityFile::$root_folder . '/vendor/autoload.php';

        // Treatment of gpx files
        if ($this->ext == 'gpx') {

            // On success, send analyzed data back to client
            $phpGpx = new phpGPX();
            if (file_exists($this->getUrl())) $gpx = $phpGpx->load($this->getUrl());
            else throw new Exception('file_not_found');
            $activity_data = new ActivityData();
            $activity_data->buildFromGpx($gpx);
            $activity_data->file_id = $this->id;

            $this->data = $activity_data;
            return $activity_data;
        
        // Treatment of fit files
        } else if ($this->ext == 'fit') {

            // On success, send analyzed data back to client

            // First dowload remote file to local temp folder
            if (!is_dir($_SERVER["DOCUMENT_ROOT"] . '/media/activities/data')) mkdir($_SERVER["DOCUMENT_ROOT"] . '/media/activities/data');
            if (!is_dir($_SERVER["DOCUMENT_ROOT"] . '/media/activities/data/temp')) mkdir($_SERVER["DOCUMENT_ROOT"] . '/media/activities/data/temp');
            $url = $_SERVER["DOCUMENT_ROOT"] . '/media/activities/data/temp/temp.' . $this->ext;
            if (file_exists($this->getUrl())) file_put_contents($url, file_get_contents($this->getUrl()));
            else throw new Exception('file_not_found');

            // Then parse

            $pFFA = new phpFITFileAnalysis($url);
            $fit = new FitData($pFFA->data_mesgs);
            
            $activity_data = new ActivityData();
            $activity_data->buildFromFit($fit);
            $activity_data->file_id = $this->id;

            $this->data = $activity_data;
            return $activity_data;

        // Treatment of tcx files (unavailable at this time)
        } else if ($this->ext == 'tcx') throw new Exception ('*.tcxファイルの解析には対応しておりません。ご迷惑をお掛けしますが、次のファイル形式に変換してから再度お使いください。' .implode('、', $this->allowed_extensions));

    }

}