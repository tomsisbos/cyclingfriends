<?php

class Mkpoint extends Model {
    
    protected $table = 'map_mkpoint';
    
    function __construct($id = NULL) {
        parent::__construct();
        $this->id               = $id;
        $this->type             = 'scenery';
        $data = $this->getData($this->table);
        $this->user             = new User($data['user_id']);
        $this->category         = $data['category'];
        $this->name             = $data['name'];
        $this->city             = $data['city'];
        $this->prefecture       = $data['prefecture'];
        $this->elevation        = $data['elevation'];
        $this->date             = $data['date'];
        $this->month            = $data['month'];
        $this->period           = $data['period'];
        $this->description      = $data['description'];
        $this->thumbnail        = $data['thumbnail'];
        $this->lngLat           = new LngLat($data['lng'], $data['lat']);
        $this->publication_date = new Datetime($data['publication_date']);
        $this->rating           = $data['rating'];
        $this->grades_number    = $data['grades_number'];
        $this->popularity       = $data['popularity'];
        $this->likes            = $data['likes'];
    }

    public function getReviews () {
        $getReviews = $this->getPdo()->prepare('SELECT id FROM mkpoint_reviews WHERE mkpoint_id = ? ORDER BY time DESC');
        $getReviews->execute(array($this->id));
        $reviews_data = $getReviews->fetchAll(PDO::FETCH_ASSOC);
        $reviews = [];
        foreach ($reviews_data as $review_data) {
            array_push($reviews, new MkpointReview($review_data['id']));
        }
        return $reviews;
    }

    // Get connected user's vote information
    public function getUserVote ($user) {
        $checkUserVote = $this->getPdo()->prepare('SELECT grade FROM grade_mkpoint WHERE mkpoint_id = ? AND user_id = ?');
        $checkUserVote->execute(array($this->id, $user->id));
        if ($checkUserVote->rowCount() > 0) {
            $vote_infos = $checkUserVote->fetch(PDO::FETCH_ASSOC);
            return $vote_infos['grade'];
        } else {
            return false;
        }
    }

    public function getUserReview ($user) {
        $getUserReview = $this->getPdo()->prepare('SELECT id FROM mkpoint_reviews WHERE mkpoint_id = ? AND user_id = ?');
        $getUserReview->execute(array($this->id, $user->id));
        $review_id = $getUserReview->fetch(PDO::FETCH_NUM)[0];
        if (!empty($review_id)) return new MkpointReview($review_id);
        else return false;
    }

    // Get mkpoint images
    public function getImages () {
        $getImages = $this->getPdo()->prepare('SELECT id FROM img_mkpoint WHERE mkpoint_id = ?');
        $getImages->execute(array($this->id));
        $images_data = $getImages->fetchAll(PDO::FETCH_ASSOC);
        $images = [];
        for ($i = 0; $i < count($images_data); $i++) array_push($images, new MkpointImage($images_data[$i]['id']));
        // Sort images by likes number
        usort($images, function ($a, $b) {
            return ($a->likes < $b->likes);
        } );
        return $images;
    }

    public function toggleFavorites () {
        if ($this->isFavorite()) {
            $removeFromFavorites = $this->getPdo()->prepare('DELETE FROM favorites WHERE user_id = ? AND object_type = ? AND object_id = ?');
            $removeFromFavorites->execute(array($_SESSION['id'], $this->type, $this->id));
            return ['success' => $this->name . 'has been removed from your favorites list.'];
        } else {
            $insertIntoFavorites = $this->getPdo()->prepare('INSERT INTO favorites (user_id, object_type, object_id) VALUES (?, ?, ?)');
            $insertIntoFavorites->execute(array($_SESSION['id'], $this->type, $this->id));
            return ['success' => $this->name . 'has been added to your favorites list !'];
        }
    }

    public function isFavorite () {
        $isFavorite = $this->getPdo()->prepare('SELECT id FROM favorites WHERE user_id = ? AND object_type = ? AND object_id = ?');
        $isFavorite->execute(array($_SESSION['id'], $this->type, $this->id));
        if ($isFavorite->rowCount() > 0) return true;
        else return false;
    }

}