<?php

class Segment extends Model {
    
    protected $table = 'segments';
    
    function __construct($id = NULL, $lngLatFormat = true) {
        $this->id = $id;
        $data = $this->getData($this->table);
        $this->route              = new Route($data['route_id'], $lngLatFormat);
        $this->rank               = $data['rank'];
        $this->name               = $data['name'];
        $this->description        = $data['description'];
        $this->favourite          = $data['favourite'];
        $this->advice             = new SegmentAdvice($this->id);
        $this->seasons            = $this->getSeasons();
        $this->specs              = new SegmentSpecs($this->id);
        $this->tags               = new SegmentTags($this->id);
        $this->rating             = $data['rating'];
        $this->grades_number      = $data['grades_number'];
        $this->popularity         = $data['popularity'];
        $this->views              = $data['views'];
    }

    private function getSeasons () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getSeasons = $db->prepare('SELECT id FROM segment_seasons WHERE segment_id = ? ORDER BY number');
        $getSeasons->execute(array($this->id));
        $seasons_data = $getSeasons->fetchAll(PDO::FETCH_ASSOC);
        $seasons = [];
        foreach ($seasons_data as $season_data) {
            array_push($seasons, new SegmentSeason($season_data['id']));
        }
        return $seasons;
    }

    private function getMkpoints () {
        // Get mkpoints on route
        $mkpoints = $this->route->getCloseMkpoints();
        // Get corresponding photos
        foreach ($mkpoints as $mkpoint) {
            $mkpoint->photos = $mkpoint->getImages();
        }
        return $mkpoints;
    }

    // Get connected user's vote information
    public function getUserVote ($user) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $checkUserVote = $db->prepare('SELECT grade FROM segment_grade WHERE segment_id = ? AND user_id = ?');
        $checkUserVote->execute(array($this->id, $user->id));
        if ($checkUserVote->rowCount() > 0) {
            $vote_infos = $checkUserVote->fetch(PDO::FETCH_ASSOC);
            return $vote_infos['grade'];
        } else return false;
    }

}