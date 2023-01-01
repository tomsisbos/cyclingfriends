<?php

class MkpointImage extends Image {
    
    protected $table = 'img_mkpoint';
    public $id;
    public $mkpoint_id;
    public $user_id;
    public $user_login;
    public $date;
    public $month;
    public $period;
    public $blob;
    public $size;
    public $name;
    public $type;  
    public $likes;
    
    function __construct ($id = NULL) {
        parent::__construct($id);
        $data = $this->getData($this->table);
        $this->mkpoint_id = intval($data['mkpoint_id']);
        $this->user_id    = intval($data['user_id']);
        $this->user_login = $data['user_login'];
        $this->date       = $data['date'];
        $this->month      = intval($data['month']);
        $this->period     = $this->getPeriod();
        $this->blob       = $data['file_blob'];
        $this->size       = intval($data['file_size']);
        $this->name       = $data['file_name'];
        $this->type       = $data['file_type'];
        $this->likes      = intval($data['likes']);
    }

    private function getPeriod() {
        
        // Get part of the month from the day
        $day = date("d", strtotime($this->date));
        if ($day < 10) $third = "上旬";
        else if (($day >= 10) AND ($day <= 20)) $third = "中旬";
        else if ($day > 20) $third = "下旬";

        // Get month in letters
        switch (date("n", strtotime($this->date))) {
            case 1: $month = "1月"; break;
            case 2: $month = "2月"; break;
            case 3: $month = "3月"; break;
            case 4: $month = "4月"; break;
            case 5: $month = "5月"; break;
            case 6: $month = "6月"; break;
            case 7: $month = "7月"; break;
            case 8: $month = "8月"; break;
            case 9: $month = "9月"; break;
            case 10: $month = "10月"; break;
            case 11: $month = "11月"; break;
            case 12: $month = "12月"; 
        }

        return $month . $third;
    }

}