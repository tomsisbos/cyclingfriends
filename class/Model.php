<?php

class Model {
    
    protected $table;
    protected $db;
    protected static $root_folder;

    function __construct () {
        Model::$root_folder = $this->getRootFolder();
    }

    private static function getRootFolder () {
        return substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
    }

    protected static function getPdo () {
        require Model::getRootFolder() . '/actions/databaseAction.php';
        return $db;
    }
    
    protected static function getGis () {
        $db_name = getenv('GIS_NAME');
        require Model::getRootFolder() . '/actions/databaseAction.php';
        return $db;
    }

    /**
     * Get instance data from database
     */
    protected function getData ($table) {
        if ($this->id != NULL) {
            $getData = $this->getPdo()->prepare("SELECT * FROM {$this->table} WHERE id = {$this->id}");
            $getData->execute();
            return $getData->fetch();
        }
    }

    /** 
     * Get guide rank string from guide int value
     * @param int $guide_value
     * @return string
     */
    protected function getGuideRankString ($guide_value) {
        switch (intval($guide_value)) {
            case 1: return 'チーフ';
            case 2: return 'アシスタント';
            case 3: return '研修生';
        }
    }

    protected function getPeriod () {
        
        // Get part of the month from the day
        if (isset($this->date)) $date = $this->date;
        else if (isset($this->datetime)) $date = $this->datetime->format('Y-m-d H:i:s');
        $day = date("d", strtotime($date));
        if ($day < 10) $third = "上旬";
        else if (($day >= 10) AND ($day <= 20)) $third = "中旬";
        else if ($day > 20) $third = "下旬";

        // Get month in letters
        switch (date("n", strtotime($date))) {
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
    
    /**
     * Attribute a color depending on the level
     */
    public function colorLevel ($level) {
        switch ($level) {
            case 1 : return 'green';
            case 2 : return 'blue';
            case 3 : return 'red'; 
            default : return 'black';
        }
    }

    /**
     * Get privacy string from instance privacy property
     */
    public function getPrivacyString () {
        switch ($this->privacy) {
            case 'public': return '公開';
            case 'friends_only': return '友達のみ';
            case 'limited': return '限定公開';
            case 'private': return '非公開';
        }
    }

    /**
     * Generate a new notification for this instance
     * @param int $user_id user to notify
     * @param string $type name of type : type defines notification text content (see Notification::getText() for details)
     * @param int $actor_id id of related actor, if necessary in notification text content
     */
    public function notify ($user_id, $type, $actor_id = NULL) {
        $notification = new Notification();
        $notification->register($user_id, $type, $this->table, $this->id, $actor_id);
    }

}