<?php

class SceneryReview extends Comment {
    
    protected $table = 'scenery_reviews';
    
    function __construct ($id = NULL) {
        parent::__construct($id);
    }

    function getScenery () {
        return new Scenery($this->entry_id);
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