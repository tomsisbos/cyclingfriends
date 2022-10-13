<?php 

class Log {

    public $message = array();

    function __construct($log) { // array of message ids
        for ($i = 0; $i < count($log); $i++) {
            $this->message[$i] = new DirectMessage($log[$i]['id']);
        }
    }/*

    public function getLastMessage () {
        $last = count($this->message) - 1;
        return $this->message[$last];

    }*/

}