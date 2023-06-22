<?php

class Guide extends User {

    public $rank;
    public $ride;
    public $position;
    
    function __construct($user_id = NULL, $ride_id = NULL, $position = NULL) {
        parent::__construct($user_id);
        $this->rank = $this->getRank();
        if (isset($ride_id)) $this->ride = new Ride($ride_id);
        if (isset($position)) $this->position = intval($position);
    }

    private function getRank () {
        $getGuideRank = $this->getPdo()->prepare("SELECT rank FROM user_guides WHERE user_id = ?");
        $getGuideRank->execute([$this->id]);
        return intval($getGuideRank->fetch(PDO::FETCH_COLUMN));
    }

    /**
     * Get rank string from rank id
     * @return string Rank string
     */
    public function getRankString () {
        return $this->getGuideRankString($this->rank);
    }

    /**
     * Get position string from position id
     * @return string Position string
     */
    public function getPositionString () {
        switch ($this->position) {
            case 1: return 'チーフ';
            case 2: return 'アシスタント';
            case 3: return '研修生';
        }
    }
}