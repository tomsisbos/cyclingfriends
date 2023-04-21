<?php

class SceneryReview extends Model {
    
    protected $table = 'scenery_reviews';
    public $id;
    public $scenery_id;
    public $user;
    public $content;
    public $time;  
    public $parent_id;
    
    function __construct ($id = NULL) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($this->table);
        $this->scenery_id = intval($data['scenery_id']);
        $this->user       = new User($data['user_id']);
        $this->content    = $data['content'];
        $this->time       = $data['time'];
        $this->parent_id  = $data['parent_id'];
    }

    function getScenery () {
        return new Scenery($this->scenery_id);
    }

    function getParent () {
        return new SceneryReview($this->parent_id);
    }

    // Get rating user gave to corresponding scenery
    function getUserRating () {
        $vote = $this->getScenery()->getUserVote($this->user);
        return $vote;
    }

}