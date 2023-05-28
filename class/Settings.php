<?php

class Settings extends Model {
    
    protected $table = 'settings';
    public $id;
    public $hide_on_neighbours;
    public $hide_realname;
    public $hide_age;
    
    function __construct($user_id) {
        parent::__construct();
        $this->id = $user_id;
        $data = $this->getData($this->table);
        if (isset($data['hide_on_neighbours'])) $this->hide_on_neighbours = (intval($data['hide_on_neighbours']) === 1);
        if (isset($data['hide_realname'])) $this->hide_realname = (intval($data['hide_realname']) === 1);
        if (isset($data['hide_age'])) $this->hide_age = (intval($data['hide_age']) === 1);
    }

}