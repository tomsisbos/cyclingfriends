<?php

class SegmentTags extends Segment {

    function __construct($segment_id = NULL) {
        $this->id               = $segment_id;
        $data = $this->getData($this->table);
        $this->hanami    = $data['tag_hanami'];
        $this->kouyou    = $data['tag_kouyou'];
        $this->ajisai    = $data['tag_ajisai'];
        $this->culture   = $data['tag_culture'];
        $this->machinami = $data['tag_machinami'];
        $this->shrines   = $data['tag_shrines'];
        $this->teafields = $data['tag_teafields'];
        $this->sea       = $data['tag_sea'];
        $this->mountains = $data['tag_mountains'];
        $this->forest    = $data['tag_forest'];
        $this->rivers    = $data['tag_rivers'];
        $this->lakes     = $data['tag_lakes'];
    }

}