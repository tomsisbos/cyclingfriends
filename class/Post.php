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

    private function insertIntoTable() {
        $insertIntoTable = $this->getPdo()->prepare("INSERT INTO {$this->table} (title, content, type, datetime) VALUES (?, ?, ?, ?)");
        $insertIntoTable->execute(array($this->title, $this->content, $this->type, date('Y-m-d H:i:s')));
    }

    public function create ($title, $type, $content) {
        $this->title = $title;
        $this->type = $type;
        $this->content = $content;
        $this->insertIntoTable();
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