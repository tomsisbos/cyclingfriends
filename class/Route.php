<?php

use Location\Coordinate;
use Location\Line;
use Location\Polyline;
use Location\Distance\Vincenty;
use Location\Utility\PointToLineDistance;

class Route extends Model {
    
    private $container_name = 'route-thumbnails';
    private $lngLatFormat;
    protected $table = 'routes';
    public $id;
    public $author;
    public $category;
    public $posting_date;
    public $name;
    public $description;
    public $distance;
    public $elevation;
    public $startplace;
    public $goalplace;
    public $thumbnail_filename;
    public $tunnels;
    
    function __construct($id = NULL, $lngLatFormat = true) {
        parent::__construct();
        $this->id                 = $id;
        $this->lngLatFormat       = $lngLatFormat;
        $data = $this->getData($this->table);
        $this->author             = new User($data['author_id']);
        $this->category           = $data['category'];
        $this->posting_date       = new Datetime($data['posting_date']);
        $this->name               = $data['name'];
        $this->description        = $data['description'];
        $this->distance           = floatval($data['distance']);
        $this->elevation          = floatval($data['elevation']);
        $this->startplace         = new Geolocation(explode('(' , rtrim($data['startplace'], ')'))[0], explode('(' , rtrim($data['startplace'], ')'))[1], 'JP');
        $this->goalplace          = new Geolocation(explode('(' , rtrim($data['goalplace'], ')'))[0], explode('(' , rtrim($data['goalplace'], ')'))[1], 'JP');
        $this->thumbnail_filename = $data['thumbnail_filename'];
        $this->tunnels            = $this->getTunnels();
    }

    /**
     * Retrieve coordinates data from database
     * @param boolean $lngLatFormat whether retrieve coordinates as a Linestring instance or as a simple array of coordinates
     */
    public function getLinestring () {
        $getCoords = $this->getPdo()->prepare('SELECT ST_AsWKT(linestring) FROM linestrings WHERE segment_id = ?');
        $getCoords->execute(array($this->id));
        $linestring_wkt = $getCoords->fetch(PDO::FETCH_COLUMN);
        $coordinates = new CFLinestring();
        $coordinates->fromWKT($linestring_wkt);
        if ($this->lngLatFormat) return $coordinates;
        else return $coordinates->getArray();
    }

    /**
     * Retrieve time data from database
     */
    public function getTime () {
        $getTime = $this->getPdo()->prepare('SELECT time_array FROM linestrings WHERE segment_id = ?');
        $getTime->execute(array($this->id));
        $result = $getTime->fetch(PDO::FETCH_COLUMN);
        if ($result) {
            $time_array = json_decode($result);
            $time = [];
            foreach ($time_array as $timestamp) {
                $datetime = new DateTime();
                $datetime->setTimestamp($timestamp);
                $datetime->setTimezone(new DateTimeZone('Asia/Tokyo'));
                array_push($time, $datetime);
            }
            return $time;
        } else return NULL;
    }

    public function getThumbnail () {
        // Connect to blob storage
        require Route::$root_folder . '/actions/blobStorageAction.php';

        // Retrieve blob url
        return $blobClient->getBlobUrl($this->container_name, $this->thumbnail_filename);
    }

    public function getFeaturedImage () {

        // Get close sceneries
        $sceneries_on_route = $this->getLineString()->getCloseSceneryIds();

        // If more than one scenery is on the course, use the most liked photo among them
        if (count($sceneries_on_route) > 0) {
            $getMostLikedPhoto = $this->getPdo()->prepare("SELECT p.id FROM scenery_photos AS p JOIN sceneries AS s ON s.id = p.scenery_id WHERE s.id IN (".implode(',', $sceneries_on_route).") ORDER BY p.likes DESC LIMIT 1");
            $getMostLikedPhoto->execute();
            $photo_id = $getMostLikedPhoto->fetch(PDO::FETCH_COLUMN);
            return new SceneryImage($photo_id);
        }

        // If no scenery is on the course, return closest activity photo from the route if exists
        else {

            $activity_photos = $this->getPublicPhotos();
            $photos = [];
            foreach ($activity_photos as $photo_data) {
                $photo = new ActivityPhoto($photo_data->id);
                $photo->remoteness = $photo_data->remoteness;
                array_push($photos, $photo);
            }
            $remoteness_column = array_column($photos, 'remoteness');
            array_multisort($remoteness_column , SORT_ASC, $photos);
            if (count($photos)) return $photos[0];
            else return false;
        }
    }

    /**
     * Get tunnels related to this route
     * @return Tunnel[]
     */
    public function getTunnels () {
        $getLinestring = $this->getPdo()->prepare('SELECT ST_AsWKT(linestring) FROM tunnels WHERE segment_id = ?');
        $getLinestring->execute(array($this->id));
        $tunnels = [];
        while ($linestring_wkt = $getLinestring->fetch(PDO::FETCH_COLUMN)) {
            $tunnel = new Tunnel();
            $tunnel->fromWKT($linestring_wkt);
            array_push($tunnels, $tunnel->getArray());
        }
        return $tunnels;
    }

    // Calculate overall route difficulty (distance included)
    public function calculateDifficulty () {
        $toughness = $this->calculateToughness();
        return ($toughness + ($this->distance / 1.3)) / 1.2;
    }

    // Calculate route toughness (distance/elevation ratio)
    public function calculateToughness () {
        return ($this->elevation / $this->distance) * 1.4;
    }

    public function getStars ($score) {
        $rank = $this->getRank($score);
        switch ($rank) {
            case 5: return '★★★★★';
            case 4: return '★★★★☆';
            case 3: return '★★★☆☆';
            case 2: return '★★☆☆☆';
            case 1: return '★☆☆☆☆';
            case 0: return '☆☆☆☆☆';
        }
    }

    public function getRank ($score) {
        if ($score > 100) {
            return 5;
        } else if ($score <= 100 && $score > 75) {
            return 4;
        } else if ($score <= 75 && $score > 55) {
            return 3;
        } else if ($score <= 55 && $score > 30) {
            return 2;
        } else if ($score <= 30 && $score > 15) {
            return 1;
        } else if ($score <= 15) {
            return 0;
        }
    }

    // Calculate estimated time depending on rider level
    public function calculateEstimatedTime ($level) {
        $toughness = $this->calculateToughness();
        $toughnessRank = $this->getRank($toughness);

        // Beginners
        if ($level == 1) {
            switch ($toughnessRank) {
                case 5: $averageSpeed = 13; break;
                case 4: $averageSpeed = 15.5; break;
                case 3: $averageSpeed = 17; break;
                case 2: $averageSpeed = 19; break;
                case 1: $averageSpeed = 20.5; break;
                case 0: $averageSpeed = 22; break;
            }
        }
        // Intermediates
        else if ($level == 2) {
            switch ($toughnessRank) {
                case 5: $averageSpeed = 16; break;
                case 4: $averageSpeed = 18.5; break;
                case 3: $averageSpeed = 20.5; break;
                case 2: $averageSpeed = 22; break;
                case 1: $averageSpeed = 23.5; break;
                case 0: $averageSpeed = 25; break;
            }
        }
        // Athletes
        else if ($level == 3) {
            switch ($toughnessRank) {
                case 5: $averageSpeed = 18; break;
                case 4: $averageSpeed = 20.5; break;
                case 3: $averageSpeed = 22.5; break;
                case 2: $averageSpeed = 24; break;
                case 1: $averageSpeed = 26; break;
                case 0: $averageSpeed = 28; break;
            }
        }

        $int_date = $this->distance / $averageSpeed;
        $hours = floor($int_date);
        $minutes = ($int_date - $hours) * 60;
        $time = new DateTime();
        $time->setTime($hours, floor($minutes));
        return $time;
    }

    // Check if $point is located inside $range from a straight line from start to half and half to goal
    public function isPointInRoughArea($point, $range) { // $point = Coordinate
        $routeCoords = $this->getLinestring()->coordinates;
        $first_core_line = new Line(
            new Coordinate($routeCoords[0]->lat, $routeCoords[0]->lng),
            new Coordinate($routeCoords[floor(count($routeCoords) / 2)]->lat, $routeCoords[floor(count($routeCoords) / 2)]->lng)
        );
        $second_core_line = new Line(
            new Coordinate($routeCoords[floor(count($routeCoords) / 2)]->lat, $routeCoords[floor(count($routeCoords) / 2)]->lng),
            new Coordinate($routeCoords[count($routeCoords) - 1]->lat, $routeCoords[count($routeCoords) - 1]->lng)
        );
        $pointToLineDistanceCalculator = new PointToLineDistance(new Vincenty());
        $first_rough_remoteness = $pointToLineDistanceCalculator->getDistance($point, $first_core_line);
        $second_rough_remoteness = $pointToLineDistanceCalculator->getDistance($point, $second_core_line);
        if ($first_rough_remoteness < $range || $second_rough_remoteness < $range) return true;
        else return false;
    }

    /**
     * Get public photos on the route (with remoteness and distance to start appended if $append_distance param is true)
     * @param int $tolerance tolerance remoteness from route in meters
     * @return ActivityPhoto
     */
    public function getPublicPhotos ($tolerance = 300) {

        // Get all public activity photos registered in the database
        $d = $tolerance / 200000; // 0.0015 = about 300m
        $getPublicPhotos = $this->getPdo()->prepare("
            SELECT
                id,
                ST_Distance(point, (SELECT linestring FROM linestrings WHERE segment_id = {$this->id})) as 'remoteness'
            FROM activity_photos
            WHERE
                ST_IsEmpty(point) = 0
                    AND
                privacy = 'public'
                    AND
                ST_Intersects(point, ST_Buffer((SELECT linestring FROM linestrings WHERE segment_id = {$this->id}), {$d}))
        ");
        $getPublicPhotos->execute();
        $result = $getPublicPhotos->fetchAll(PDO::FETCH_ASSOC);
        $public_photos = array_map(function ($photo) {
            $activity_photo = new ActivityPhoto($photo['id']);
            $activity_photo->remoteness = $photo['remoteness'];
            return $activity_photo;
        }, $result);
        return $public_photos;
    }

    public function delete () {
        $message = $this->name. 'が削除されました。';
        // Delete route summary
        $deleteRoute = $this->getPdo()->prepare('DELETE FROM routes WHERE id = ?');
        $deleteRoute->execute(array($this->id));
        // Delete route linestring
        $deleteCoords = $this->getPdo()->prepare('DELETE FROM linestrings WHERE segment_id = ?');
        $deleteCoords->execute(array($this->id));
        // Delete route tunnels
        $deleteTunnels = $this->getPdo()->prepare('DELETE FROM tunnels WHERE segment_id = ?');
        $deleteTunnels->execute(array($this->id));
        return $message;
    }

    public function getTerrainValue () {
        $toughness = $this->calculateToughness();
        if ($this->distance > 30) {
            if ($toughness > 25) $terrain_value = 'Mountains';
            else if ($toughness > 18) $terrain_value = 'Hills';
            else if ($toughness > 8) $terrain_value = 'Small hills';
            else $terrain_value = 'Flat';
        } else if ($this->distance > 10) {
            if ($toughness > 35) $terrain_value = 'Mountains';
            else if ($toughness > 22) $terrain_value = 'Hills';
            else if ($toughness > 12) $terrain_value = 'Small hills';
            else $terrain_value = 'Flat';
        } else if ($this->distance > 5) {
            if ($toughness > 60) $terrain_value = 'Mountains';
            else if ($toughness > 30) $terrain_value = 'Hills';
            else if ($toughness > 15) $terrain_value = 'Small hills';
            else $terrain_value = 'Flat';
        } else {
            if ($toughness > 60) $terrain_value = 'Hills';
            else if ($toughness > 20) $terrain_value = 'Small hills';
            else $terrain_value = 'Flat';
        }
        return $terrain_value;
    }

    public function getSceneriesWithPhotos ($tolerance = 300) {
        // Get sceneries on route
        $sceneries = $this->getLinestring()->getCloseSceneries($tolerance);
        // Get corresponding photos
        foreach ($sceneries as $scenery) {
            $scenery->photos = $scenery->getImages();
        }
        return $sceneries;
    }

    // Return all scenery point photos found on the route
    public function getPhotos ($tolerance = 300) {
        $photos = [];
        foreach ($this->getSceneriesWithPhotos($tolerance) as $scenery) {
            foreach ($scenery->photos as $photo) {
                array_push($photos, $photo);
            }
        }
        return $photos;
    }

}