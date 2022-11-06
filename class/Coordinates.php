<?php

class Coordinates {
    
    public $coordinates = [];
    public $time = [];
    
    function __construct ($coordinates, $time = NULL) {
        forEach($coordinates as $lngLat) {
            $lngLat = new LngLat($lngLat[0], $lngLat[1]);
            array_push($this->coordinates, $lngLat);
        }
        if ($time) {
            forEach($time as $datetimestamp) {
                $datetime = new DateTime();
                $datetime->setTimestamp($datetimestamp / 1000);
                $datetime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
                array_push($this->time, $datetime->format('Y-m-d H:i:s'));
            }
        }
    }

    // Create a route from these coordinates
    public function createRoute ($author_id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail = false, $tunnels) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        
        // Prepare start and goal place strings
        $startplace = $startplace['city'] . ' (' . $startplace['prefecture'] . ')';
        $goalplace = $goalplace['city'] . ' (' . $goalplace['prefecture'] . ')';

        // If creation
        if ($route_id == 'new') {
            // Save route summary
            $posting_date = date('Y-m-d H:i:s');
            $insertRoute = $db->prepare('INSERT INTO routes(author_id, category, posting_date, name, description, distance, elevation, startplace, goalplace, thumbnail) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insertRoute->execute(array($author_id, $category, $posting_date, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail));
            // Save route coords
            $getRouteId = $db->prepare('SELECT id FROM routes WHERE author_id = ? AND posting_date = ? AND name = ?');
            $getRouteId->execute(array($author_id, $posting_date, $name));
            $route_id = $getRouteId->fetch()['id'];
            for ($i = 0; $i < count($this->coordinates); $i++) {
                $number   = $i;
                $lng      = $this->coordinates[$i]->lng;
                $lat      = $this->coordinates[$i]->lat;
                if ($this->time != NULL) {
                    $datetime = $this->time[$i];
                    $insertCoords = $db->prepare('INSERT INTO coords(segment_id, number, lng, lat, datetime) VALUES (?, ?, ?, ?, ?)');
                    $insertCoords->execute(array($route_id, $number, $lng, $lat, $datetime));
                } else {
                    $insertCoords = $db->prepare('INSERT INTO coords(segment_id, number, lng, lat) VALUES (?, ?, ?, ?)');
                    $insertCoords->execute(array($route_id, $number, $lng, $lat));
                }
            }
            // Save tunnels coords
            if (!empty($tunnels)) for ($tunnels_cursor = 0; $tunnels_cursor < count($tunnels); $tunnels_cursor++) {
                for ($coords_cursor = 0; $coords_cursor < count($tunnels[$tunnels_cursor]); $coords_cursor++) {
                    $lng = $tunnels[$tunnels_cursor][$coords_cursor][0];
                    $lat = $tunnels[$tunnels_cursor][$coords_cursor][1];
                    $insertCoords = $db->prepare('INSERT INTO tunnels(tunnel_id, number, segment_id, lng, lat) VALUES (?, ?, ?, ?, ?)');
                    $insertCoords->execute(array($tunnels_cursor, $coords_cursor, $route_id, $lng, $lat));
                }
            }
            return $route_id;
        
        // If update
        } else {
            // Update route summary
            if ($thumbnail) {
                $updateRoute = $db->prepare('UPDATE routes SET category = ?, name = ?, description = ?, distance = ?, elevation = ?, startplace = ?, goalplace = ?, thumbnail = ? WHERE id = ?');
                $updateRoute->execute(array($category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail, $route_id));
            } else {
                $updateRoute = $db->prepare('UPDATE routes SET category = ?, name = ?, description = ?, distance = ?, elevation = ?, startplace = ?, goalplace = ? WHERE id = ?');
                $updateRoute->execute(array($category, $name, $description, $distance, $elevation, $startplace, $goalplace, $route_id));
            }
            // Update route coords
            $deletePreviousCoords = $db->prepare('DELETE FROM coords WHERE segment_id = ?');
            $deletePreviousCoords->execute(array($route_id));
            for ($i = 0; $i < count($this->coordinates); $i++) {
                $number = $i;
                $lng    = $this->coordinates[$i]->lng;
                $lat    = $this->coordinates[$i]->lat;
                $insertCoords = $db->prepare('INSERT INTO coords(segment_id, number, lng, lat) VALUES (?, ?, ?, ?)');
                $insertCoords->execute(array($route_id, $number, $lng, $lat));
            }
            // Save tunnels coords
            $deletePreviousTunnels = $db->prepare('DELETE FROM tunnels WHERE segment_id = ?');
            $deletePreviousTunnels->execute(array($route_id));
            for ($tunnels_cursor = 0; $tunnels_cursor < count($tunnels); $tunnels_cursor++) {
                for ($coords_cursor = 0; $coords_cursor < count($tunnels[$tunnels_cursor]); $coords_cursor++) {
                    $lng = $tunnels[$tunnels_cursor][$coords_cursor][0];
                    $lat = $tunnels[$tunnels_cursor][$coords_cursor][1];
                    $insertCoords = $db->prepare('INSERT INTO tunnels(tunnel_id, number, segment_id, lng, lat) VALUES (?, ?, ?, ?, ?)');
                    $insertCoords->execute(array($tunnels_cursor, $coords_cursor, $route_id, $lng, $lat));
                }
            }
        }
        return $route_id;
    }

    // Create a segment (and a route) from these coordinates
    public function createSegment ($author_id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail, $tunnels, $rank, $favourite, $seasons, $advice, $specs, $tags) {
        
        // Create route
        $route_id = $this->createRoute($author_id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail, $tunnels);

        // Prepare variables
        if ($favourite == 'on') $favourite = 1;
        else $favourite = 0;
        forEach ($specs as $key => $index) {
            if ($index == 'on') $specs[$key] = 1; // true
            if ($index == 'off') $specs[$key] = 0; // false
        }
        forEach ($tags as $key => $index) {
            if ($index == 'on') $tags[$key] = 1; // true
            if ($index == 'off') $tags[$key] = 0; // false
        }
        if (empty($advice)) {
            $advice['name'] = NULL;
            $advice['description'] = NULL;
        }
        $popularity = 30;
        
        // Save segment
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $insertSegment = $db->prepare('INSERT INTO segments(route_id, rank, name, description, favourite, advice_name, advice_description, spec_offroad, spec_rindo, spec_cyclinglane, spec_cyclingroad, tag_hanami, tag_kouyou, tag_ajisai, tag_culture, tag_machinami, tag_shrines, tag_teafields, tag_sea, tag_mountains, tag_forest, tag_rivers, tag_lakes, popularity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insertSegment->execute(array($route_id, $rank, $name, $description, $favourite, $advice['name'], $advice['description'], $specs['offroad'], $specs['rindo'], $specs['cyclinglane'], $specs['cyclingroad'], $tags['hanami'], $tags['kouyou'], $tags['ajisai'], $tags['culture'], $tags['machinami'], $tags['shrines'], $tags['teafields'], $tags['sea'], $tags['mountains'], $tags['forest'], $tags['rivers'], $tags['lakes'], $popularity));

        // Get segment id
        $getSegmentId = $db->prepare('SELECT id FROM segments WHERE route_id = ? AND name = ?');
        $getSegmentId->execute(array($route_id, $name));
        $segment_id = $getSegmentId->fetch()['id'];

        // Save seasons
        if (!empty($seasons)) {
            forEach($seasons as $key => $season) {
                $insertSeason = $db->prepare('INSERT INTO segment_seasons(segment_id, number, period_start_month, period_start_detail, period_end_month, period_end_detail, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $insertSeason->execute(array($segment_id, $key + 1, $season['start'][1], $season['start'][0], $season['end'][1], $season['end'][0], $season['description']));
            }
        }

        return $segment_id;
    }

}