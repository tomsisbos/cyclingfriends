<?php

use Location\Coordinate;
use Location\Line;
use Location\Polyline;
use Location\Distance\Vincenty;
use Location\Utility\PointToLineDistance;

class Route extends Model {
    
    protected $table = 'routes';
    public $id;
    public $author;
    public $category;
    public $posting_date;
    public $name;
    public $description;
    public $distance;
    public $elevation;
    public $coordinates;
    public $tunnels;
    
    function __construct($id = NULL, $lngLatFormat = true) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($this->table);
        $this->author = new User($data['author_id']);
        $this->category = $data['category'];
        $this->posting_date = $data['posting_date'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->distance = floatval($data['distance']);
        $this->elevation = floatval($data['elevation']);
        $this->startplace = $data['startplace'];
        $this->goalplace = $data['goalplace'];
        $this->coordinates = $this->getCoordinates($lngLatFormat);
        $this->time = $this->getTime();
        $this->tunnels = $this->getTunnels();
    }

    private function getCoordinates ($lngLatFormat) {
        $getCoords = $this->getPdo()->prepare('SELECT lng, lat FROM coords WHERE segment_id = ? ORDER BY number ASC');
        $getCoords->execute(array($this->id));
        $coords = $getCoords->fetchAll();
        $coordinates = [];
        forEach($coords as $lngLat) {
            if ($lngLatFormat) $lngLat = new LngLat($lngLat[0], $lngLat[1]);
            else $lngLat = [floatval($lngLat[0]), floatval($lngLat[1])];
            array_push($coordinates, $lngLat);
        }
        return $coordinates;
    }

    private function getTime () {
        $getTime = $this->getPdo()->prepare('SELECT datetime FROM coords WHERE segment_id = ? ORDER BY number ASC');
        $getTime->execute(array($this->id));
        $timedata = $getTime->fetchAll();
        $time = [];
        forEach($timedata as $data) {
            $datetime = new DateTime($data['datetime']);
            array_push($time, $datetime);
        }
        return $time;
    }

    public function getThumbnail () {
        $getThumbnail = $this->getPdo()->prepare('SELECT thumbnail FROM routes WHERE id = ?');
        $getThumbnail->execute(array($this->id));
        return $getThumbnail->fetch(PDO::FETCH_NUM)[0];
    }

    public function getFeaturedImage () {
        return new RouteFeaturedImage($this->id);
    }

    public function getTunnels () {
        $tunnels = [];
        $getTunnelsNumber = $this->getPdo()->prepare('SELECT DISTINCT tunnel_id FROM tunnels WHERE segment_id = ?');
        $getTunnelsNumber->execute(array($this->id));
        $tunnels_number = $getTunnelsNumber->rowCount();
        for ($i = 0 ; $i < $tunnels_number; $i++) {
            $getTunnelCoords = $this->getPdo()->prepare('SELECT lng, lat FROM tunnels WHERE tunnel_id = ? AND segment_id = ?');
            $getTunnelCoords->execute(array($i, $this->id));
            $tunnels[$i] = $getTunnelCoords->fetchAll(PDO::FETCH_NUM);
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
        if ($level === 'Beginner') {
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
        if ($level === 'Intermediate') {
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
        if ($level === 'Athlete') {
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
        $time->setTime($hours, $minutes);
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

    // Get Mkpoints that are less than [basis] km from the route
    public function getCloseMkpoints ($tolerance = 3000, $classFormat = true, $append_distance = false) { // m

        // Get all Mkpoints registered in the database
        $getMkpoints = $this->getPdo()->prepare('SELECT id, name, lng, lat FROM map_mkpoint');
        $getMkpoints->execute(array($this->id));
        $mkpoints = $getMkpoints->fetchAll(PDO::FETCH_ASSOC);
        $mkpoints_in_range = [];
        $number = 0;

        // Get route coords
        $routeCoords = $this->coordinates;

        // For each mkpoint, check remoteness to route
        for ($i = 0; $i < count($mkpoints); $i++) {

            $point = new Coordinate($mkpoints[$i]['lat'], $mkpoints[$i]['lng']);
            $closest_point = [];
            $range = $tolerance * 10;

            // Step of route coordinates to evaluate (defined accordingly to number of route coords for optimization purposes)
            if (count($routeCoords) > 500) $step = 5;
            else if (count($routeCoords) > 100 && count($routeCoords) < 500) $step = 2;
            else $step = 1;

            // For mkpoints inside this range, test remoteness for each route segment on a step
            if ($this->isPointInRoughArea($point, $range)) {
                array_push($mkpoints_in_range, $mkpoints[$i]);

                $remoteness_min = 500000000;
                $routeCoords = $this->coordinates;
                $simplifiedRouteCoords = [];
                for ($j = 0; $j < count($routeCoords) - $step - 1; $j += $step) {
                    array_push($simplifiedRouteCoords, $routeCoords[$j]);
                    $line = new Line(
                        new Coordinate($routeCoords[$j]->lat, $routeCoords[$j]->lng),
                        new Coordinate($routeCoords[$j + $step]->lat, $routeCoords[$j + $step]->lng)
                    );
                    $pointToLineDistanceCalculator = new PointToLineDistance(new Vincenty());
                    $segment_remoteness = $pointToLineDistanceCalculator->getDistance($point, $line);
                    if ($segment_remoteness < $remoteness_min) { // If distance is the shortest calculated until this point, then erase distance_min record
                        $remoteness_min = $segment_remoteness;
                        $closest_point = $routeCoords[$j];
                    }
                }
                $mkpoints_in_range[$number]['remoteness'] = $remoteness_min;
                $mkpoints_in_range[$number]['closest_point'] = $closest_point;
                $number++;
            }
        }

        // Return an array of Mkpoints less than [tolerance] from the line
        $close_mkpoints = array();
        if (isset($mkpoints_in_range[0]['distance'])) {
            $distance_column = array_column($mkpoints_in_range, 'distance');
            array_multisort($distance_column, SORT_ASC, $mkpoints_in_range);
        }
        foreach ($mkpoints_in_range as $mkpoint_data) {
            // If mkpoint is located inside tolerance zone
            if (isset($mkpoint_data['remoteness'])) {
                if ($mkpoint_data['remoteness'] < $tolerance) {
                    // Calculate distance from start
                    if ($append_distance) {
                        $sublineCoords = array_slice($simplifiedRouteCoords, 0, array_search($mkpoint_data['closest_point'], $simplifiedRouteCoords));
                        $subline = new Polyline();
                        forEach ($sublineCoords as $lngLat) {
                            $coordinates = new Coordinate($lngLat->lat, $lngLat->lng);
                            $subline->addPoint($coordinates);
                        }
                        $mkpoint_data['distance'] = $subline->getLength(new Vincenty());
                    }
                    // If classFormat is set to true, build mkpoint object and append relevant data to it
                    if ($classFormat) {
                        $mkpoint = new Mkpoint($mkpoint_data['id']);
                        if ($mkpoint_data['remoteness'] < 200) $mkpoint->on_route = true;
                        else {
                            $mkpoint->on_route = false;
                            $mkpoint->remoteness = $mkpoint_data['remoteness']; // Append remoteness from the route
                        }
                        if ($append_distance) $mkpoint->distance = $mkpoint_data['distance']; // Append distance from the start of the route
                    // Else, only return id and relevant data 
                    } else {
                        if ($mkpoint_data['remoteness'] < 200) $mkpoint = ['id' => $mkpoint_data['id'], 'on_route' => true];
                        else $mkpoint = ['id' => $mkpoint_data['id'], 'on_route' => false, 'remoteness' => $mkpoint_data['remoteness']];
                        if ($append_distance) $mkpoint['distance'] = $mkpoint_data['distance'];
                    }
                    // Add it to close_mkpoints array
                    array_push($close_mkpoints, $mkpoint);
                }
            }
        }

        return $close_mkpoints;
    }

    public function delete () {
        // Get route name
        $getRouteName = $this->getPdo()->prepare('SELECT name FROM routes WHERE id = ?');
        $getRouteName->execute(array($this->id));
        $route_name = $getRouteName->fetch(PDO::FETCH_NUM)[0];
        // Delete route summary
        $deleteRoute = $this->getPdo()->prepare('DELETE FROM routes WHERE id = ?');
        $deleteRoute->execute(array($this->id));
        // Delete route coords
        $deleteCoords = $this->getPdo()->prepare('DELETE FROM coords WHERE segment_id = ?');
        $deleteCoords->execute(array($this->id));
        // Delete route tunnels
        $deleteTunnels = $this->getPdo()->prepare('DELETE FROM tunnels WHERE segment_id = ?');
        $deleteTunnels->execute(array($this->id));
        return $route_name. ' has been successfully deleted.';
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

    public function getMkpointsWithPhotos ($tolerance = 300) {
        // Get mkpoints on route
        $mkpoints = $this->getCloseMkpoints($tolerance);
        // Get corresponding photos
        foreach ($mkpoints as $mkpoint) {
            $mkpoint->photos = $mkpoint->getImages();
        }
        return $mkpoints;
    }

    // Return all scenery point photos found on the route
    public function getPhotos ($tolerance = 300) {
        $photos = [];
        foreach ($this->getMkpointsWithPhotos($tolerance) as $mkpoint) {
            foreach ($mkpoint->photos as $photo) {
                array_push($photos, $photo);
            }
        }
        return $photos;
    }

    public function getItinerary ($tolerance = 300) {

        $spots = [];
        $connected_user = new User($_SESSION['id']);
        $cleared_mkpoints = $connected_user->getClearedMkpoints();

        foreach ($this->getCloseMkpoints($tolerance, true, true) as $mkpoint) {
            $spot = ['type' => 'mkpoint', 'icon' => $mkpoint->thumbnail, 'id' => $mkpoint->id, 'on_route' => $mkpoint->on_route, 'distance' => $mkpoint->distance, 'name' => $mkpoint->name, 'city' => $mkpoint->city, 'prefecture' => $mkpoint->prefecture, 'elevation' => $mkpoint->elevation, 'viewed' => false];
            if (isset($mkpoint->remoteness)) $spot['remoteness'] = $mkpoint->remoteness;
            if (in_array_r($mkpoint->id, $cleared_mkpoints)) $spot['viewed'] = true;
            array_push($spots, $spot);
        }

        function compareDistance ($a, $b) {
            return $a['distance'] > $b['distance'];
        }
        usort($spots, "compareDistance");

        return $spots;
    }

}