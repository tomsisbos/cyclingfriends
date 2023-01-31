<?php

class SegmentSeason extends Segment {
    
    protected $table = 'segment_seasons';

    function __construct($id = NULL) {
        $this->id           = $id;
        $data = $this->getData($this->table);
        $this->segment_id   = intval($data['segment_id']);
        $this->number       = intval($data['number']);
        $this->period_start = ['month' => intval($data['period_start_month']), 'detail' => intval($data['period_start_detail'])];
        $this->period_end   = ['month' => intval($data['period_end_month']), 'detail' => intval($data['period_end_detail'])];
        $this->description  = $data['description'];
    }

    // Build period string from an array containing period of the month and number of the month
    public function getPeriodStart () {
        switch ($this->period_start[1]) {
            case 1: $first = '上旬'; break;
            case 2: $first = '中旬'; break;
            case 3: $first = '下旬'; break;
        }
        switch ($this->period_start[0]) {
            case 1: $second = '1月'; break;
            case 2: $second = '2月'; break;
            case 3: $second = '3月'; break;
            case 4: $second = '4月'; break;
            case 5: $second = '5月'; break;
            case 6: $second = '6月'; break;
            case 7: $second = '7月'; break;
            case 8: $second = '8月'; break;
            case 9: $second = '9月'; break;
            case 10: $second = '10月'; break;
            case 11: $second = '11月'; break;
            case 12: $second = '12月'; break;
        }
        return $second . $first;
    }

    // Build period string from an array containing period of the month and number of the month
    public function getPeriodEnd () {
        switch ($this->period_end[1]) {
            case 1: $first = '上旬'; break;
            case 2: $first = '中旬'; break;
            case 3: $first = '下旬'; break;
        }
        switch ($this->period_end[0]) {
            case 1: $second = '1月'; break;
            case 2: $second = '2月'; break;
            case 3: $second = '3月'; break;
            case 4: $second = '4月'; break;
            case 5: $second = '5月'; break;
            case 6: $second = '6月'; break;
            case 7: $second = '7月'; break;
            case 8: $second = '8月'; break;
            case 9: $second = '9月'; break;
            case 10: $second = '10月'; break;
            case 11: $second = '11月'; break;
            case 12: $second = '12月'; break;
        }
        return $second . $first;
    }


}