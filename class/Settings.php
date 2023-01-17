<?php

class Settings extends Model {
    
    protected $table = 'settings';
    public $id;
    public $hide_on_riders;
    public $hide_on_neighbours;
    public $hide_on_chat;
    
    function __construct($user_id) {
        parent::__construct();
        $this->id = $user_id;
        $data = $this->getData($this->table);
        if (isset($data['hide_on_riders'])) $this->hide_on_riders = (intval($data['hide_on_riders']) === 1);
        if (isset($data['hide_on_neighbours'])) $this->hide_on_neighbours = (intval($data['hide_on_neighbours']) === 1);
        if (isset($data['hide_on_chat'])) $this->hide_on_chat = (intval($data['hide_on_chat']) === 1);
    }

}