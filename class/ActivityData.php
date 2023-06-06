<?php

use phpGPX\Models\GpxFile;

class ActivityData {

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

    function __construct () {
        
    }

    /**
     * Get geolocation for start and goal place from linestring start and goal coordinates
     */
    private function getGeolocation () {
        $this->summary['startplace'] = $this->linestring->coordinates[0]->queryGeolocation();
        $this->summary['goalplace']  = $this->linestring->coordinates[$this->linestring->length - 1]->queryGeolocation();
    }

    /**
     * Populate instance from gpx parsed data
     * @param GpxFile $parsed_data Previously parsed gpx data
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
            'positive_elevation' => $session['total_ascent'],
            'negative_elevation' => $session['total_descent'],
            'altitude_min' => min($record['altitude']),
            'altitude_max' => max($record['altitude']),
            'speed_max' => $session['max_speed'],
            'start_time' => new DateTime(date('Y-m-d H:i:s', $session['start_time'])), new DateTimeZone('Asia/Tokyo'),
            'finish_time' => new DateTime(date('Y-m-d H:i:s', $record['timestamp'][count($record['timestamp']) - 1]), new DateTimeZone('Asia/Tokyo'))
        ];
        if (isset($record['temperature'][$i])) {
            $this->summary['temperature_min'] = min($record['temperature']);
            $this->summary['temperature_avg'] = avg($record['temperature']);
            $this->summary['temperature_max'] = max($record['temperature']);
        }

        // Build trackpoints
        $coordinates = [];
        $trackpoints = [];
        for ($i = 0; $i < count($record['position_long']) - 1; $i++) {

            // Build coordinates array
            array_push($coordinates, [$record['position_long'][$i], $record['position_lat'][$i]]);

            // Add basic properties
            $trackpoint_data = [
                'time' => $record['timestamp'][$i],
                'elevation' => $record['altitude'][$i],
                'distance' => $record['distance'][$i],
                'speed' => $record['speed'][$i]
            ];

            // Add other properties
            if (isset($record['temperature'][$i])) $trackpoint_data['temperature'] = $record['temperature'][$i];
            if (isset($record['speed'][$i])) $trackpoint_data['speed'] = $record['speed'][$i];
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
     * If instance holds parsed data, create an activity from it
     * @param int $user_id User to create activity for
     * @return boolean True if activity has been created, else false
     */
    public function createActivity ($user_id) {

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
        
        $checkpoint_start = [
            'number' => 0,
            'name' => 'Start',
            'type' => 'Start',
            'story' => '',
            'datetime' => new DateTime(date('Y-m-d H:i:s', $this->linestring->trackpoints[0]->time), new DateTimeZone('Asia/Tokyo')),
            'city' => $this->summary['startplace']->city,
            'prefecture' => $this->summary['startplace']->prefecture,
            'elevation' => $this->linestring->trackpoints[0]->elevation,
            'distance' => 0,
            'temperature' => $this->linestring->trackpoints[0]->temperature,
            'lng' => $this->linestring->coordinates[0]->lng,
            'lat' => $this->linestring->coordinates[0]->lat,
            'special' => 'start'
        ];
        
        $checkpoint_goal = [
            'number' => 1,
            'name' => 'Goal',
            'type' => 'Goal',
            'story' => '',
            'datetime' => new DateTime(date('Y-m-d H:i:s', $this->linestring->trackpoints[$this->linestring->length - 1]->time), new DateTimeZone('Asia/Tokyo')),
            'city' => $this->summary['goalplace']->city,
            'prefecture' => $this->summary['goalplace']->prefecture,
            'elevation' => $this->linestring->trackpoints[$this->linestring->length - 1]->elevation,
            'distance' => $this->linestring->trackpoints[$this->linestring->length - 1]->distance,
            'temperature' => $this->linestring->trackpoints[$this->linestring->length - 1]->temperature,
            'lng' => $this->linestring->coordinates[$this->linestring->length - 1]->lng,
            'lat' => $this->linestring->coordinates[$this->linestring->length - 1]->lat,
            'special' => 'goal'
        ];

        $activity_data = [
            'user_id' => $user->id,
            'datetime' => new DateTime(date('Y-m-d H:i:s', $this->linestring->trackpoints[0]->time), new DateTimeZone('Asia/Tokyo')),
            'title' => $this->summary['title'],
            'distance' => $this->summary['distance'],
            'duration' => $this->summary['duration'],
            'duration_running' => $this->summary['duration_running'],
            'bike_id' => $user->getBikes()[0]['id'],
            'privacy' => 'private',
            'elevation' => $this->summary['positive_elevation'],
            'speed_max' => $this->summary['speed_max'],
            'slope_max' => null,
            'altitude_max' => $this->summary['altitude_max'],
            'altitude_min' => $this->summary['altitude_min'],
            'temperature' => [
                'min' => $this->summary['temperature_min'],
                'avg' => $this->summary['temperature_avg'],
                'max' => $this->summary['temperature_max']
            ],
            'route_data' => $route_data,
            'checkpoints_data' => [$checkpoint_start, $checkpoint_goal]
        ];

        $activity = new Activity();
        $activity->create($activity_data);

        return true;
    }
}