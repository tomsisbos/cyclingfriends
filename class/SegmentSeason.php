<?php

class SegmentSeason extends Segment {
    
    protected $table = 'segment_seasons';

    function __construct($id = NULL) {
        $this->id           = $id;
        $data = $this->getData($this->table);
        $this->segment_id   = $data['segment_id'];
        $this->number       = $data['number'];
        $this->period_start = $data['period_start'];
        $this->period_end   = $data['period_end'];
        $this->description  = $data['description'];
        if (isset($data['featured_mkpoint_img_id_1'])) $this->featured_mkpoint_img = [$data['featured_mkpoint_img_id_1'], $data['featured_mkpoint_img_id_2'], $data['featured_mkpoint_img_id_3'], ];
    }

}