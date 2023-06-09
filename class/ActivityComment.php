<?php

class ActivityComment extends Comment {
    
    protected $table = 'activity_comments';
    
    function __construct ($id = NULL) {
        parent::__construct($id);
    }

    function getActivity () {
        return new Activity($this->entry_id);
    }

    function getParent () {
        return new ActivityComment($this->parent_id);
    }

}