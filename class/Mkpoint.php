<?php

use Location\Coordinate;
use Location\Line;
use Location\Distance\Vincenty;
use Location\Utility\PointToLineDistance;

class Mkpoint extends Model {
    
    protected $table = 'map_mkpoint';
    
    function __construct($id = NULL) {
        parent::__construct();
        $this->id               = $id;
        $this->type             = 'scenery';
        $this->container_name   = 'scenery-photos';
        $data = $this->getData($this->table);
        $this->user_id          = $data['user_id'];
        $this->category         = $data['category'];
        $this->name             = $data['name'];
        $this->city             = $data['city'];
        $this->prefecture       = $data['prefecture'];
        $this->elevation        = $data['elevation'];
        $this->date             = $data['date'];
        $this->period           = $this->getPeriod();
        $this->month            = $data['month'];
        $this->description      = $data['description'];
        $this->thumbnail        = $data['thumbnail'];
        $this->lngLat           = new LngLat($data['lng'], $data['lat']);
        $this->publication_date = new Datetime($data['publication_date']);
        $this->rating           = $data['rating'];
        $this->grades_number    = $data['grades_number'];
        $this->popularity       = $data['popularity'];
        $this->likes            = $data['likes'];
    }

    private function getPeriod() {
        
        // Get part of the month from the day
        $day = date("d", strtotime($this->date));
        if ($day < 10) $third = "上旬";
        else if (($day >= 10) AND ($day <= 20)) $third = "中旬";
        else if ($day > 20) $third = "下旬";

        // Get month in letters
        switch (date("n", strtotime($this->date))) {
            case 1: $month = "1月"; break;
            case 2: $month = "2月"; break;
            case 3: $month = "3月"; break;
            case 4: $month = "4月"; break;
            case 5: $month = "5月"; break;
            case 6: $month = "6月"; break;
            case 7: $month = "7月"; break;
            case 8: $month = "8月"; break;
            case 9: $month = "9月"; break;
            case 10: $month = "10月"; break;
            case 11: $month = "11月"; break;
            case 12: $month = "12月"; 
        }

        return $month . $third;
    }

    public function getAuthor () {
        return new User($this->user_id);
    }

    public function delete () {
        // Connect to blob storage and delete relevant blobs
        require Mkpoint::$root_folder . '/actions/blobStorageAction.php';
        foreach ($this->getImages() as $photo) $blobClient->deleteBlob($this->container_name, $photo->filename);

        // Remove database entry
        $removeMkpointPhoto = $this->getPdo()->prepare('DELETE FROM img_mkpoint WHERE id = ?');
        foreach ($this->getImages() as $photo) $removeMkpointPhoto->execute(array($photo->id));

        // Remove mkpoint data
        $removeMkpoint = $this->getPdo()->prepare('DELETE FROM map_mkpoint WHERE id = ?');
        $removeMkpoint->execute(array($this->id));
        // Remove favorite data
        $removeMkpointFavorites = $this->getPdo()->prepare('DELETE FROM favorites WHERE object_type = ? AND object_id = ?');
        $removeMkpointFavorites->execute(array('scenery', $this->id));
        // Remove user scenery data
        $removeUserMkpoint = $this->getPdo()->prepare('DELETE FROM user_mkpoints WHERE mkpoint_id = ?');
        $removeUserMkpoint->execute(array($this->id));
        // Remove photo data
        $removeMkpointPhotos = $this->getPdo()->prepare('DELETE FROM img_mkpoint WHERE mkpoint_id = ?');
        $removeMkpointPhotos->execute(array($this->id));
        // Remove tags data
        $removeMkpointTags = $this->getPdo()->prepare('DELETE FROM tags WHERE object_type = ? AND object_id = ?');
        $removeMkpointTags->execute(array('scenery', $this->id));
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

    public function postReview ($content) {
        $connected_user = new User($_SESSION['id']);
        $propic  = $connected_user->getPropicUrl();
        $time    = date('Y-m-d H:i:s');

        // Check if user has already posted a review
        $reviews = $this->getUserReview($connected_user);
        // If there is one..
        if (!empty($reviews)) {
            // ..and if content is not empty, update it
            if (!empty($content)) {
                $updateReview = $this->getPdo()->prepare('UPDATE mkpoint_reviews SET content = ?, time = ? WHERE mkpoint_id = ? AND user_id = ?');
                $updateReview->execute(array($content, $time, $this->id, $connected_user->id));
            // ..and if content is empty, delete it
            } else {
                $deleteReview = $this->getPdo()->prepare('DELETE FROM mkpoint_reviews WHERE mkpoint_id = ? AND user_id = ?');
                $deleteReview->execute(array($this->id, $connected_user->id));
            }

        // Else, insert into mkpoint_reviews table
        } else {
            $insertReview = $this->getPdo()->prepare('INSERT INTO mkpoint_reviews(mkpoint_id, user_id, user_login, content, time) VALUES (?, ?, ?, ?, ?)');
            $insertReview->execute(array($this->id, $connected_user->id, $connected_user->login, $content, $time));
            $this->notify($this->user_id, 'scenery_review_posting');
        }
    }

    public function getTags () {
        $getTags = $this->getPdo()->prepare('SELECT tag FROM tags WHERE object_type = ? AND object_id = ?');
        $getTags->execute(array($this->type, $this->id));
        $tags_data = $getTags->fetchAll(PDO::FETCH_ASSOC);
        if (count($tags_data) == 0 || $tags_data[0]['tag'] == '') return [];
        else {
            $tags = [];
            foreach ($tags_data as $tag_data) {
                array_push($tags, $tag_data['tag']);
            }
            return $tags;
        }
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
        $review_id = $getUserReview->fetch(PDO::FETCH_COLUMN);
        if ($review_id) return new MkpointReview($review_id);
        else return false;
    }

    // Get mkpoint images
    public function getImages ($number = 99) {
        $getImages = $this->getPdo()->prepare("SELECT id FROM img_mkpoint WHERE mkpoint_id = ? ORDER BY likes LIMIT {$number}");
        $getImages->execute(array($this->id));
        $images_data = $getImages->fetchAll(PDO::FETCH_ASSOC);
        $images = [];
        for ($i = 0; $i < count($images_data); $i++) array_push($images, new MkpointImage($images_data[$i]['id']));
        // Sort images by likes number
        usort($images, function ($a, $b) {
            return ($b->likes <=> $a->likes);
        } );
        return $images;
    }

    public function toggleFavorites () {
        if ($this->isFavorite()) {
            $removeFromFavorites = $this->getPdo()->prepare('DELETE FROM favorites WHERE user_id = ? AND object_type = ? AND object_id = ?');
            $removeFromFavorites->execute(array($_SESSION['id'], $this->type, $this->id));
            return ['success' => $this->name . 'は<a class="in-success" href="/favorites/sceneries">お気に入りリスト</a>から削除されました。'];
        } else {
            $insertIntoFavorites = $this->getPdo()->prepare('INSERT INTO favorites (user_id, object_type, object_id) VALUES (?, ?, ?)');
            $insertIntoFavorites->execute(array($_SESSION['id'], $this->type, $this->id));
            return ['success' => $this->name . 'は<a class="in-success" href="/favorites/sceneries">お気に入りリスト</a>に追加されました !'];
        }
    }

    public function isFavorite () {
        $isFavorite = $this->getPdo()->prepare('SELECT id FROM favorites WHERE user_id = ? AND object_type = ? AND object_id = ?');
        $isFavorite->execute(array($_SESSION['id'], $this->type, $this->id));
        if ($isFavorite->rowCount() > 0) return true;
        else return false;
    }

    public function findLastRelatedActivities ($limit) {
        $i = 0;
        $offset = 0;
        $lastRelatedActivites = [];
        while ($i < $limit) {
            $getCloseActivitiy = $this->getPdo()->prepare("SELECT activities.id FROM activities INNER JOIN routes ON activities.route_id = routes.id WHERE routes.category = 'activity' AND (routes.goalplace LIKE '%" .$this->prefecture. "%' OR routes.startplace LIKE '%" .$this->prefecture. "%') LIMIT 1 OFFSET " .$offset);
            $getCloseActivitiy->execute();
            if ($getCloseActivitiy->rowCount() > 0) {
                $activity_data = $getCloseActivitiy->fetch(PDO::FETCH_ASSOC);
                $activity = new Activity($activity_data['id']);
                $range = 10000;
                if ($activity->route->isPointInRoughArea(new Coordinate($this->lngLat->lat, $this->lngLat->lng), $range)) {
                    if ($this->getRemoteness($activity->route) < 500) array_push($lastRelatedActivites, $activity);
                }
                $offset++;
            } else return $lastRelatedActivites;
        }
    }

    public function getRemoteness ($route, $step = 5) {
        $remoteness_min = 500000000;
        $routeCoords = $route->coordinates;
        $simplifiedRouteCoords = [];
        for ($j = 0; $j < count($routeCoords) - $step - 1; $j += $step) {
            array_push($simplifiedRouteCoords, $routeCoords[$j]);
            $line = new Line(
                new Coordinate($routeCoords[$j]->lat, $routeCoords[$j]->lng),
                new Coordinate($routeCoords[$j + $step]->lat, $routeCoords[$j + $step]->lng)
            );
            $pointToLineDistanceCalculator = new PointToLineDistance(new Vincenty());
            $segment_remoteness = $pointToLineDistanceCalculator->getDistance(new Coordinate($this->lngLat->lat, $this->lngLat->lng), $line);
            if ($segment_remoteness < $remoteness_min) $remoteness_min = $segment_remoteness;
        }
        return $remoteness_min;
    }

    public function isCleared () {
        $isCleared = $this->getPdo()->prepare('SELECT DISTINCT activity_id FROM user_mkpoints WHERE user_id = ? AND mkpoint_id = ?');
        $isCleared->execute(array($_SESSION['id'], $this->id));
        if ($isCleared->rowCount() > 0) return $isCleared->fetch(PDO::FETCH_NUM)[0];
        else return false;
    }

}