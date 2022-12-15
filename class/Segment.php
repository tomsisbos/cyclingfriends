<?php

class Segment extends Model {
    
    protected $table = 'segments';
    
    function __construct($id = NULL, $lngLatFormat = true) {
        parent::__construct();
        $this->id = $id;
        $this->type               = 'segment';
        $data = $this->getData($this->table);
        $this->route              = new Route($data['route_id'], $lngLatFormat);
        $this->rank               = $data['rank'];
        $this->name               = $data['name'];
        $this->description        = $data['description'];
        $this->advised            = (intval($data['advised']) === 1);
        $this->advice             = new SegmentAdvice($this->id);
        $this->seasons            = $this->getSeasons();
        $this->specs              = new SegmentSpecs($this->id);
        $this->tags               = $this->getTags();
        $this->rating             = intval($data['rating']);
        $this->grades_number      = intval($data['grades_number']);
        $this->popularity         = intval($data['popularity']);
        $this->views              = intval($data['views']);
    }

    private function getSeasons () {
        $getSeasons = $this->getPdo()->prepare('SELECT id FROM segment_seasons WHERE segment_id = ? ORDER BY number');
        $getSeasons->execute(array($this->id));
        $seasons_data = $getSeasons->fetchAll(PDO::FETCH_ASSOC);
        $seasons = [];
        foreach ($seasons_data as $season_data) {
            array_push($seasons, new SegmentSeason($season_data['id']));
        }
        return $seasons;
    }

    public function getTags () {
        $getTags = $this->getPdo()->prepare('SELECT tag FROM tags WHERE object_type = ? AND object_id = ?');
        $getTags->execute(array($this->type, $this->id));
        $tags_data = $getTags->fetchAll(PDO::FETCH_ASSOC);
        $tags = [];
        foreach ($tags_data as $tag_data) {
            array_push($tags, $tag_data['tag']);
        }
        return $tags;
    }

    // Get connected user's vote information
    public function getUserVote ($user) {
        $checkUserVote = $this->getPdo()->prepare('SELECT grade FROM segment_grade WHERE segment_id = ? AND user_id = ?');
        $checkUserVote->execute(array($this->id, $user->id));
        if ($checkUserVote->rowCount() > 0) {
            $vote_infos = $checkUserVote->fetch(PDO::FETCH_ASSOC);
            return $vote_infos['grade'];
        } else return false;
    }

    public function getFeaturedImage ($blob = false) {

        // Get all photos in an array
        $photos = $this->route->getPhotos();

        // If at least one photo has been found, return the one with the most likes number
        if (count($photos) > 0) {
            
            usort($photos, (function ($a, $b) {
                return strcmp($b->likes, $a->likes);
            } ) );

            if ($blob) return $photos[0]->blob;
            else return 'data:image/jpeg;base64,' .$photos[0]->blob;
        
        // If no photo has been found, return default image
        } else {

            if ($blob) return false;
            else return '/media/default-photo-' . rand(1,9) .'.svg';

        }

    }

    public function toggleFavorites () {
        if ($this->isFavorite()) {
            $removeFromFavorites = $this->getPdo()->prepare('DELETE FROM favorites WHERE user_id = ? AND object_type = ? AND object_id = ?');
            $removeFromFavorites->execute(array($_SESSION['id'], $this->type, $this->id));
            return ['success' => $this->name . ' has been removed from <a class="in-success" href="/favorites/segments">your favorites list</a>.'];
        } else {
            $insertIntoFavorites = $this->getPdo()->prepare('INSERT INTO favorites (user_id, object_type, object_id) VALUES (?, ?, ?)');
            $insertIntoFavorites->execute(array($_SESSION['id'], $this->type, $this->id));
            return ['success' => $this->name . ' has been added to <a class="in-success" href="/favorites/segments">your favorites list</a> !'];
        }
    }

    public function isFavorite () {
        $isFavorite = $this->getPdo()->prepare('SELECT id FROM favorites WHERE user_id = ? AND object_type = ? AND object_id = ?');
        $isFavorite->execute(array($_SESSION['id'], $this->type, $this->id));
        if ($isFavorite->rowCount() > 0) return true;
        else return false;
    }

    public function isCleared () {
        $isCleared = $this->getPdo()->prepare('SELECT DISTINCT activity_id FROM user_segments WHERE user_id = ? AND segment_id = ?');
        $isCleared->execute(array($_SESSION['id'], $this->id));
        if ($isCleared->rowCount() > 0) return $isCleared->fetch(PDO::FETCH_NUM)[0];
        else return false;
    }

}