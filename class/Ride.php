<?php

class Ride extends Model {
    
    protected $table = 'rides';
    
    function __construct($id = NULL, $lngLatFormat = true) {
        parent::__construct();
        $this->id = $id;
        $data = $this->getData($this->table);
        $this->name                                 = $data['name'];
        $this->date                                 = $data['date'];
        $this->meeting_time                         = $data['meeting_time'];
        $this->departure_time                       = $data['departure_time'];
        $this->finish_time                          = $data['finish_time'];
        $this->nb_riders_min                        = $data['nb_riders_min'];
        $this->nb_riders_max                        = $data['nb_riders_max'];
        $this->level_beginner                       = $data['level_beginner'];
        $this->level_intermediate                   = $data['level_intermediate'];
        $this->level_athlete                        = $data['level_athlete'];
        $this->citybike                             = $data['citybike'];
        $this->roadbike                             = $data['roadbike'];
        $this->mountainbike                         = $data['mountainbike'];
        $this->gravelcxbike                         = $data['gravelcxbike'];
        $this->description                          = $data['description'];
        $this->meeting_place                        = $data['meeting_place'];
        $this->distance_about                       = $data['distance_about'];
        $this->distance                             = $data['distance'];
        $this->finish_place                         = $data['finish_place'];
        $this->terrain                              = $data['terrain'];
        $this->course_description                   = $data['course_description'];
        $this->posting_date                         = $data['posting_date'];
        $this->author                               = new User ($data['author_id']);
        $this->privacy                              = $data['privacy'];
        $this->status                               = $data['status'];
        $this->substatus                            = $data['substatus'];
        $this->entry_start                          = $data['entry_start'];
        $this->entry_end                            = $data['entry_end'];
        $this->participants_number                  = $data['participants_number'];
        if (isset($data['route_id'])) $this->route  = new Route ($data['route_id'], $lngLatFormat);
        $this->checkpoints                          = $this->getCheckpoints();
    }

    public function getFeaturedImage () {
        $getFeaturedImage = $this->getPdo()->prepare('SELECT img, img_size, img_name, img_type FROM ride_checkpoints WHERE ride_id = ? AND featured = true');
        $getFeaturedImage->execute(array($this->id));
        return $getFeaturedImage->fetch(PDO::FETCH_ASSOC);
    }

    function getAcceptedLevels () {
        $getAcceptedLevels = $this->getPdo()->prepare('SELECT level_beginner, level_intermediate, level_athlete FROM rides WHERE id = ?');
        $getAcceptedLevels->execute(array($this->id));
        $accepted_levels = $getAcceptedLevels->fetch(PDO::FETCH_NUM);
        return $accepted_levels;
    }

    // Get accepted levels infos of a specific ride in values
    public function getAcceptedLevelsValues () {

        $getAcceptedLevels = $this->getPdo()->prepare('SELECT level_beginner, level_intermediate, level_athlete FROM rides WHERE id = ?');
        $getAcceptedLevels->execute(array($this->id));
        $accepted_levels = $getAcceptedLevels->fetch(PDO::FETCH_NUM);

        // Build accepted bikes values table
        $accepted_levels_values = [];
        forEach($accepted_levels as $number => $boolean) {
            if ($boolean) {
                array_push($accepted_levels_values, $number + 1);
            }
        }
        return $accepted_levels_values;
    }

    // Get accepted level list of a specific ride from the database
    public function getAcceptedLevelTags () {
        $level_list = $this->getAcceptedLevels();
        // Set variables to default value
        $string = '';
        // Build the list string
        foreach($level_list as $level => $boolean){
            // Filter string keys for preventing double iteration
            // if(strlen($level)>1){
                // If level is accepted, then write it
                if ($boolean == true) {
                    $string .= '<span class="tag-' .colorLevel(getLevelFromKey($level+1)). '">' .getLevelFromKey($level+1). '</span>';
                }
            // }
        }
        return $string;
    }

    // Get accepted level list of a specific ride from the database
    public function getAcceptedLevelString () {
        $level_list = $this->getAcceptedLevels();
        // If all levels are true, return Anyone
        if($level_list[0] && $level_list[1] && $level_list[2]){
            return 'Anyone';
        }else{
            // Set variables to default value
            $i = 0;	$string = '';
            // Build the list string
            foreach($level_list as $level => $boolean){
                // If level is accepted, then write it
                if($boolean == true){
                    // Insert commas between level
                    if($i > 0){
                        $string .= ', ';
                    }
                    $string .= getLevelFromKey($level + 1);
                    $i++;
                }
            }
        }
        return $string;
    }

    // Get accepted bikes infos of a specific ride from the rides table
    public function getAcceptedBikes () {
        $getAcceptedBikes = $this->getPdo()->prepare('SELECT citybike, roadbike, mountainbike, gravelcxbike FROM rides WHERE id = ?');
        $getAcceptedBikes->execute(array($this->id));
        $accepted_bikes = $getAcceptedBikes->fetch();
        return $accepted_bikes;
    }

    // Get accepted bikes infos of a specific ride in values
    public function getAcceptedBikesValues () {

        $getAcceptedBikes = $this->getPdo()->prepare('SELECT citybike, roadbike, mountainbike, gravelcxbike FROM rides WHERE id = ?');
        $getAcceptedBikes->execute(array($this->id));
        $accepted_bikes = $getAcceptedBikes->fetch(PDO::FETCH_NUM);

        // Build accepted bikes values table
        $accepted_bikes_values = [];
        forEach($accepted_bikes as $number => $boolean) {
            if ($boolean) {
                array_push($accepted_bikes_values, $number + 1);
            }
        }
        return $accepted_bikes_values;
    }

    // Get accepted bikes list of a specific ride from the database
    public function getAcceptedBikesString () {

        $accepted_bikes = $this->getAcceptedBikes();

        if ($accepted_bikes[0] && $accepted_bikes[1] && $accepted_bikes[2] && $accepted_bikes[3]) return 'All bikes accepted';
        else {
            // Set variables to default value
            $i = 0;	$string = '';
            // Build the list string
            foreach ($accepted_bikes as $bike => $boolean) {
                // Filter string keys for preventing double iteration
                if (strlen($bike) > 1) {
                    // If bike type is accepted, then write it
                    if ($boolean == true) {
                        // Insert commas between bike types
                        if ($i > 0) $string .= ', ';
                        $string .= getBikesFromColumnName($bike);
                        // add plural
                        $string .= 's';
                        $i++;
                    }
                }
            }
            return $string;
        }
    }

    // Check if user's bike fits with this ride accepted bikes
    public function isBikeAccepted ($user) {
        
        $accepted_bikes = $this->getAcceptedBikes();
        
        // Get user bikes info
        $bikes = $user->getBikes();
        
        // Iterates accepted bikes list of the ride
        foreach ($accepted_bikes as $biketype => $boolean) {
            // For each bike accepted,
            if ($boolean) {
                // Check if there is a bike type matching in user's bike list
                for ($i = 0; $i < count($bikes); $i++) {
                    $bike = new Bike ($bikes[$i]['id']);
                    if (getBikesFromColumnName($biketype) == $bike->type) {
                        // If there is one, return true
                        return true;
                    }
                }
            }
        }
        // If no match have been found, return false
        return false;
    }

    public function join ($participant) {
        // Add a line into participation database
        $joinRide = $this->getPdo()->prepare('INSERT INTO participation(user_id, ride_id, entry_date) VALUES (?, ?, ?)');
        $joinRide->execute(array($participant->id, $this->id, date('Y-m-d H:i:s')));	
    }

    public function quit ($participant) {
        // Remove an user from participation database
		$quitRide = $this->getPdo()->prepare('DELETE FROM participation WHERE user_id = ? AND ride_id = ?');
		$quitRide->execute(array($_SESSION['id'], $this->id));	
    }

    public function isOpen () {
        if (date('Y-m-d') < $this->entry_start) {
            return 'not yet';
        } else if (date('Y-m-d') > $this->entry_end) {
            return 'closed';
        } else if (date('Y-m-d') >= $this->entry_start AND date('Y-m-d') <= $this->entry_end) {
            return 'open';
        } else {
            return false;
        }
    }

    public function isParticipating ($user) {
        
        // Check if the user has already joined the ride
        $checkIfParticipate = $this->getPdo()->prepare('SELECT ride_id FROM participation WHERE user_id = ? AND ride_id = ?');
        $checkIfParticipate->execute(array($user->id, $this->id));
    
        if ($checkIfParticipate->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Function for getting an array with participants list and the total number of them
    public function getParticipants () {
        $getParticipants = $this->getPdo()->prepare('SELECT user_id FROM participation WHERE ride_id = ?');
        $getParticipants->execute(array($this->id));
        if ($getParticipants->rowCount() > 0) {
            // Regroup user ids in one array
            $participants = array_column($getParticipants->fetchAll(PDO::FETCH_ASSOC), 'user_id');
            return $participants;
        } else {
            return NULL;
        }
    }

    // Check if a ride is full or not
    public function isFull () {
        
        // Get current number of participants
        $participants = $this->getParticipants();
        if(!empty($participants)){
            $current_nb = count($participants);
        }else{
            $current_nb = 0;
        }
        
        // Get maximum number of participants
        
        if($current_nb >= $this->nb_riders_max){
            return true;
        }else if($current_nb < $this->nb_riders_max){
            return false;
        }
    }

    // Check if all participants to a ride are in friends list of an user
    public function isEveryParticipantInFriendsList ($user) {
        $friends = $user->getFriends();
        $participants = $this->getParticipants();
        if ($participants) {
            $participating_friends = array_intersect($friends, $participants);
            $participants_not_friends = array_diff($participants, $participating_friends);
            if (count($participants_not_friends) == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function defineStatus ($privacy){
        $substatus = NULL; // Set substatus to NULL for preventing errors in case of no substatus set
        
        // If ride date is passed
        if ($this->date < date('Y-m-d')) {
            $status = 'Finished'; } // status is Finished
        
        // If ride is full
        else if ($this->isFull()) {
            $status = 'Full'; } // status is Full
            
        // If privacy is set as private
        else if ($privacy == 'Private') {
            $status = 'Private'; } // status is Private
            
        // If not set as Finished, Full or Private
        else {
            
            // If not set as private, ride date is yet to come and entry start date is yet to come
            if (($privacy != 'Private') AND ($this->date > date('Y-m-d')) AND ($this->entry_start > date('Y-m-d'))){
                $status = 'Closed'; // status is Closed
                $substatus = 'opening soon'; // substatus is opening soon
            }
    
            // If not set as private, ride date is yet to come and entries are open
            else if (($privacy != 'Private') AND ($this->date > date('Y-m-d')) AND ($this->entry_start <= date('Y-m-d') AND $this->entry_end >= date('Y-m-d'))) {
                // If number of applicants is lower than minimum number set
                $participants_number = $this->setParticipationInfos()['participants_number'];
                if ($participants_number < $this->nb_riders_min) {
                    $status = 'Open'; // status is Open 
                    $substatus = 'riders wanted'; // substatus is riders wanted
                } else { // If minimum number is reached
                    $status = 'Open'; // status is Open
                    $substatus = 'ready to depart'; //substatus is ready to depart
                }
            }
    
            // If not set as private, ride date is yet to come but entries are closed
            else if (($privacy != 'Private') AND ($this->date >= date('Y-m-d')) AND ($this->entry_start < date('Y-m-d') AND $this->entry_end < date('Y-m-d'))) {
                $status = 'Closed'; // status is Closed
                $substatus = 'ready to depart'; //substatus is ready to depart
            }
    
            else {
                $status = 'no status';
            }
            
        }
    
        $updateStatus = $this->getPdo()->prepare('UPDATE rides SET status = ?, substatus = ? WHERE id = ?');
        $updateStatus->execute(array($status, $substatus, $this->id));

        $this->status    = $status;
        $this->substatus = $substatus;
        
        return array($status, $substatus);
    }

    public function setParticipationInfos () {
        $participation = $this->getParticipants(); 

        if (empty($participation)) {
            $participants_number = 0;
        } else {
            $participants_number = count($participation);
        }

        // If number of applicants is lower than the minimum number
        if ($participants_number < $this->nb_riders_min) {
            $participation_color = 'blue'; } // blue
            
        // If number of applicants is between the minimum and the maximum number
        else if (($participants_number >= $this->nb_riders_min) AND ($participants_number < $this->nb_riders_max)) {
            $participation_color = 'green'; } // green
        
        // If number of applicants equals the maximum number
        else if (($participants_number == $this->nb_riders_max)) {
            $participation_color = 'red'; } // red
        
        else {
            $participation_color = 'black';
            
        }

        return array('participants_number' => $participants_number, 'participation_color' => $participation_color);
    }

    public function exists () {
        if (!empty($this->name)) return true;
        else return false;
    }

    public function delete () {      
        $deleteCheckpoints = $this->getPdo()->prepare('DELETE FROM ride_checkpoints WHERE ride_id = ?');
        $deleteCheckpoints->execute(array($this->id));
        $deleteChat = $this->getPdo()->prepare('DELETE FROM ride_chat WHERE ride_id = ?');
        $deleteChat->execute(array($this->id));
        $deleteRide = $this->getPdo()->prepare('DELETE FROM rides WHERE id = ?');
        $deleteRide->execute(array($this->id));
        return true;
    }

    // Function for uploading ride gallery
    public function uploadGallery () {
        
        // Declaration of variables
        $return     = false;
        $img_blob   = '';
        $img_size   = 0;
        $max_size   = 500000;
        $img_name   = '';
        $img_type   = '';
                            
        // Count files and start the loop if there are from 1 to 5 files
        $countfiles = count($_FILES['file']['name']);
        if($countfiles > 5){
            $error = 'You can\'t upload more than 5 files. Please try again with 5 files or less.';
            return array(false, $error);
        }else if($countfiles <= 0 OR empty($_FILES['file']['name'][0])){
            return;
        }else if($countfiles <= 5 AND $countfiles > 0){

            // Delete all photos previously uploaded
            $resetImage = $this->getPdo()->prepare('DELETE FROM ride_photos WHERE ride_id = ?');
            $resetImage->execute(array($this->id));

            for($i = 0; $i < $countfiles; $i++){
                                
                // Displays an error message if any problem through upload
                $return = is_uploaded_file($_FILES['file']['tmp_name'][$i]);
                if (!$return) {
                    $error = 'Upload problem for ' . $_FILES['file']['name'][$i] . '. Please try again.';
                    return array(false, $error);
                }else{
                    
                // Sort upload data into variables
                $img_id   = $i;
                $img_name = $_FILES['file']['name'][$i];
                $img_type = $_FILES['file']['type'][$i];
                $img_blob = file_get_contents($_FILES['file']['tmp_name'][$i]);
                        
                    // Displays an error message if file size exceeds $max_size
                    $img_size = $_FILES['file']['size'][$i];
                    if ($img_size > $max_size) {
                        $error = $img_name . ' exceeds size limit (500kb). Please reduce the size and try again.';
                        return array(false, $error);
                                
                    }else{		
                        $insertImage = $this->getPdo()->prepare('INSERT INTO ride_photos (ride_id, img_id, img, size, name, type) VALUES (?, ?, ?, ?, ?, ?)');
                        $insertImage->execute(array($this->id, $img_id, $img_blob, $img_size, $img_name, $img_type));
                        $checksuccess = true;
                    }
                }
            }
            
            if($checksuccess == true){
                $success = $countfiles . ' pictures have correctly been uploaded !';
                return array(true, $success);
            }
        }
    }

    function deleteRideGallery(){
        $CheckIfGallerySet = $this->getPdo()->prepare('SELECT * FROM ride_photos WHERE ride_id = ?');
        $CheckIfGallerySet->execute(array($this->id));
        if($CheckIfGallerySet->rowCount() > 0){	
            $deleteGallery = $this->getPdo()->prepare('DELETE FROM ride_photos WHERE ride_id = ?');
            $deleteGallery->execute(array($this->id));
            $success = 'Current gallery has been successfully deleted.';
            return array(true, $success);
        }else{
            $error = 'You don\'t have set any gallery yet.';
            return array(false, $error);
        }
    }

    // Get all checkpoints info of a specific ride
    public function getCheckpoints () {
        $getCheckpoints = $this->getPdo()->prepare('SELECT id FROM ride_checkpoints WHERE ride_id = ? ORDER BY checkpoint_id');
        $getCheckpoints->execute(array($this->id));
        $checkpoints_ids = $getCheckpoints->fetchAll(PDO::FETCH_ASSOC);
        $checkpoints = array();
        forEach ($checkpoints_ids as $checkpoint) {
            $checkpoint = new RideCheckpoint($checkpoint['id']);
            array_push($checkpoints, $checkpoint);
        }
        return $checkpoints;
    }

    // Check if Start and Finish are the same place
    public function isSameSF () {
        if ($this->checkpoints[0]->lngLat->lng === $this->checkpoints[count($this->checkpoints)-1]->lngLat->lng) {
            return true;
        } else {
            return false;
        } 
    }

    public function getChat () {
        $getChat = $this->getPdo()->prepare('SELECT * FROM ride_chat WHERE ride_id = ?');
        $getChat->execute(array($this->id));
        return $getChat->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMapThumbnail () {
        $getMapThumbnail = $this->getPdo()->prepare('SELECT thumbnail FROM routes WHERE id = ?');
        $getMapThumbnail->execute(array($this->route->id));
        return $getMapThumbnail->fetch(PDO::FETCH_NUM)[0];
    }

}