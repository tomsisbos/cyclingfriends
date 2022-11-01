<?php

class SegmentTags extends Segment {

    function __construct($segment_id = NULL) {
        $this->id               = $segment_id;
        $data = $this->getData($this->table);
        $this->hanami    = intval($data['tag_hanami']);
        $this->kouyou    = intval($data['tag_kouyou']);
        $this->ajisai    = intval($data['tag_ajisai']);
        $this->culture   = intval($data['tag_culture']);
        $this->machinami = intval($data['tag_machinami']);
        $this->shrines   = intval($data['tag_shrines']);
        $this->teafields = intval($data['tag_teafields']);
        $this->sea       = intval($data['tag_sea']);
        $this->mountains = intval($data['tag_mountains']);
        $this->forest    = intval($data['tag_forest']);
        $this->rivers    = intval($data['tag_rivers']);
        $this->lakes     = intval($data['tag_lakes']);
    }

}