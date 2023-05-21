<?php

use Location\Coordinate;
use Location\Line;
use Location\Polyline;
use Location\Distance\Vincenty;
use Location\Utility\PointToLineDistance;

class Route extends Model {
    
    private $container_name = 'route-thumbnails';
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
    public $coordinates;
    public $thumbnail_filename;
    public $time;
    public $tunnels;
    
    function __construct($id = NULL, $lngLatFormat = true) {
        parent::__construct();
        $this->id                 = $id;
        $data = $this->getData($this->table);
        $this->author             = new User($data['author_id']);
        $this->category           = $data['category'];
        $this->posting_date       = new Datetime($data['posting_date']);
        $this->posting_date->setTimezone(new DateTimeZone('Asia/Tokyo'));
        $this->name               = $data['name'];
        $this->description        = $data['description'];
        $this->distance           = floatval($data['distance']);
        $this->elevation          = floatval($data['elevation']);
        $this->startplace         = $data['startplace'];
        $this->goalplace          = $data['goalplace'];
        if ($lngLatFormat) $this->coordinates = $this->getLinestring($lngLatFormat)->coordinates;
        else $this->coordinates = $this->getLinestring($lngLatFormat)->getArray();
        $this->thumbnail_filename = $data['thumbnail_filename'];
        $this->time               = $this->getTime();
        $this->tunnels            = $this->getTunnels();
    }

    /**
     * Retrieve time data from database
     */
    private function getTime () {
        $getTime = $this->getPdo()->prepare('SELECT timearray FROM linestrings WHERE segment_id = ?');
        $getTime->execute(array($this->id));
        $result = $getTime->fetch(PDO::FETCH_COLUMN);
        if ($result) {
            $timearray = json_decode($result);
            $time = [];
            foreach ($timearray as $timestamp) {
                $datetime = new DateTime($timestamp, new DateTimeZone('Asia/Tokyo'));
                array_push($time, $datetime);
            }
            return $time;
        } else return NULL;
    }

    /**
     * Check if a point is inside a certain range from route
     * @param Coordinate $point point to check for
     * @param int $range range
     * @return boolean|array false if outside range, array containing remoteness and closest point if inside range
     */
    private function inRange ($point, $range) {

        $closest_point = [];

        // Step of route coordinates to evaluate (defined accordingly to number of route coords for optimization purposes)
        if (count($this->coordinates) > 500) $step = 5;
        else if (count($this->coordinates) > 100 && count($this->coordinates) < 500) $step = 2;
        else $step = 1;

        // For points inside this range, test remoteness for each route segment on a step
        if ($this->isPointInRoughArea($point, $range)) {

            $remoteness_min = 500000000;
            for ($j = 0; $j < count($this->coordinates) - $step - 1; $j += $step) {
                $line = new Line(
                    new Coordinate($this->coordinates[$j]->lat, $this->coordinates[$j]->lng),
                    new Coordinate($this->coordinates[$j + $step]->lat, $this->coordinates[$j + $step]->lng)
                );
                $pointToLineDistanceCalculator = new PointToLineDistance(new Vincenty());
                $segment_remoteness = $pointToLineDistanceCalculator->getDistance($point, $line);
                if ($segment_remoteness < $remoteness_min) { // If distance is the shortest calculated until this point, then erase distance_min record
                    $remoteness_min = $segment_remoteness;
                    $closest_point = $this->coordinates[$j];
                }
            }
            return ['remoteness' => $remoteness_min, 'closest_point' => $closest_point];
        } else return false;
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
        return $coordinates;
    }

    public function getThumbnail () {
        // Connect to blob storage
        require Route::$root_folder . '/actions/blobStorageAction.php';

        // Retrieve blob url
        return $blobClient->getBlobUrl($this->container_name, $this->thumbnail_filename);
    }

    public function getFeaturedImage () {
        // Get close sceneries
        $sceneries_on_route = $this->getCloseSceneries(300);

        // If more than one scenery is on the course, use the most liked photo among them
        if (count($sceneries_on_route) > 0) {
            $images = [];
            foreach ($sceneries_on_route as $scenery) array_push($images, $scenery->getImages(1)[0]);
            usort($images, function ($a, $b) {
                return $a->likes <=> $b->likes;
            } );
            return $images[0];
        }

        // If no scenery is on the course, return closest activity photo from the route if exists
        else {

            $activity_photos = $this->getPublicPhotos();
            $photos = [];
            foreach ($activity_photos as $photo_data) {
                $photo = new ActivityPhoto($photo_data['id']);
                $photo->remoteness = $photo_data['remoteness'];
                $photo->closest_point = $photo_data['closest_point'];
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
        $routeCoords = $this->coordinates;
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
     * @param boolean $append_distance whether append remoteness and distance or not
     */
    public function getPublicPhotos ($tolerance = 3000, $append_distance = true) {

        // Get all public activity photos registered in the database
        $d = 0.0015; // About 300m
        $getPublicPhotos = $this->getPdo()->prepare("
            SELECT
                id
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
            return new ActivityPhoto($photo['id']);
        }, $result);
        return $public_photos;
    }

    /** Get Sceneries that are less than [basis] km from the route (with remoteness and distance to start appended if $append_distance param is true)
     * @param int $tolerance tolerance remoteness from route in meters
     * @return Scenery[]
     */
    public function getCloseSceneries ($tolerance = 3000, $classFormat = true, $append_distance = false) { // m

        // Get all Sceneries registered in the database
        $getSceneries = $this->getPdo()->prepare('SELECT id, name, ST_X(point) as lng, ST_Y(point) as lat FROM sceneries');
        $getSceneries->execute();
        $sceneries = $getSceneries->fetchAll(PDO::FETCH_ASSOC);
        $sceneries_in_range = [];

        // Filter sceneries inside a certain range from route
        for ($i = 0; $i < count($sceneries); $i++) {
            $range_data = $this->inRange(new Coordinate($sceneries[$i]['lat'], $sceneries[$i]['lng']), $tolerance * 10);
            if ($range_data) {
                $sceneries[$i]['remoteness'] = $range_data['remoteness'];
                $sceneries[$i]['closest_point'] = $range_data['closest_point'];
                array_push($sceneries_in_range, $sceneries[$i]);
            }
        }

        // Return an array of Sceneries less than [tolerance] from route
        $close_sceneries = array();
        if (isset($sceneries_in_range[0]['distance'])) {
            $distance_column = array_column($sceneries_in_range, 'distance');
            array_multisort($distance_column, SORT_ASC, $sceneries_in_range);
        }
        foreach ($sceneries_in_range as $scenery_data) {
            // If scenery is located inside tolerance zone
            if (isset($scenery_data['remoteness'])) {
                if ($scenery_data['remoteness'] < $tolerance) {
                    // Calculate distance from start
                    if ($append_distance) {
                        $sublineCoords = array_slice($this->coordinates, 0, array_search($scenery_data['closest_point'], $this->coordinates));
                        $subline = new Polyline();
                        forEach ($sublineCoords as $lngLat) {
                            $coordinates = new Coordinate($lngLat->lat, $lngLat->lng);
                            $subline->addPoint($coordinates);
                        }
                        $scenery_data['distance'] = $subline->getLength(new Vincenty());
                    }
                    // If classFormat is set to true, build scenery object and append relevant data to it
                    if ($classFormat) {
                        $scenery = new Scenery($scenery_data['id']);
                        if ($scenery_data['remoteness'] < 200) $scenery->on_route = true;
                        else {
                            $scenery->on_route = false;
                            $scenery->remoteness = $scenery_data['remoteness']; // Append remoteness from the route
                        }
                        if ($append_distance) $scenery->distance = $scenery_data['distance']; // Append distance from the start of the route
                    // Else, only return id and relevant data 
                    } else {
                        if ($scenery_data['remoteness'] < 200) $scenery = ['id' => $scenery_data['id'], 'on_route' => true];
                        else $scenery = ['id' => $scenery_data['id'], 'on_route' => false, 'remoteness' => $scenery_data['remoteness']];
                        if ($append_distance) $scenery['distance'] = $scenery_data['distance'];
                    }
                    // Add it to close_sceneries array
                    array_push($close_sceneries, $scenery);
                }
            }
        }

        return $close_sceneries;
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
        $sceneries = $this->getCloseSceneries($tolerance);
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