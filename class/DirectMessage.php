<?php

class DirectMessage extends Model {
    
    protected $table = 'messages';

    function __construct($id = NULL) {
        $this->id                        = $id;
        $data = $this->getData($this->table);
        $this->sender                    = new User ($data['sender_id']);
        $this->receiver                  = new User ($data['receiver_id']);
        $this->message                   = $data['message'];
        $this->time                      = $data['time'];
        $this->answer_to                 = $data['answer_to'];
        $this->group_id                  = $data['group_id'];
    }

}