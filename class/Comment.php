<?php

class Comment extends Model {

    public $id;
    public $entry_id;
    public $user;
    public $content;
    public $time;  
    public $parent_id;
    
    function __construct ($id = NULL) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($this->table);
        $this->entry_id = intval($data['entry_id']);
        $this->user       = new User($data['user_id']);
        $this->content    = $data['content'];
        $this->time       = $data['time'];
        $this->parent_id  = $data['parent_id'];
    }

}