<?php

class CFLinestring extends Model {
    
    protected $table = 'linestrings';
    public $coordinates = [];
    public $time = [];
    public $length = 0;
    
    function __construct ($coordinates = NULL, $time = NULL) {
        parent::__construct();
        if ($coordinates) {
            forEach($coordinates as $lngLat) {
                $lngLat = new LngLat($lngLat[0], $lngLat[1]);
                array_push($this->coordinates, $lngLat);
            }
            $this->length = count($coordinates);
        }
        if ($time) forEach($time as $datetimestamp) {
            $datetime = new DateTime($datetimestamp / 1000, new DateTimeZone('Asia/Tokyo'));
            array_push($this->time, $datetime);
        }
    }

    /**
     * Returns a json array of timestamps
     */
    private function timeToJson () {
        $timestamps = array_map(function ($datetime) {
            return $datetime->getTimestamp();
        }, $this->time);
        return json_encode($timestamps);
    }

    /**
     * Save linestring in database
     * @param int $segment_id id of segment to refer to
     */
    private function saveLinestring ($segment_id) {
        if ($this->time == null) {
            $save = $this->getPdo()->prepare("INSERT INTO {$this->table} (segment_id, linestring) VALUES (?, ST_LineStringFromText(?))");
            $save->execute([$segment_id, $this->toWKT()]);
        } else {
            $save = $this->getPdo()->prepare("INSERT INTO {$this->table} (segment_id, linestring, timearray) VALUES (?, ST_LineStringFromText(?), ?)");
            $save->execute([$segment_id, $this->toWKT(), $this->timeToJson()]);
        }
    }
    
    /**
     * Returns a linestring at WKT format
     */
    protected function toWKT () {
        $linestring_wkt = 'LINESTRING(';
        for ($i = 0; $i < count($this->coordinates); $i++) {
            if ($i > 0) $linestring_wkt .= ', ';
            $linestring_wkt .= $this->coordinates[$i]->lng. ' ' .$this->coordinates[$i]->lat;
        }
        $linestring_wkt .= ')';
        return $linestring_wkt;
    }

    /**
     * Loads coordinates from a WKT formatted point
     * @param string $linestring_wkt the string to convert
     */
    public function fromWKT ($linestring_wkt) {
        $linestring = geoPHP::load($linestring_wkt, 'wkt');
        $i = 1;
        $coordinates = [];
        while ($point = $linestring->pointN($i)) {
            array_push($coordinates, new LngLat($point->getX(), $point->gety()));
            $i++;
        }
        $this->coordinates = $coordinates;
    }

    /**
     * Get coordinates as a numeric array 
     */
    public function getArray () {
        $array = array_map(function ($lngLat) {
            return $lngLat->getArray();
        }, $this->coordinates);
        return $array;
    }

    // Create a route from these coordinates
    public function createRoute ($author_id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail = false, $tunnels = [], $loading_record = NULL) {

        // Prepare start and goal place strings
        $startplace = $startplace['city'] . ' (' . $startplace['prefecture'] . ')';
        $goalplace = $goalplace['city'] . ' (' . $goalplace['prefecture'] . ')';

        // Generate filename and save thumbnail blob to blob server
        $file = base64_to_jpeg($thumbnail, $_SERVER["DOCUMENT_ROOT"]. '/media/temp/thumb_temp.jpg');
        $stream = fopen($file, "r");

        $container_name = 'route-thumbnails';
        $thumbnail_filename = setFilename('thumb');
        $metadata = [
            'route_id' => $route_id,
        ];

        require CFLinestring::$root_folder . '/actions/blobStorageAction.php';
        $blobClient->createBlockBlob($container_name, $thumbnail_filename, $stream);
        $blobClient->setBlobMetadata($container_name, $thumbnail_filename, $metadata);

        // If creation
        if ($route_id == 'new') {

            // Save route summary
			$posting_date = new DateTime('now', new DateTimezone('Asia/Tokyo'));
            $insertRoute = $this->getPdo()->prepare('INSERT INTO routes(author_id, category, posting_date, name, description, distance, elevation, startplace, goalplace, thumbnail_filename) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insertRoute->execute(array($author_id, $category, $posting_date->format('Y-m-d H:i:s'), $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail_filename));
            
            // Get route id
            $getRouteId = $this->getPdo()->prepare('SELECT id FROM routes WHERE author_id = ? AND posting_date = ? AND name = ?');
            $getRouteId->execute(array($author_id, $posting_date->format('Y-m-d H:i:s'), $name));
            $route_id = $getRouteId->fetch(PDO::FETCH_COLUMN);
        
        // If update
        } else {
            // Update route summary
            if ($thumbnail_filename) {
                $updateRoute = $this->getPdo()->prepare('UPDATE routes SET category = ?, name = ?, description = ?, distance = ?, elevation = ?, startplace = ?, goalplace = ?, thumbnail_filename = ? WHERE id = ?');
                $updateRoute->execute(array($category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail_filename, $route_id));
            } else {
                $updateRoute = $this->getPdo()->prepare('UPDATE routes SET category = ?, name = ?, description = ?, distance = ?, elevation = ?, startplace = ?, goalplace = ? WHERE id = ?');
                $updateRoute->execute(array($category, $name, $description, $distance, $elevation, $startplace, $goalplace, $route_id));
            }
            // Delete previous linestring
            $deletePreviousLinestring = $this->getPdo()->prepare('DELETE FROM linestrings WHERE segment_id = ?');
            $deletePreviousLinestring->execute(array($route_id));
            // Delete previous tunnels
            $deletePreviousTunnels = $this->getPdo()->prepare('DELETE FROM tunnels WHERE segment_id = ?');
            $deletePreviousTunnels->execute(array($route_id));
        }
        
        // Save tunnels
        for ($i = 0; $i < count($tunnels); $i++) {
            $tunnel = new Tunnel($tunnels[$i]);
            $tunnel->saveTunnel($route_id, $i);
        }
        
        // Save linestring
        $this->saveLinestring($route_id);
        
        return $route_id;
    }

    // Create a segment (and a route) from these coordinates
    public function createSegment ($author_id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail_filename, $tunnels, $rank, $advised, $seasons, $advice, $specs, $tags) {
        
        // Create route
        $route_id = $this->createRoute($author_id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail_filename, $tunnels);

        // Prepare variables
        if ($advised == 'on') $advised = 1;
        else $advised = 0;
        forEach ($specs as $key => $index) {
            if ($index == 'on') $specs[$key] = 1; // true
            if ($index == 'off') $specs[$key] = 0; // false
        }
        if (empty($advice)) {
            $advice['name'] = NULL;
            $advice['description'] = NULL;
        }
        $popularity = 30;
        
        // Save segment
        $insertSegment = $this->getPdo()->prepare('INSERT INTO segments(route_id, rank, name, description, advised, advice_name, advice_description, spec_offroad, spec_rindo, spec_cyclinglane, spec_cyclingroad, popularity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insertSegment->execute(array($route_id, $rank, $name, $description, $advised, $advice['name'], $advice['description'], $specs['offroad'], $specs['rindo'], $specs['cyclinglane'], $specs['cyclingroad'], $popularity));

        // Get segment id
        $getSegmentId = $this->getPdo()->prepare('SELECT id FROM segments WHERE route_id = ? AND name = ?');
        $getSegmentId->execute(array($route_id, $name));
        $segment_id = $getSegmentId->fetch()['id'];

        // Save seasons
        if (!empty($seasons)) {
            forEach($seasons as $key => $season) {
                $insertSeason = $this->getPdo()->prepare('INSERT INTO segment_seasons(segment_id, number, period_start_month, period_start_detail, period_end_month, period_end_detail, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $insertSeason->execute(array($segment_id, $key + 1, $season['start'][1], $season['start'][0], $season['end'][1], $season['end'][0], $season['description']));
            }
        }

        // Save tags
        forEach ($tags as $tag) {
            $insertTag = $this->getPdo()->prepare('INSERT INTO tags(object_type, object_id, tag) VALUES (?, ?, ?)');
            $insertTag->execute(array('segment', $segment_id, $tag));
        }

        return $segment_id;
    }

}