<?php

class Model {
    
    protected $table;
    protected $db;

    function __construct () {
    }

    protected static function getPdo () {
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/databaseAction.php';
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
     */
    public function notify ($user_id, $type, $actor_id = NULL) {
        $notification = new Notification();
        $notification->register($user_id, $type, $this->table, $this->id, $actor_id);
    }

}