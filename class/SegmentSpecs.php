<?php

class SegmentSpecs extends Segment {

    function __construct($segment_id = NULL) {
        $this->id               = $segment_id;
        $data = $this->getData($this->table);
        $this->offroad     = $data['spec_offroad'];
        $this->rindo       = $data['spec_rindo'];
        $this->cyclinglane = $data['spec_cyclinglane'];
        $this->cyclingroad = $data['spec_cyclingroad'];
    }

}