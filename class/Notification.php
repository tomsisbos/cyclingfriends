<?php

class Notification extends Model {
    
    protected $table = 'notifications';
    public $id;
    public $user_id;
    public $type;
    public $entry_table;
    public $entry_id;
    public $checked;
    public $datetime;
    public $text;
    public $ref;

    function __construct ($id = NULL) {
        parent::__construct();
        if ($id !== NULL) $this->load($id);
    }

    public function load ($id) {
        $this->id          = intval($id);
        $data = $this->getData($this->table);
        $this->user_id     = $data['user_id'];
        $this->type        = $data['type'];
        $this->entry_table = $data['entry_table'];
        $this->entry_id    = $data['entry_id'];
        $this->checked     = (intval($data['checked']) === 1);
        $this->datetime    = new Datetime($data['datetime']);
    }

    public function register ($user_id, $type, $entry_table, $entry_id) {
        $checkIfExists = $this->getPdo()->prepare("SELECT id FROM {$this->table} WHERE user_id = ? AND type = ? AND entry_table = ? AND entry_id = ?");
        $checkIfExists->execute([$user_id, $type, $entry_table, $entry_id]);
        // If similar entry exists, reset checked and datetime values
        if ($checkIfExists->rowCount() > 0) {
            $updateNotification = $this->getPdo()->prepare("UPDATE {$this->table} SET checked = 0, datetime = NOW()");
            $updateNotification->execute();
        // Else, insert it
        } else {
            $id = getNextAutoIncrement($this->table);
            $createNotification = $this->getPdo()->prepare("INSERT INTO {$this->table} (user_id, type, entry_table, entry_id) VALUES (?, ?, ?, ?)");
            $createNotification->execute([$user_id, $type, $entry_table, $entry_id]);
            $this->id          = $id;
            $this->user_id     = $user_id;
            $this->type        = $type;
            $this->entry_table = $entry_table;
            $this->entry_id    = $entry_id;
            $this->checked     = false;
            $this->datetime    = new Datetime();
        }
    }

    /**
     * Get the instance of which user notification is to be notified to
     */
    public function getUser () {
        return new User($this->user_id);
    }

    /**
     * Get the instance of what is notification related to
     */
    public function getEntry () {
        switch ($this->entry_table) {
            case 'activities': return new Activity($this->entry_id);
            case 'routes': return new Route($this->entry_id);
            case 'rides': return new Ride($this->entry_id);
            case 'users': return new User($this->entry_id);
            case 'map_mkpoint': return new Mkpoint($this->entry_id);
            /// [...]
        }
    }

    /**
     * Generate text notification according to type
     */
    public function getText () {

        $entry = $this->getEntry();

        switch ($this->type) {
            case 'friends_request':
                $this->text = $entry->login. 'から友達リクエストが届いています。';
                $this->ref = 'friends';
                break;
            case 'friends_approval':
                $this->text = $entry->login. 'が友達リクエストを承認してくれました！';
                $this->ref = 'rider/' .$entry->id;
                break;
            case 'follow':
                $this->text = $entry->login. 'がフォローしてくれました。';
                $this->ref = 'rider/' .$entry->id;
                break;
        }
    }

    /**
     * Set this notification checked property to true
     */
    public function check () {
        $checkNotification = $this->getPdo()->prepare("UPDATE {$this->table} SET checked = 1, datetime = NOW() WHERE id = ?");
        $checkNotification->execute([$this->id]);
    }
}