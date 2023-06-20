<?php

class RideReport extends Model {
    
    protected $table = 'ride_reports';
    public static $report_types = ['activity_id', 'photoalbum_url', 'video_url'];
    public $id;
    public $ride_id;
    public $activity_id;
    public $video_url;
    public $photoalbum_url;
    
    function __construct ($id = NULL) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($this->table);
        if (isset($data['ride_id'])) $this->ride_id = intval($data['ride_id']);
        if (isset($data['activity_id'])) $this->activity_id = intval($data['activity_id']);
        if (isset($data['video_url'])) $this->video_url = $data['video_url'];
        if (isset($data['photoalbum_url'])) $this->photoalbum_url = $data['photoalbum_url'];
    }

    private function getVideoId () {
        $exploded = explode('/', $this->video_url);
        return end($exploded);
    }

    public function getActivity () {
        return new Activity($this->activity_id);
    }

    public function getVideoIframe ($width = 720, $height = 405) {
        return '<iframe class="responsive-iframe" id="ytplayer" type="text/html" width="' .$width. '" height="' .$height. '" src="https://www.youtube.com/embed/' .$this->getVideoId(). '?autoplay=1&list=PLh5FHR57HS40VebyT_ZD5acIwlcJto-b4&listType=playlist&loop=1&modestbranding=1&color=white" frameborder="0" allowfullscreen></iframe>';
    }

}