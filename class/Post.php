<?php

class Post extends Model {
    
    protected $table = 'posts';
    public $id;
    public $title;
    public $content;
    public $type;
    public $datetime;
    
    function __construct() {
        parent::__construct();
    }

    /**
     * Create a new post entry in the database
     * @return boolean
     */
    private function insertIntoTable() {
        // Check if no similar data
        $checkSimilarData = $this->getPdo()->prepare("SELECT id FROM {$this->table} WHERE content = ?");
        $checkSimilarData->execute(array($this->content));
        // If no similar data exists, insert data
        if ($checkSimilarData->rowCount() == 0) {
            $insertIntoTable = $this->getPdo()->prepare("INSERT INTO {$this->table} (title, content, type, datetime) VALUES (?, ?, ?, ?)");
            $insertIntoTable->execute(array($this->title, $this->content, $this->type, (new DateTime('now'))->setTimezone('Asia/Tokyo')->format('Y-m-d H:i:s')));
            return true;
        } else return false;
    }

    public function create ($title, $type, $content) {
        $this->title = $title;
        $this->type = $type;
        $this->content = $content;
        $result = $this->insertIntoTable();
        return $result;
    }

    public function load ($id) {
        $this->id = $id;
        $data = $this->getData($this->table);
        $this->title = $data['title'];
        $this->content = $data['content'];
        $this->type = $data['type'];
        $this->datetime = new Datetime($data['datetime']);
    }

    public function getTypeString () {
        switch ($this->type) {
            case 'general': return '<div class="general">一般</div>';
            case 'dev': return '<div class="dev">開発</div>';
        }
    }
}