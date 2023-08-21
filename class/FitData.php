<?php

class FitData {

    public $record = [];
    public $session = [];
    
    function __construct($data) {

        $errors_directory = $_SERVER["DOCUMENT_ROOT"]. '/api/garmin/errors';
        if (!file_exists($errors_directory)) mkdir($errors_directory, 0777, true); // Create user directory if necessary
        $temp_url = $errors_directory. '/' .'fit.log';
        file_put_contents($temp_url, json_encode($data));

        if (!isset($data['record'])) throw new Exception('no_record_data');
        foreach ($data['record'] as $key => $entry) {
            if (is_array($entry)) $data_record[$key] = array_values($entry);
            else $data_record[$key] = $entry;
        }
        $this->record = $data_record;
        $this->session = $data['session'];
    }
}