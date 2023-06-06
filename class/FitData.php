<?php

class FitData {

    public $record = [];
    public $session = [];
    
    function __construct($data) {

        foreach ($data['record'] as $key => $entry) {
            $data_record[$key] = array_values($entry);
        }
        $this->record = $data_record;
        $this->session = $data['session'];
    }
}