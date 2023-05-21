<?php

class Activity extends Model {
    
    protected $table = 'activities';
    public $id;
    public $user_id;
    public $datetime;
    public $title;
    public $duration;
    public $duration_running;
    public $temperature_min;
    public $temperature_avg;
    public $temperature_max;
    public $speed_max;
    public $altitude_max;
    public $slope_max;
    public $bike;
    public $privacy;
    public $notes;
    public $notes_privacy;
    public $route;
    
    function __construct($id = NULL, $lngLatFormat = true) {
        parent::__construct();
        $this->id = intval($id);
        $data = $this->getData($this->table);
        $this->user_id          = $data['user_id'];
        $this->datetime         = new Datetime($data['datetime']);
        $this->title            = $data['title'];
        $this->duration         = new Datetime($data['duration']);
        $this->duration_running = new Datetime($data['duration_running']);
        $this->temperature_min  = floatval($data['temperature_min']);
        $this->temperature_avg  = floatval($data['temperature_avg']);
        $this->temperature_max  = floatval($data['temperature_max']);
        $this->speed_max        = floatval($data['speed_max']);
        $this->altitude_max     = intval($data['altitude_max']);
        $this->slope_max        = floatval($data['slope_max']);
        $this->bike             = $data['bike_id'];
        $this->privacy          = $data['privacy'];
        $this->notes            = $data['notes'];
        $this->notes_privacy    = $data['notes_privacy'];
        $this->route            = new Route($data['route_id'], $lngLatFormat);
    }

    public function getAuthor () {
        return new User($this->user_id);
    }

    public function getCheckpoints () {
        $getCheckpoints = $this->getPdo()->prepare('SELECT id FROM activity_checkpoints WHERE activity_id = ? ORDER BY number');
        $getCheckpoints->execute(array($this->id));
        $checkpoints_ids = $getCheckpoints->fetchAll(PDO::FETCH_ASSOC);
        $checkpoints = array();
        foreach ($checkpoints_ids as $checkpoint) {
            $checkpoint = new ActivityCheckpoint($checkpoint['id']);
            array_push($checkpoints, $checkpoint);
        }
        return $checkpoints;
    }

    public function getCheckpointPhotos ($current_checkpoint_number) {
        // Get all activity photos id with datetime
        $getPhotos = $this->getPdo()->prepare('SELECT id, datetime FROM activity_photos WHERE activity_id = ? ORDER BY datetime');
        $getPhotos->execute(array($this->id));
        $photo_ids = $getPhotos->fetchAll(PDO::FETCH_ASSOC);

        // Get closest checkpoint for each activity photo
        for ($i = 0; $i < count($photo_ids); $i++) {
            $closest_checkpoint_number   = 0;
            $closest_checkpoint_datetime = new DateTime(0, new DateTimeZone('Asia/Tokyo'));
            $photo_datetime = new DateTime($photo_ids[$i]['datetime']);
            foreach ($this->getCheckpoints() as $checkpoint) {
                if ($checkpoint->datetime > $closest_checkpoint_datetime AND $checkpoint->datetime < $photo_datetime) {
                    if ($checkpoint->number + 1 > count($this->getCheckpoints()) - 1) $closest_checkpoint_number = $checkpoint->number;
                    else $closest_checkpoint_number = $checkpoint->number + 1;
                    $closest_checkpoint_datetime = $checkpoint->datetime;
                }
            }
            $photo_ids[$i]['checkpoint_number'] = intval($closest_checkpoint_number);
        }

        // If checkpoint correspond to the one passed in argument, add it to the list to return
        $photos_to_append = [];
        foreach ($photo_ids as $photo_id) {
            if ($photo_id['checkpoint_number'] == $current_checkpoint_number) {
                array_push($photos_to_append, new ActivityPhoto($photo_id['id']));
            }
        }

        return $photos_to_append;
    }

    public function getFirstStory() {
        $getFirstStory = $this->getPdo()->prepare('SELECT story FROM activity_checkpoints WHERE activity_id = ? AND story IS NOT NULL ORDER BY number DESC');
        $getFirstStory->execute(array($this->id));
        return $getFirstStory->fetch(PDO::FETCH_NUM)[0];
    }

    public function getPhotoIds () {
        $getPhotos = $this->getPdo()->prepare('SELECT id FROM activity_photos WHERE activity_id = ? ORDER BY featured DESC, datetime ASC');
        $getPhotos->execute(array($this->id));
        return $getPhotos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPhotos () {
        $photo_ids = $this->getPhotoIds();
        $photos = array();
        foreach ($photo_ids as $photo) {
            $photo = new ActivityPhoto($photo['id']);
            array_push($photos, $photo);
        }
        return $photos;
    }

    public function getPreviewPhotos ($quantity) {
        $photo_ids = $this->getPhotoIds();
        $photos = array();
        for ($i = 0; $i < count($photo_ids) AND $i < $quantity; $i++) {
            $photo = new ActivityPhoto($photo_ids[$i]['id']);
            array_push($photos, $photo);
        }
        return $photos;
    }

    public function getAverageSpeed () {
        $hours = intval($this->duration_running->format('H'));
        $minutes = intval($this->duration_running->format('i')) / 60;
        $duration_running_value = $hours + $minutes; 
        return round($this->route->distance / $duration_running_value, 1);
    }

    public function getBreakTime() {
        return $this->duration->diff($this->duration_running);
    }

    public function getPlace () {
        $checkpoints = $this->getCheckpoints();
        $start = $checkpoints[0]->geolocation;
        $goal = $checkpoints[count($checkpoints) - 1]->geolocation;
        return ['start' => $start, 'goal' => $goal];
    }

    public function setBackgroundColor ($property, $isColor = false) {
        $string = 'bg-';
        switch ($property) {
            case 'distance':
                if ($isColor) $string .= 'green';
                else $string .= 'grey';
                if ($this->route->distance < 40) $string .= '-3';
                else if (!$this->route->distance > 100) $string .= '-2';
                break;
            case 'duration':
                if ($isColor) $string .= 'yellow';
                else $string .= 'grey';
                if (intval($this->duration->format('H') < 2)) $string .= '-3';
                else if (!intval($this->duration->format('H') > 5)) $string .= '-2';
                break;
            case 'duration_running':
                if ($isColor) $string .= 'yellow';
                else $string .= 'grey';
                if (intval($this->duration->format('H') < 2)) $string .= '-3';
                else if (!intval($this->duration->format('H') > 5)) $string .= '-2';
                break;
            case 'break_time':
                if ($isColor) $string .= 'yellow';
                else $string .= 'grey';
                if (intval($this->getBreakTime()->format('H') > 1)) $string .= '-3';
                break;
            case 'elevation': 
                if ($isColor) $string .= 'blue';
                else $string .= 'grey';
                if ($this->route->elevation < 500) $string .= '-3';
                else if (!$this->route->elevation > 2000) $string .= '-2';
                break;
            case 'speed_avg':
                if ($isColor) $string .= 'pink';
                else $string .= 'grey';
                if ($this->getAverageSpeed() < 21) $string .= '-3';
                else if (!$this->getAverageSpeed() > 28) $string .= '-2';
                break;
            case 'temperature_avg':
                if ($isColor) $string .= 'grey';
                else $string .= 'grey';
                if ($this->temperature_avg < 10) $string .= '-3';
                else if (!$this->temperature_avg > 22) $string .= '-2';
                break;
            case 'temperature_max':
                if ($isColor) $string .= 'grey';
                else $string .= 'grey';
                if ($this->temperature_max < 16) $string .= '-3';
                else if (!$this->temperature_max > 38) $string .= '-2';
                break;
            case 'altitude_max':
                if ($isColor) $string .= 'green';
                else $string .= 'grey';
                if ($this->altitude_max < 400) $string .= '-3';
                else if (!$this->altitude_max > 1200) $string .= '-2';
                break;
            case 'slope_max':
                if ($isColor) $string .= 'blue';
                else $string .= 'grey';
                if ($this->slope_max < 9) $string .= '-3';
                else if (!$this->slope_max > 16) $string .= '-2';
                break;
            case 'speed_max':
                if ($isColor) $string .= 'pink';
                else $string .= 'grey';
                if ($this->speed_max < 36) $string .= '-3';
                else if (!$this->speed_max > 58) $string .= '-2';
                break;
        }
        return $string;
    }

    public function getEndDateTime() {
        $end_timestamp = $this->datetime->getTimeStamp() + $this->duration->getTimeStamp();
        return new DateTime($end_timestamp);
    }

    public function exists () {
        if (!empty($this->title)) return true;
        else return false;
    }

    public function getFeaturedImage () {
        $photos = $this->getPhotos();
        foreach ($photos as $photo) {
            if ($photo->featured) return $photo;
        }
        // If no featured photo, return the last one
        if ($photos) return $photos[count($photos) - 1];
        // If no photo, return false
        else return false;
    }

    public function hasAccess ($user = false) {
        $author = $this->getAuthor();
        if ($this->privacy == 'friends_only') {
            if ($user && $user->isFriend($author) || $user->id == $author->id) return true;
            else return false;
        } else if ($this->privacy == 'private') {
            if ($user && $author->id == $user->id) return true;
            else return false;
        } else return true;
    }

    public function delete () {
        $this->route->delete();
        $deleteCheckpoints = $this->getPdo()->prepare('DELETE FROM activity_checkpoints WHERE activity_id = ?');
        $deleteCheckpoints->execute(array($this->id));
        $deletePhotos = $this->getPdo()->prepare('DELETE FROM activity_photos WHERE activity_id = ?');
        $deletePhotos->execute(array($this->id));
        $deleteLikeData = $this->getPdo()->prepare('DELETE FROM activity_islike WHERE activity_id = ?');
        $deleteLikeData->execute(array($this->id));
        $deleteActivity = $this->getPdo()->prepare('DELETE FROM activities WHERE id = ?');
        $deleteActivity->execute(array($this->id));
        return true;
    }
}