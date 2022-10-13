<?php

class SegmentAdvice extends Segment {

    function __construct($segment_id = NULL) {
        $this->id               = $segment_id;
        $data = $this->getData($this->table);
        $this->name        = $data['advice_name'];
        $this->description = $data['advice_description'];
    }

}