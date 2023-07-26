<?php

use Geo\Geojson;
use Location\Coordinate;
use Location\Formatter\Coordinate\DecimalDegrees;
use Location\Polyline;
use Location\Processor\Polyline\SimplifyDouglasPeucker;
use Location\Distance\Vincenty;

class CFLinestring extends Model {
    
    protected $table = 'linestrings';

    protected $container_name = 'route-thumbnails';

    public $id;
    
    /**
     * @var LngLat[]|array[] An array of lngLat or float coordinates
     */
    public $coordinates = [];

    public $length = 0;
    
    /**
     * @param LngLat[]|array[] $coordinates An array of lngLat or float coordinates
     */
    function __construct ($coordinates = null) {
        parent::__construct();
        if ($coordinates) {
            forEach($coordinates as $lngLat) {
                if (!($lngLat instanceof LngLat)) $lngLat = new LngLat($lngLat[0], $lngLat[1]);
                array_push($this->coordinates, $lngLat);
            }
            $this->length = count($coordinates);
        }
    }

    /**
     * Save linestring in database
     * @param int $segment_id id of segment to refer to
     */
    protected function saveLinestring ($segment_id) {
        $save = $this->getPdo()->prepare("INSERT INTO {$this->table} (segment_id, linestring) VALUES (?, ST_LineStringFromText(?))");
        $save->execute([$segment_id, $this->toWKT()]);
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
        $this->length = count($coordinates);
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
    public function createRoute ($author_id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $tunnels = [], $loading_record = NULL) {

        // Prepare start and goal place strings
        $startplace = $startplace->city . ' (' . $startplace->prefecture . ')';
        $goalplace = $goalplace->city . ' (' . $goalplace->prefecture . ')';

        // Generate filename and save thumbnail blob to blob server
        $thumbnail = $this->queryStaticMap();

        $thumbnail_filename = setFilename('thumb');
        $metadata = [
            'route_id' => $route_id,
        ];

        require CFLinestring::$root_folder . '/actions/blobStorageAction.php';
        $blobClient->createBlockBlob($this->container_name, $thumbnail_filename, $thumbnail);
        $blobClient->setBlobMetadata($this->container_name, $thumbnail_filename, $metadata);

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

    /**
     * Retrieve a Geojson instance using linestring coordinates set with $properties
     * @param array $properties Properties to append to geojson
     * @return Geojson
     */
    public function toGeojson ($properties = [], $precision = null) {
        if ($precision) $coordinates = $this->simplify($precision);
        else $coordinates = $this->coordinates;
        return new Geojson('LineString', $coordinates, $properties);
    }

    /**
     * @param float $margin Margin in coordinate units
     */
    public function getBBox ($margin = .02) {
        $array_lng = [];
        $array_lat = [];
        foreach ($this->coordinates as $lngLat) {
            array_push($array_lng, $lngLat->lng);
            array_push($array_lat, $lngLat->lat);
        }
        return [new LngLat(min($array_lng) - $margin, min($array_lat) - $margin), new LngLat(max($array_lng) + $margin, max($array_lat) + $margin)];
    }

    /**
     * Return a simplified version of the linestring
     * @param int $precision Remove all points which perpendicular distance is less than $precision meters from the surrounding points
     */
    public function simplify ($precision = 100) {

        $polyline = new Polyline();
        foreach ($this->coordinates as $coord) $polyline->addPoint(new Coordinate($coord->lat, $coord->lng));

        $processor = new SimplifyDouglasPeucker($precision);

        $simplified_polyline = $processor->simplify($polyline);
        $simplified_coords = [];
        foreach ($simplified_polyline->getPoints() as $point) array_push($simplified_coords, [$point->getLng(), $point->getLat()]);

        return $simplified_coords;
    }

    /**
     * Return line distance in kilometers
     * @return int Distance in kilometers
     */
    public function computeDistance () {

        $polyline = new Polyline();
        foreach ($this->coordinates as $coord) $polyline->addPoint(new Coordinate($coord->lat, $coord->lng));
        
        return $polyline->getLength(new Vincenty()) / 1000;
    }

    /**
     * Get all scenery spot ids less than 
     * @param int $tolerance Tolerance in meters
     */
    public function getCloseSceneryIds ($tolerance = 300) {
        $d = $tolerance / 200000; // About 1000m
        $getCloseSceneries = $this->getPdo()->prepare("SELECT id FROM sceneries WHERE ST_Intersects(point, ST_Buffer(ST_LineStringFromText(?), ?))");
        $getCloseSceneries->execute([$this->toWKT(), $d]);
        return $getCloseSceneries->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get all scenery spots less than $tolerance meters from linestring
     * @param int $tolerance Tolerance in meters
     */
    public function getCloseSceneries ($tolerance = 300) {
        $scenery_ids = $this->getCloseSceneryIds($tolerance);
        var_dump($scenery_ids); die();
        return array_map(function ($id) {
            return new Scenery($id);
        }, $scenery_ids);
    }

    /**
     * Query a static map from mapbox server and store it to the static_map property
     * @return File
     */
    public function queryStaticMap () {
        
        // Api key
        $api_key = getenv('MAPBOX_API_KEY');

        // Claclucate line precision depending on distance
        $line_distance = $this->computeDistance();
        if ($line_distance < 30) $linestring_precision = 25;
        else if ($line_distance < 80) $linestring_precision = 60;
        else if ($line_distance < 120) $linestring_precision = 140;
        else if ($line_distance < 200) $linestring_precision = 250;
        else $linestring_precision = 320;

        // Build line geojson
        $stroke_color = '6f6fff';
        $stroke_width = 10;
        $geojson = $this->toGeojson([
            "stroke" => "#" .$stroke_color,
            "stroke-width" => $stroke_width
        ], $linestring_precision);
        $geojson_string = json_encode($geojson);
        $geojson_string_encoded = urlencode($geojson_string);

        // Build start and goal markers
        $start_icon = urlencode('https://img.icons8.com/flat-round/64/play.png');
        $start_lng = $this->coordinates[0]->lng;
        $start_lat = $this->coordinates[0]->lat;
        $goal_icon = urlencode('https://img.icons8.com/flat-round/64/stop.png');
        $goal_lng = $this->coordinates[$this->length - 1]->lng;
        $goal_lat = $this->coordinates[$this->length - 1]->lat;

        // Set map properties
        $width = 1280;
        $height = 1280;
        $bbox = $this->getBBox();
        $bbox_string = '[' .$bbox[0]->lng. ',' .$bbox[0]->lat. ',' .$bbox[1]->lng. ',' .$bbox[1]->lat. ']';
        $padding = 280;
        $pitch = 30;

        // Make request
        $uri = "https://api.mapbox.com/styles/v1/sisbos/cl07xga7c002616qcbxymnn5z/static/geojson({$geojson_string_encoded}),url-{$start_icon}({$start_lng},{$start_lat}),url-{$goal_icon}({$goal_lng},{$goal_lat})/{$bbox_string}/{$width}x{$height}?padding={$padding}&before_layer=road-label&access_token={$api_key}";
        $curl = curl_init($uri);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CAINFO => CFLinestring::$root_folder. "bin/cacert.pem"
        ]);
        $thumbnail = curl_exec($curl);
        if (!$thumbnail) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception($error);
        } else if (curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 200) {
            curl_close($curl);
            throw new Exception($thumbnail);
        }

        return $thumbnail;
    }

}