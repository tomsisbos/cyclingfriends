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
        require Manual::getRootFolder() . '/actions/databaseAction.php';
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
     * @param int $user_id user to notify
     * @param string $type name of type : type defines notification text content (see Notification::getText() for details)
     * @param int $actor_id id of related actor, if necessary in notification text content
     */
    public function notify ($user_id, $type, $actor_id = NULL) {
        $notification = new Notification();
        $notification->register($user_id, $type, $this->table, $this->id, $actor_id);
    }

}