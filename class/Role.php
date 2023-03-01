<?php

class Role extends Model {
    
    protected $table = 'rights';
    public $id;
    public $slug;
    public $name;
    public $rank; // User : 0~, Member : 1~, Editor : 2~, Moderator : 3~, Administrator : 40
    
    function __construct($slug) {
        parent::__construct();
        $this->slug = $slug;
        $data = $this->getData($this->table);
        $this->id   = $data['id'];
        $this->name = $data['name'];
        $this->rank = intval($data['rank']);
    }
    
    // Get instance data from database
    protected function getData ($table) {
        if ($this->id != NULL) {
            $getData = $this->getPdo()->prepare("SELECT * FROM {$this->table} WHERE slug = {$this->slug}");
            $getData->execute();
            return $getData->fetch();
        }
    }
}