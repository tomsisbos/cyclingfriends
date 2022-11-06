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
        if (isset($data['featured_mkpoint_img_id_1'])) $this->featured_mkpoint_img = [$data['featured_mkpoint_img_id_1'], $data['featured_mkpoint_img_id_2'], $data['featured_mkpoint_img_id_3'], ];
    }

    // Build period string from an array containing period of the month and number of the month
    public function getPeriodStart () {
        switch ($this->period_start[1]) {
            case 1: $first = 'early '; break;
            case 2: $first = 'mid '; break;
            case 3: $first = 'late '; break;
        }
        switch ($this->period_start[0]) {
            case 1: $second = 'january'; break;
            case 2: $second = 'february'; break;
            case 3: $second = 'march'; break;
            case 4: $second = 'april'; break;
            case 5: $second = 'may'; break;
            case 6: $second = 'june'; break;
            case 7: $second = 'july'; break;
            case 8: $second = 'august'; break;
            case 9: $second = 'september'; break;
            case 10: $second = 'october'; break;
            case 11: $second = 'november'; break;
            case 12: $second = 'december'; break;
        }
        return $first . $second;
    }

    // Build period string from an array containing period of the month and number of the month
    public function getPeriodEnd () {
        switch ($this->period_end[1]) {
            case 1: $first = 'early '; break;
            case 2: $first = 'mid '; break;
            case 3: $first = 'late '; break;
        }
        switch ($this->period_end[0]) {
            case 1: $second = 'january'; break;
            case 2: $second = 'february'; break;
            case 3: $second = 'march'; break;
            case 4: $second = 'april'; break;
            case 5: $second = 'may'; break;
            case 6: $second = 'june'; break;
            case 7: $second = 'july'; break;
            case 8: $second = 'august'; break;
            case 9: $second = 'september'; break;
            case 10: $second = 'october'; break;
            case 11: $second = 'november'; break;
            case 12: $second = 'december'; break;
        }
        return $first . $second;
    }


}