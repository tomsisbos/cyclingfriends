<?php

use phpGPX\Models\GpxFile;

class ActivityData extends Model {

    /**
     * Activity summary data
     * @var array
     */
    public $summary;

    /**
     * Activity linestring
     * @var CFLinestringWithTrackpoints
     */
    public $linestring;

    function __construct ($summary = null, $coordinates = null, $trackpoints = null) {
        if ($summary && $coordinates && $trackpoints) {
            $this->summary = $summary;
            $this->linestring = new CFLinestringWithTrackpoints($coordinates, $trackpoints);
        }
    }

    /**
     * Get geolocation for start and goal place from linestring start and goal coordinates
     */
    private function getGeolocation () {
        $this->summary['startplace']     = $this->linestring->coordinates[0]->queryGeolocation();
        $this->summary['goalplace']      = $this->linestring->coordinates[$this->linestring->length - 1]->queryGeolocation();
    }

    /**
     * Populate instance from gpx parsed data
     * @param GpxFile $parsed_data Previously parsed gpx data
     * @throws Exception If parsing failed, throws exception
     */
    public function buildFromGpx ($parsed_data) {

        $track = $parsed_data->tracks[0];

        // Build trackpoints
        $coordinates       = [];
        $trackpoints       = [];
        $speed_array       = [];
        $temperature_array = [];
        $duration_running = 0;
        foreach ($track->segments[0]->points as $point) {

            if (!isset($point->time)) throw new Exception('このファイルには時間データが付随されていません。');

            // Build coordinates array
            array_push($coordinates, [$point->longitude, $point->latitude]);

            // Add basic properties
            $trackpoint_data = [
                'time' => $point->time->setTimezone(new DateTimeZone('Asia/Tokyo'))->getTimestamp(),
                'elevation' => $point->elevation,
                'distance' => $point->distance / 1000
            ];

            // Add extensions
            if (isset($point->extensions->trackPointExtension)) {
                $extension = $point->extensions->trackPointExtension;
                if (isset($extension->avgTemperature)) {
                    $trackpoint_data['temperature'] = $extension->avgTemperature;
                    array_push($temperature_array, $trackpoint_data['temperature']);
                }
                if (isset($extension->heartRate)) $trackpoint_data['heart_rate'] = $extension->heartRate;
                if (isset($extension->cadence)) $trackpoint_data['cadence'] = $extension->cadence;
                if (isset($extension->speed)) {
                    $trackpoint_data['speed'] = $extension->speed;
                    array_push($speed_array, $trackpoint_data['speed'] / 1000);
                }
            }
            if (isset($point->extensions->unsupported)) {
                $extension = $point->extensions->unsupported;
                if (isset($extension->power)) $trackpoint_data['power'] = intval($extension->power);
            }

            // Calculate duration running
            if (isset($previous_point)) {
                $section_distance = $point->distance - $previous_point->distance;
                $section_seconds = $point->time->diff($previous_point->time)->s;
                if ($section_distance < 300 && $section_seconds > 0 && !(isset($extension->speed))) array_push($speed_array, ($section_distance / 1000) / ($section_seconds / 60 / 60)); // ... And calculate section speed if data does dot exist (Cut longer distance to prevent bugs from tunnels or signal lost)
                if ($section_seconds > 0 && $section_distance / $section_seconds > 0.001) $duration_running += $section_seconds;
            }
            $previous_point = $point;

            array_push($trackpoints, new Trackpoint($trackpoint_data));
        }

        // Build summary
        $this->summary = [
            'title' => $track->name,
            'distance' => $track->stats->distance / 1000,
            'duration' => $track->stats->startedAt->diff($track->stats->finishedAt),
            'duration_running' => timestampToDateInterval($duration_running),
            'positive_elevation' => $track->stats->cumulativeElevationGain,
            'negative_elevation' => $track->stats->cumulativeElevationLoss,
            'altitude_min' => $track->stats->minAltitude,
            'altitude_max' => $track->stats->maxAltitude,
            'start_time' => $track->stats->startedAt->setTimezone(new DateTimeZone('Asia/Tokyo')),
            'finish_time' => $track->stats->finishedAt->setTimezone(new DateTimeZone('Asia/Tokyo'))
        ];
        if (count($speed_array) > 0) $this->summary['speed_max'] = max($speed_array);
        if (count($temperature_array) > 0) {
            $this->summary['temperature_min'] = min($temperature_array);
            $this->summary['temperature_avg'] = avg($temperature_array);
            $this->summary['temperature_max'] = max($temperature_array);
        }

        // Build linestring
        $this->linestring = new CFLinestringWithTrackpoints($coordinates, $trackpoints);

        // Query geolocation for startplace and goalplace
        $this->getGeolocation();
    }

    

    /**
     * Populate instance from fit parsed data
     * @param FitData $parsed_data Previously parsed fit data
     * @throws Exception
     */
    public function buildFromFit ($parsed_data) {

        $record = $parsed_data->record;
        $session = $parsed_data->session;

        // Build summary
        $duration_running = timestampToDateInterval(round($session['total_timer_time']));
        $this->summary = [
            'title' => $duration_running->h. '時間' .$duration_running->i. '分のライド',
            'distance' => $session['total_distance'],
            'duration' => timestampToDateInterval(round($session['total_elapsed_time'])),
            'duration_running' => $duration_running,
            'start_time' => new DateTime(date('Y-m-d H:i:s', $session['start_time'])), new DateTimeZone('Asia/Tokyo'),
            'finish_time' => new DateTime(date('Y-m-d H:i:s', $record['timestamp'][count($record['timestamp']) - 1]), new DateTimeZone('Asia/Tokyo'))
        ];
        if (isset($session['max_speed'])) $this->summary['max_speed'] =  $session['max_speed'];
        if (isset($session['total_ascent'])) $this->summary['positive_elevation'] = $session['total_ascent'];
        else $this->summary['positive_elevation'] = 0;
        if (isset($session['total_descent'])) $this->summary['negative_elevation'] = $session['total_descent'];
        else $this->summary['negative_elevation'] = 0;
        if (isset($record['altitude'][0]) && $record['altitude'][0] != null) $this->summary['altitude_max'] = max($record['altitude']);
        else $this->summary['altitude_max'] = 0;
        if (isset($record['temperature'][0])) {
            $this->summary['temperature_min'] = min($record['temperature']);
            $this->summary['temperature_avg'] = avg($record['temperature']);
            $this->summary['temperature_max'] = max($record['temperature']);
        } else {
            $this->summary['temperature_min'] = null;
            $this->summary['temperature_avg'] = null;
            $this->summary['temperature_max'] = null;
        }

        // Build trackpoints
        if (!isset($record['position_long']) || $record['position_long'] == null) throw new Exception('missing_coordinates');
        $coordinates = [];
        $trackpoints = [];
        for ($i = 0; $i < count($record['position_long']) - 1; $i++) {

            // Build coordinates array
            array_push($coordinates, [$record['position_long'][$i], $record['position_lat'][$i]]);

            // Add basic properties
            $trackpoint_data = [
                'time' => $record['timestamp'][$i],
                'distance' => $record['distance'][$i],
            ];

            // Add other properties
            if (isset($record['temperature'][$i])) $trackpoint_data['temperature'] = $record['temperature'][$i];
            if (isset($record['speed'][$i])) $trackpoint_data['speed'] = $record['speed'][$i];
            if (isset($record['altitude'][$i])) $trackpoint_data['elevation'] = $record['altitude'][$i];
            if (isset($record['heart_rate'][$i])) $trackpoint_data['heart_rate'] = $record['heart_rate'][$i];
            if (isset($record['cadence'][$i])) $trackpoint_data['cadence'] = $record['cadence'][$i];

            array_push($trackpoints, new Trackpoint($trackpoint_data));
        }

        // Build linestring
        $this->linestring = new CFLinestringWithTrackpoints($coordinates, $trackpoints);

        // Query geolocation for startplace and goalplace
        $this->getGeolocation();
    }

    /**
     * Check if a similar entry exists for a specific user
     * @param int $user_id
     * @return boolean
     */
    public function alreadyExists ($user_id) {
        $checkIfExists = $this->getPdo()->prepare("SELECT id FROM activities WHERE user_id = ? AND datetime = ?");
        $checkIfExists->execute([$user_id, (new DateTime('@' .$this->linestring->trackpoints[0]->time, new DateTimeZone('Asia/Tokyo')))->setTimezone(new DateTimeZone('Asia/Tokyo'))->format('Y-m-d H:i:s')]);
        if ($checkIfExists->rowCount() > 0) return $checkIfExists->fetch(PDO::FETCH_COLUMN);
        else return false;
    }   

    /**
     * If instance holds parsed data, create an activity from it
     * @param int $user_id User to create activity for
     * @param array $editable_data Editable data to append to activity
     * @return int Id of created activity
     */
    public function createActivity ($user_id, $editable_data = []) {

        $user = new User($user_id);

        $route_data = [
            'author_id'   => $user->id,
            'route_id'    => 'new',
            'category'    => 'activity',
            'name'        => $this->summary['title'],
            'description' => '',
            'distance'    => $this->summary['distance'],
            'elevation'   => $this->summary['positive_elevation'],
            'startplace'  => $this->summary['startplace'],
            'goalplace'   => $this->summary['goalplace'],
            'tunnels'     => [],
            'linestring'  => $this->linestring
        ];
        
        // If checkpoint data is appended, use it
        if (isset($editable_data['checkpoints'])) {
            $checkpoints_data = array_map(function ($checkpoint) {
                if ($checkpoint['type'] == 'Start') $checkpoint['special'] = 'start';
                if ($checkpoint['type'] == 'Goal') $checkpoint['special'] = 'goal';
                $checkpoint['lng'] = $checkpoint['lngLat']['lng'];
                $checkpoint['lat'] = $checkpoint['lngLat']['lat'];
                if (isset($checkpoint['geolocation'])) {
                    $checkpoint['city'] = $checkpoint['geolocation']['city'];
                    $checkpoint['prefecture'] = $checkpoint['geolocation']['prefecture'];
                } else {
                    $checkpoint['city'] = null;
                    $checkpoint['prefecture'] = null;
                }
                $checkpoint['datetime'] = (new DateTime('@' .$checkpoint['datetime']))->setTimezone(new DateTimeZone('Asia/Tokyo')); // Change timestamp to datetime instance
                return $checkpoint;
            }, $editable_data['checkpoints']);
        }
        // Else, build start and goal checkpoints from scratch
        else {
            $checkpoints_data = [
                [
                    'number' => 0,
                    'name' => 'Start',
                    'type' => 'Start',
                    'story' => '',
                    'datetime' => (new DateTime('@' .$this->linestring->trackpoints[0]->time))->setTimezone(new DateTimeZone('Asia/Tokyo')),
                    'city' => $this->summary['startplace']->city,
                    'prefecture' => $this->summary['startplace']->prefecture,
                    'elevation' => $this->linestring->trackpoints[0]->elevation,
                    'distance' => 0,
                    'temperature' => $this->linestring->trackpoints[0]->temperature,
                    'lng' => $this->linestring->coordinates[0]->lng,
                    'lat' => $this->linestring->coordinates[0]->lat,
                    'special' => 'start'
                ],
                [
                    'number' => 1,
                    'name' => 'Goal',
                    'type' => 'Goal',
                    'story' => '',
                    'datetime' => (new DateTime('@' .$this->linestring->trackpoints[$this->linestring->length - 1]->time))->setTimezone(new DateTimeZone('Asia/Tokyo')),
                    'city' => $this->summary['goalplace']->city,
                    'prefecture' => $this->summary['goalplace']->prefecture,
                    'elevation' => $this->linestring->trackpoints[$this->linestring->length - 1]->elevation,
                    'distance' => $this->linestring->trackpoints[$this->linestring->length - 1]->distance,
                    'temperature' => $this->linestring->trackpoints[$this->linestring->length - 1]->temperature,
                    'lng' => $this->linestring->coordinates[$this->linestring->length - 1]->lng,
                    'lat' => $this->linestring->coordinates[$this->linestring->length - 1]->lat,
                    'special' => 'goal'
                ]
            ];
        }

        $activity_data = [
            'user_id' => $user->id,
            'datetime' => (new DateTime('@' .$this->linestring->trackpoints[0]->time, new DateTimeZone('Asia/Tokyo')))->setTimezone(new DateTimeZone('Asia/Tokyo')),
            'title' => $this->summary['title'],
            'distance' => $this->summary['distance'],
            'duration' => $this->summary['duration'],
            'duration_running' => $this->summary['duration_running'],
            'privacy' => 'public',
            'elevation' => $this->summary['positive_elevation'],
            'slope_max' => null,
            'route_data' => $route_data,
            'checkpoints_data' => $checkpoints_data
        ];

        if ($user->getSettings()->hide_garmin_activities) $activity_data['privacy'] = 'private';

        // Possibly missing data
        if (isset($this->summary['altitude_max'])) $activity_data['altitude_max'] = $this->summary['altitude_max'];
        else $activity_data['altitude_max'] = null;
        if (isset($this->summary['altitude_min'])) $activity_data['altitude_min'] = $this->summary['altitude_min'];
        else $activity_data['altitude_min'] = null;
        if (isset($this->summary['speed_max'])) $activity_data['speed_max'] = $this->summary['speed_max'];
        else $activity_data['speed_max'] = null;
        if (isset($this->summary['temperature_min'])) $activity_data['temperature']['min'] = $this->summary['temperature_min'];
        else $activity_data['temperature']['min'] = null;
        if (isset($this->summary['temperature_avg'])) $activity_data['temperature']['avg'] = $this->summary['temperature_avg'];
        else $activity_data['temperature']['avg'] = null;
        if (isset($this->summary['temperature_max'])) $activity_data['temperature']['max'] = $this->summary['temperature_max'];
        else $activity_data['temperature']['max'] = null;
        if (count($user->getBikes()) > 0) $activity_data['bike_id'] = $user->getBikes()[0];
        else $activity_data['bike_id'] = null;

        // Editable data
        if (isset($editable_data['title'])) $activity_data['title'] = $editable_data['title'];
        if (isset($editable_data['privacy'])) $activity_data['privacy'] = $editable_data['privacy'];
        if (isset($editable_data['bike_id']) && !empty($editable_data['bike_id'])) $activity_data['bike_id'] = $editable_data['bike_id'];

        if (isset($this->file_id)) $activity_data['file_id'] = $this->file_id;

        $activity = new Activity();
        $activity_id = $activity->create($activity_data);

        return $activity_id;
    }
}