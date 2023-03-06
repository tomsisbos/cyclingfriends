<?php

class DevMessage extends Model {
    
    protected $table = 'dev_chat';
    public $id;
    public $number;
    public $user_id;
    public $content;
    
    function __construct($id = NULL) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($id);
        $this->number = $data['number'];
        $this->user_id = $data['user_id'];
        $this->content = $data['content'];
    }
    
    public function getUser () {
        return new User($this->user_id);
    }

}