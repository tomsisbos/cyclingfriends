<?php

class RideMessage extends Model {

    public $parent;
    protected $table = 'ride_chat';
    
    function __construct($id = NULL) {
        $this->id                        = $id;
        $data = $this->getData($this->table);
        $this->ride                      = new Ride ($data['ride_id']);
        $this->author                    = new User ($data['author_id']);
        $this->message                   = $data['message'];
        $this->time                      = $data['time'];
        if ($data['parent_id']) {
            $this->parent                = new RideMessage ($data['parent_id']);
        }
    }
    
}