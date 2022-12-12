<?php

class MkpointReview extends Model {
    
    protected $table = 'mkpoint_reviews';
    public $id;
    public $mkpoint_id;
    public $user;
    public $content;
    public $time;  
    public $parent_id;
    
    function __construct ($id = NULL) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($this->table);
        $this->mkpoint_id = intval($data['mkpoint_id']);
        $this->user       = new User($data['user_id']);
        $this->content    = $data['content'];
        $this->time       = $data['time'];
        $this->parent_id  = $data['parent_id'];
    }

    function getMkpoint () {
        return new Mkpoint($this->mkpoint_id);
    }

    function getParent () {
        return new MkpointReview($this->parent_id);
    }

    // Get rating user gave to corresponding mkpoint
    function getUserRating () {
        $vote = $this->getMkpoint()->getUserVote($this->user);
        return $vote;
    }

}