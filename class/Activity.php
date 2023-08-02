<?php

class Activity extends Model {
    
    protected $table = 'activities';
    protected $subtable = 'activity';
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
    public $altitude_min;
    public $slope_max;
    public $bike;
    public $privacy;
    public $notes;
    public $notes_privacy;
    public $route;
    
    function __construct($id = null, $lngLatFormat = true) {
        parent::__construct();
        if ($id !== null) {
            $this->id = intval($id);
            $data = $this->getData($this->table);
            $this->user_id          = $data['user_id'];
            $this->datetime         = new DateTime($data['datetime']);
            $this->title            = $data['title'];
            $this->duration         = new DateInterval('PT' .explode(':', $data['duration'])[0]. 'H' .explode(':', $data['duration'])[1]. 'M' .explode(':', $data['duration'])[2]. 'S');
            $this->duration_running = new DateInterval('PT' .explode(':', $data['duration_running'])[0]. 'H' .explode(':', $data['duration_running'])[1]. 'M' .explode(':', $data['duration_running'])[2]. 'S');
            $this->temperature_min  = floatval($data['temperature_min']);
            $this->temperature_avg  = floatval($data['temperature_avg']);
            $this->temperature_max  = floatval($data['temperature_max']);
            $this->speed_max        = floatval($data['speed_max']);
            $this->altitude_max     = intval($data['altitude_max']);
            $this->altitude_min     = intval($data['altitude_min']);
            $this->slope_max        = floatval($data['slope_max']);
            $this->bike             = $data['bike_id'];
            $this->privacy          = $data['privacy'];
            $this->notes            = $data['notes'];
            $this->notes_privacy    = $data['notes_privacy'];
            $this->route            = new Route($data['route_id'], $lngLatFormat);
        }
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
            $closest_checkpoint_datetime = new DateTime;
            $closest_checkpoint_datetime->setTimestamp(0);
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
        $hours = intval($this->duration_running->h);
        $minutes = intval($this->duration_running->i) / 60;
        $duration_running_value = $hours + $minutes; 
        return round($this->route->distance / $duration_running_value, 1);
    }

    public function getBreakTime() {
        $duration = new DateTime($this->duration->format('%H:%i:%s'));
        $duration_running = new DateTime($this->duration_running->format('%H:%i:%s'));
        return $duration_running->diff($duration);
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
                if (intval($this->duration->h < 2)) $string .= '-3';
                else if (!intval($this->duration->h > 5)) $string .= '-2';
                break;
            case 'duration_running':
                if ($isColor) $string .= 'yellow';
                else $string .= 'grey';
                if (intval($this->duration->h < 2)) $string .= '-3';
                else if (!intval($this->duration->h > 5)) $string .= '-2';
                break;
            case 'break_time':
                if ($isColor) $string .= 'yellow';
                else $string .= 'grey';
                if (intval($this->getBreakTime()->h > 1)) $string .= '-3';
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
            case 'altitude_min':
                if ($isColor) $string .= 'blue';
                else $string .= 'grey';
                if ($this->altitude_min < 20) $string .= '-3';
                else if (!$this->altitude_min > 400) $string .= '-2';
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
        $end_timestamp = $this->datetime->getTimeStamp() + strtotime($this->duration->format('%H:%i:%s'));
        return (new DateTime())->setTimestamp($end_timestamp);
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

    /** 
     * Retrieve all comments of this instance
     * @return ActivityComment[]
     */
    public function getComments () {
        $getComments = $this->getPdo()->prepare("SELECT id FROM {$this->subtable}_comments WHERE entry_id = ?");
        $getComments->execute([$this->id]);
        $comment_ids = $getComments->fetchAll(PDO::FETCH_COLUMN);
        $comments = [];
        foreach ($comment_ids as $id) array_push($comments, new ActivityComment($id));
        return $comments;
    }

    /**
     * Post a new comment from $user_id
     * @param int $user_id
     * @param string $content
     */
    public function postComment ($user_id, $content) {
        $user = new User($user_id);
        $time = (new Datetime('now', new DateTimeZone('Asia/Tokyo')))->format('Y-m-d H:i:s');
        $postComment = $this->getPdo()->prepare("INSERT INTO {$this->subtable}_comments (entry_id, user_id, content, time) VALUES (?, ?, ?, ?)");
        $postComment->execute(array($this->id, $user->id, $content, $time));
    }

    /**
     * Create a new activity
     * @param ActivityData An activity data instance
     * @return int Id of created activity
     */
    public function create ($activity_data) {
        
        // Get activity id
        $activity_id = getNextAutoIncrement('activities');

        // Insert data in 'routes' table
        $route_data = $activity_data['route_data'];
        $route_id = $route_data['linestring']->createRoute($route_data['author_id'], $route_data['route_id'], $route_data['category'], $route_data['name'], $route_data['description'], $route_data['distance'], $route_data['elevation'], $route_data['startplace'], $route_data['goalplace'], $route_data['tunnels']);
        
        // Insert data in 'activities' table
        $insert_activity = $this->getPdo()->prepare('INSERT INTO activities(user_id, datetime, posting_date, title, duration, duration_running, temperature_min, temperature_avg, temperature_max, speed_max, altitude_max, altitude_min, slope_max, bike_id, privacy, route_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insert_activity->execute(array($activity_data['user_id'], $activity_data['datetime']->format('Y-m-d H:i:s'), (new Datetime('now', new DateTimeZone('Asia/Tokyo')))->format('Y-m-d H:i:s'), $activity_data['title'], $activity_data['duration']->format('%H:%i:%s'), $activity_data['duration_running']->format('%H:%i:%s'), $activity_data['temperature']['min'],  $activity_data['temperature']['avg'], $activity_data['temperature']['max'], $activity_data['speed_max'], $activity_data['altitude_max'], $activity_data['altitude_min'], $activity_data['slope_max'], $activity_data['bike_id'], $activity_data['privacy'], $route_id));

        // If a corresponding file exists, add activity id to this file entry
        if (isset($activity_data['file_id'])) {
            $update_file = $this->getPdo()->prepare("UPDATE activity_files SET activity_id = ? WHERE id = ?");
            $update_file->execute([$activity_id, $activity_data['file_id']]);
        }

        // Insert data in 'checkpoints' table
        foreach ($activity_data['checkpoints_data'] as $checkpoint_data) {
            $checkpoint = new ActivityCheckpoint();
            $checkpoint_data['activity_id'] = $activity_id;
            $checkpoint->create($checkpoint_data);
        }

        return $activity_id;
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
        $deleteNotification = $this->getPdo()->prepare('DELETE FROM notifications WHERE entry_table = activities AND entry_id = ?');
        $deleteNotification->execute(array($this->id));
        return true;
    }
}