<?php

use Location\Coordinate;
use Location\Line;
use Location\Distance\Vincenty;
use Location\Utility\PointToLineDistance;

class Scenery extends Model {
    
    protected $table = 'sceneries';
    
    function __construct($id = NULL) {
        parent::__construct();
        $this->id               = $id;
        $this->type             = 'scenery';
        $this->container_name   = 'scenery-photos';
        if ($id != NULL) {
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
            $this->lngLat           = $this->getLngLat();
            $this->publication_date = new Datetime($data['publication_date']);
            $this->rating           = $data['rating'];
            $this->grades_number    = $data['grades_number'];
            $this->popularity       = $data['popularity'];
            $this->likes            = $data['likes'];
        }
    }

    private function getLngLat () {
        $getPointToText = $this->getPdo()->prepare("SELECT ST_AsText(point) FROM {$this->table} WHERE id = ?");
        $getPointToText->execute([$this->id]);
        $point_text = $getPointToText->fetch(PDO::FETCH_COLUMN);
        $lngLat = new LngLat();
        $lngLat->fromWKT($point_text);
        return $lngLat;
    }

    public function getAuthor () {
        return new User($this->user_id);
    }

    /**
     * Register a new scenery entry in the database based on $scenery_data
     * @param array $scenery_data array containing necessary data (scenery data, scenery photos data, scenery tags data)
     */
    public function create ($scenery_data) {

        // Convert lng and lat to WKT format
        $lngLat = new LngLat($scenery_data['lng'], $scenery_data['lat']);
        $point_wkt = $lngLat->toWKT();

        // Insert scenery data
        $scenery_data['id'] = getNextAutoIncrement($this->table); // Get id for referring photos
        $insertSceneryData = $this->getPdo()->prepare("INSERT INTO sceneries (user_id, user_login, category, name, city, prefecture, elevation, date, month, description, thumbnail, publication_date, popularity, point) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ST_GeomFromText(?))");
        $insertSceneryData->execute(array($scenery_data['user_id'], $scenery_data['user_login'], $scenery_data['category'], $scenery_data['name'], $scenery_data['city'], $scenery_data['prefecture'], $scenery_data['elevation'], $scenery_data['date']->format('Y-m-d H:i:s'), $scenery_data['month'], $scenery_data['description'], $scenery_data['thumbnail'], $scenery_data['publication_date']->format('Y-m-d H:i:s'), $scenery_data['popularity'], $point_wkt));

        // Connect to blob storage
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/blobStorage.php';

        // Insert photos data
        foreach ($scenery_data['photos'] as $photo) {
            $insertPhotos = $this->getPdo()->prepare('INSERT INTO scenery_photos (scenery_id, user_id, date, likes, filename) VALUES (?, ?, ?, ?, ?)');
            $insertPhotos->execute(array($scenery_data['id'], $scenery_data['user_id'], $scenery_data['date']->format('Y-m-d H:i:s'), 0, $photo['filename']));
            
            // Send file to blob storage
            $containername = 'scenery-photos';
            $blobClient->createBlockBlob($containername, $photo['filename'], $photo['blob']);
            // Set file metadata
            $metadata = [
                'file_name' => $photo['name'],
                'file_type' => $photo['type'],
                'file_size' => $photo['size'],
                'scenery_id' => $scenery_data['id'],
                'author_id' => $scenery_data['user_id'],
                'date' => $scenery_data['publication_date']->format('Y-m-d H:i:s'),
                'lat' => $scenery_data['lat'],
                'lng' => $scenery_data['lng']
            ];
            $blobClient->setBlobMetadata($containername, $photo['filename'], $metadata);
        }

        // Insert tags data
        if (!empty($scenery_data['tags'][0])) {
            foreach ($scenery_data['tags'] as $tag) {
                $insertTag = $this->getPdo()->prepare('INSERT INTO tags (object_type, object_id, tag) VALUES (?, ?, ?)');
                $insertTag->execute(array('scenery', $scenery_data['id'], $tag));
            }
        }
    }

    public function delete () {
        // Connect to blob storage and delete relevant blobs
        require Scenery::$root_folder . '/actions/blobStorage.php';
        foreach ($this->getImages() as $photo) $blobClient->deleteBlob($this->container_name, $photo->filename);

        // Remove database entry
        $removeSceneryPhoto = $this->getPdo()->prepare('DELETE FROM scenery_photos WHERE id = ?');
        foreach ($this->getImages() as $photo) $removeSceneryPhoto->execute(array($photo->id));

        // Remove scenery data
        $removeScenery = $this->getPdo()->prepare('DELETE FROM sceneries WHERE id = ?');
        $removeScenery->execute(array($this->id));
        // Remove favorite data
        $removeSceneryFavorites = $this->getPdo()->prepare('DELETE FROM favorites WHERE object_type = ? AND object_id = ?');
        $removeSceneryFavorites->execute(array('scenery', $this->id));
        // Remove photo data
        $removeSceneryPhotos = $this->getPdo()->prepare('DELETE FROM scenery_photos WHERE scenery_id = ?');
        $removeSceneryPhotos->execute(array($this->id));
        // Remove tags data
        $removeSceneryTags = $this->getPdo()->prepare('DELETE FROM tags WHERE object_type = ? AND object_id = ?');
        $removeSceneryTags->execute(array('scenery', $this->id));
    }

    public function getReviews () {
        $getReviews = $this->getPdo()->prepare('SELECT id FROM scenery_reviews WHERE entry_id = ? ORDER BY time DESC');
        $getReviews->execute(array($this->id));
        $reviews_data = $getReviews->fetchAll(PDO::FETCH_COLUMN);
        $reviews = [];
        foreach ($reviews_data as $review_id) {
            array_push($reviews, new SceneryReview($review_id));
        }
        return $reviews;
    }

    /**
     * Get stars string according to this scenery rating
     */
    public function getStars () {
        if ($this->rating == 5) return '★★★★★';
        else if ($this->rating >= 4) return '★★★★☆';
        else if ($this->rating >= 3) return '★★★☆☆';
        else if ($this->rating >= 2) return '★★☆☆☆';
        else if ($this->rating >= 1) return '★☆☆☆☆';
        else return '☆☆☆☆☆';
    }

    public function postReview ($content) {
        $user    = new User($_SESSION['id']);
        $propic  = $user->getPropicUrl();
        $time    = (new Datetime('now', new DateTimeZone('Asia/Tokyo')))->format('Y-m-d H:i:s');

        // Check if user has already posted a review
        $reviews = $this->getUserReview($user);
        // If there is one..
        if (!empty($reviews)) {
            // ..and if content is not empty, update it
            if (!empty($content)) {
                $updateReview = $this->getPdo()->prepare('UPDATE scenery_reviews SET content = ?, time = ? WHERE entry_id = ? AND user_id = ?');
                $updateReview->execute(array($content, $time, $this->id, $user->id));
            // ..and if content is empty, delete it
            } else {
                $deleteReview = $this->getPdo()->prepare('DELETE FROM scenery_reviews WHERE entry_id = ? AND user_id = ?');
                $deleteReview->execute(array($this->id, $user->id));
            }

        // Else, insert into scenery_reviews table
        } else {
            $insertReview = $this->getPdo()->prepare('INSERT INTO scenery_reviews(entry_id, user_id, user_login, content, time) VALUES (?, ?, ?, ?, ?)');
            $insertReview->execute(array($this->id, $user->id, $user->login, $content, $time));
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
        $checkUserVote = $this->getPdo()->prepare('SELECT grade FROM scenery_grades WHERE scenery_id = ? AND user_id = ?');
        $checkUserVote->execute(array($this->id, $user->id));
        if ($checkUserVote->rowCount() > 0) {
            $vote_infos = $checkUserVote->fetch(PDO::FETCH_ASSOC);
            return $vote_infos['grade'];
        } else {
            return false;
        }
    }

    public function getUserReview ($user) {
        $getUserReview = $this->getPdo()->prepare('SELECT id FROM scenery_reviews WHERE entry_id = ? AND user_id = ?');
        $getUserReview->execute(array($this->id, $user->id));
        $review_id = $getUserReview->fetch(PDO::FETCH_COLUMN);
        if ($review_id) return new SceneryReview($review_id);
        else return false;
    }

    // Get scenery images
    public function getImages ($number = 99) {
        $getImages = $this->getPdo()->prepare("SELECT id FROM scenery_photos WHERE scenery_id = ? ORDER BY likes LIMIT {$number}");
        $getImages->execute(array($this->id));
        $images_data = $getImages->fetchAll(PDO::FETCH_ASSOC);
        $images = [];
        for ($i = 0; $i < count($images_data); $i++) array_push($images, new SceneryImage($images_data[$i]['id']));
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
        $getCloseActivitiy = $this->getPdo()->prepare("
            SELECT a.id
            FROM activities AS a
            JOIN routes AS r ON a.route_id = r.id
            JOIN linestrings AS l ON r.id = l.segment_id
            WHERE ST_Intersects(ST_Buffer((SELECT point FROM sceneries WHERE id = ?), 0.001), l.linestring)
            ORDER BY datetime DESC
            LIMIT 10
        ");
        $getCloseActivitiy->execute([$this->id]);
        $activity_ids = $getCloseActivitiy->fetchAll(PDO::FETCH_COLUMN);
        return array_map(function ($id) {
            return new Activity($id);
        }, $activity_ids);
    }

    public function getRemoteness ($route, $step = 5) {
        $remoteness_min = 500000000;
        $routeCoords = $route->getLinestring();
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
        return false;/// To implement
    }

    /**
     * Change scenery location
     * @param LngLat new location coordinates
     */
    public function move ($lngLat) {
        $move = $this->getPdo()->prepare("UPDATE {$this->table} SET point = ST_GeomFromText(?) WHERE id = ?");
        $move->execute([$lngLat->toWKT(), $this->id]);
    }

}