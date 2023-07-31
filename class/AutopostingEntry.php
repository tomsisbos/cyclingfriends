<?php

class AutopostingEntry extends Model {

    protected $table = 'autoposting';
    public $id;
    public $entry_id;
    public $entry_type;
    public $datetime;
    public $instance;

    function __construct ($id) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($this->table);
        $this->entry_id = $data['entry_id'];
        $this->entry_type = $data['entry_type'];
        $this->datetime = $data['datetime'];
        $this->instance = $this->getInstance();
    }

    /**
     * Get relevant entry instance
     */
    private function getInstance () {
        switch ($this->entry_type) {
            case 'scenery': return new Scenery($this->entry_id);
        }
    }

}