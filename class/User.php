<?php

use Location\Coordinate;
use Location\Distance\Vincenty;

class User extends Model {

    public $id;
    public $login;
    public $email;
    public $default_profilepicture_id;
    public $inscription_date;
    public $first_name;
    public $last_name;
    public $gender;
    public $birthdate;
    public $place;
    public $lngLat;
    public $level;
    public $description;
    public $twitter;
    public $facebook;
    public $instagram;
    public $strava;
    protected $table = 'users';
    
    function __construct($id = NULL) {
        parent::__construct();
        $this->id                        = $id;
        $data = $this->getData($this->table);
        $this->login                     = $data['login'];
        $this->email                     = $data['email'];
        $this->default_profilepicture_id = $data['default_profilepicture_id'];
        $this->inscription_date          = $data['inscription_date'];
        $this->first_name                = $data['first_name'];
        $this->last_name                 = $data['last_name'];
        $this->gender                    = $data['gender'];
        $this->birthdate                 = $data['birthdate'];
        $this->location                  = new Geolocation($data['city'], $data['prefecture']);
        $this->lngLat                    = new LngLat($data['lng'], $data['lat']);
        $this->level                     = $data['level'];
        $this->description               = $data['description'];
        $this->twitter                   = $data['twitter'];
        $this->facebook                  = $data['facebook'];
        $this->instagram                 = $data['instagram'];
        $this->strava                    = $data['strava'];
    }

    // Register user into database
    public function register ($email, $login, $password) {
        $this->default_profilepicture_id = rand(1,9);
        $this->email                     = $email;
        $this->login                     = $login;
        // Insert data into database
        $register = $this->getPdo()->prepare('INSERT INTO users(email, login, password, default_profilepicture_id, inscription_date, level) VALUES (?, ?, ?, ?, ?, ?)');
        $register->execute(array($email, $login, $password, rand(1,9), date('Y-m-d'), 'Beginner'));
        // Get id
        $this->id                        = $this->getData()['id'];
    }

    // Set session according to user data
    public function setSession () {
        $_SESSION['auth']                      = true;
        $_SESSION['id']                        = $this->id;
        $_SESSION['email']                     = $this->email;
        $_SESSION['login']                     = $this->login;
        $_SESSION['default_profilepicture_id'] = $this->default_profilepicture_id;
        $_SESSION['inscription_date']          = $this->inscription_date;
        $_SESSION['location']                  = $this->location;
        $_SESSION['lngLat']                    = $this->lngLat;
        $_SESSION['settings']                  = $this->getSettings();
		$_SESSION['rights']                    = $this->getRights();
    }

    public function getSettings() {
        return new Settings ($this->id);
    }

    public function updateSettings($settings) {
        foreach ($settings as $key => $setting) {
            if ($setting == true) $value = 1;
            else $value = 0;
            $updateSetting = $this->getPdo()->prepare("UPDATE settings SET {$key} = :value WHERE id = :id");
            $updateSetting->execute([':value' => $value, ':id' => $this->id]);
            return true;
        }
    }

    // Get user rights from users table
    public function getRights() {
        $getRights = $this->getPdo()->prepare('SELECT rights FROM users WHERE id = ?');
        $getRights->execute(array($this->id));
        return $getRights->fetch(PDO::FETCH_NUM)[0];
    }

    public function checkIfLoginAlreadyExists ($login) {
        $checkIfUserAlreadyExists = $this->getPdo()->prepare('SELECT login FROM users WHERE login = ?');
        $checkIfUserAlreadyExists->execute(array($login));
        if($checkIfUserAlreadyExists->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function checkIfEmailAlreadyExists ($email) {
        $checkIfEmailAlreadyExists = $this->getPdo()->prepare('SELECT email FROM users WHERE email = ?');
        $checkIfEmailAlreadyExists->execute(array($email));
        if($checkIfEmailAlreadyExists->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }

    // Function for checking password strength : at least 6 characters
    public function checkPasswordStrength($password){
        $number = preg_match('@[0-9]@', $password);
        if(strlen($password) < 6) {
            return false;
        }else{
            return true;
        }
    }

    public function getPassword () {
        $getUserInfos = $this->getPdo()->prepare('SELECT password FROM users WHERE id = ?');
        $getUserInfos->execute(array($this->id));
        return $password = $getUserInfos->fetch(PDO::FETCH_NUM)[0];
    }
    
    public function getInscriptionDate($user){
        $getUserInfos = $this->getPdo()->prepare('SELECT * FROM users WHERE id = ?');
        $getUserInfos->execute(array($user));
        return $user_infos = $getUserInfos->fetch();
    }

    // Function calculating an age from birthdate
    public function calculateAge () {
        $today = date("Y-m-d");
        $diff = date_diff(date_create($this->birthdate), date_create($today));
        return $diff->format('%y');
    }

    // Update user location in database
    public function setLocation ($geolocation, $lngLat) {
        $setLocation = $this->getPdo()->prepare('UPDATE users SET city = ?, prefecture = ?, lng = ?, lat = ? WHERE id = ?');
        $setLocation->execute([$geolocation->city, $geolocation->prefecture, $lngLat->lng, $lngLat->lat, $this->id]);
        return true;
    }

    // Register a friend request
    public function sendFriendRequest ($friend) {
        
        // Check if an entry exists with inviter and receiver id
        $checkIfAlreadySentARequest = $this->getPdo()->prepare('SELECT * FROM friends WHERE (inviter_id = :inviter AND receiver_id = :receiver) OR (inviter_id = :receiver AND receiver_id = :inviter)');
        $checkIfAlreadySentARequest->execute([":inviter" => $this->id, ":receiver" => $friend->id]);
        $friendship = $checkIfAlreadySentARequest->fetch();
        
        // If there is one, return false with an error message depending on if the friends request has already been accepted by receiver or not
        if ($checkIfAlreadySentARequest->rowCount() > 0) {
            // If accepted is set to true
            if ($friendship['accepted']) return array('error' => "You already are friend with " .$friend->login. ".");
            // If accepted is set to false and current user is the inviter
            else if ($friendship['inviter_id'] == $_SESSION['id']) return array('error' => "You already sent an invitation to " .$friend->login. ".");
            // else (If accepted is set to false and current user is the receiver)
            else return array('error' => $friend->login. ' has already sent you an invitation. You can accept or dismiss it on <a href="/riders/friends.php">your friends page</a>.');
            
        // If there is no existing entry, insert a new friendship relation (before validation) in friends table, and return true and a success message
        } else {
            $createNewFriendship = $this->getPdo()->prepare('INSERT INTO friends(inviter_id, inviter_login, receiver_id, receiver_login, invitation_date) VALUES (?, ?, ?, ?, ?)');
            $createNewFriendship->execute(array($this->id, $this->login, $friend->id, $friend->login, date('Y-m-d')));
            return array('success' => "Your friends request has been sent to " .$friend->login. " !");
        }
    }

    // Set a friend request to accepted
    public function acceptFriendRequest ($friend) {
        // Set friendship status to "accepted"
        $acceptFriendsRequest = $this->getPdo()->prepare('UPDATE friends SET accepted = 1, approval_date = :approval_date WHERE (inviter_id = :inviter AND receiver_id = :receiver) OR (inviter_id = :receiver AND receiver_id = :inviter) AND accepted = 0');
        $acceptFriendsRequest->execute([":approval_date" => date('Y-m-d'), ":inviter" => $this->id, ":receiver" => $friend->id]);
        if ($acceptFriendsRequest->rowCount() > 0) return array('success' => $friend->login .' has been added to your friends list !');
        else return array('error' => 'You already are friends with ' .$friend->login. '.');
    }

    // Remove a friendship relation
    public function removeFriend ($friend) {
		$removeFriends = $this->getPdo()->prepare('DELETE FROM friends WHERE CASE WHEN inviter_id = :user_id THEN receiver_id = :friend WHEN receiver_id = :user_id THEN inviter_id = :friend END');
		$removeFriends->execute([":user_id" => $this->id, ":friend" => $friend->id]);
        if ($removeFriends->rowCount() > 0) return array('success' =>  $friend->login .' has been removed from your friends list.');
        else return array('error' => 'You are not friends with ' .$friend->login. '.');
    }

    public function getFriends () {
        $getFriends = $this->getPdo()->prepare('SELECT CASE WHEN inviter_id = :user_id THEN receiver_id WHEN receiver_id = :user_id THEN inviter_id END FROM friends WHERE (inviter_id = :user_id OR receiver_id = :user_id) AND accepted = 1');
        $getFriends->execute(array(":user_id" => $this->id));
        return array_column($getFriends->fetchAll(PDO::FETCH_NUM), 0);
    }

    public function isFriend ($friend) {
        $friendslist = $this->getFriends();
        if (in_array_r($friend->id, $friendslist)) return true;
        else return false;
    }

    public function getRequesters () {
        // Get all infos about friends of connected user from database in a multidimensionnal array
        $getRequesters = $this->getPdo()->prepare('SELECT inviter_id FROM friends WHERE receiver_id = :user AND accepted = false');
        $getRequesters->execute([":user" => $this->id]);
        $combinedData = $getRequesters->fetchAll();
        // Get requesters ids into a simple array
        $requesters = array();
        for ($i = 0; isset($combinedData[$i]); $i++) {
            array_push($requesters, $combinedData[$i][0]);
        }
        return $requesters;
    }

    public function friendsSince ($friend_id) {
        $friendslist = $this->getFriends();
        $getApprovalDate = $this->getPdo()->prepare('SELECT approval_date FROM friends WHERE (inviter_id = :user_id OR receiver_id = :user_id)');
        $getApprovalDate->execute(array(":user_id" => $friend_id));
        $approval_date = $getApprovalDate->fetch();
        return $approval_date[0];
    }

    // Insert a new entry in followers table
    public function follow ($user) {
        $follow = $this->getPdo()->prepare('INSERT INTO followers (following_id, followed_id, following_date) VALUES (?, ?, ?)');
        $follow->execute(array($this->id, $user->id, date("Y-m-d H:i:s")));
        if ($follow->rowCount() > 0) return array('success' => 'You now are following ' . $user->login . ' !');
        else return array('error' => 'You are already following ' . $user->login . '.');
    }

    // Removes an entry in followers table
    public function unfollow ($user) {
        $unfollow = $this->getPdo()->prepare('DELETE FROM followers WHERE following_id = ? AND followed_id = ?');
        $unfollow->execute(array($this->id, $user->id));
        if ($unfollow->rowCount() > 0) return array('success' => 'You are no more following ' . $user->login . '.');
        else return array('error' => 'You have already unfollowed ' . $user->login . '.');
    }

    // Checks if follows a specific user
    public function follows ($user) {
        $checkIfFollows = $this->getPdo()->prepare('SELECT id FROM followers WHERE following_id = ? AND followed_id = ?');
        $checkIfFollows->execute(array($this->id, $user->id));
        if ($checkIfFollows->rowCount() > 0) return true;
        else return false;
    }

    // Checks if is followed by a specific user
    public function isFollowed ($user) {
        $checkIfIsFollowed = $this->getPdo()->prepare('SELECT id FROM followers WHERE following_id = ? AND followed_id = ?');
        $checkIfIsFollowed->execute(array($user->id, $this->id));
        if ($checkIfIsFollowed->rowCount() > 0) return true;
        else return false;
    }

    public function getFollowedList () {
        $getFollowedList = $this->getPdo()->prepare('SELECT following_id FROM followers WHERE followed_id = ?');
        $getFollowedList->execute(array($this->id));
        return array_column($getFollowedList->fetchAll(PDO::FETCH_NUM), 0);
    }

    public function getFollowingList () {
        $getFollowingList = $this->getPdo()->prepare('SELECT followed_id FROM followers WHERE following_id = ?');
        $getFollowingList->execute(array($this->id));
        return array_column($getFollowingList->fetchAll(PDO::FETCH_NUM), 0);
    }

    public function getFriendsAndFollowingList () {
        $getFriendsAndFollowingList = $this->getPdo()->prepare('SELECT followed_id FROM followers WHERE following_id = :user_id UNION SELECT CASE WHEN inviter_id = :user_id THEN receiver_id WHEN receiver_id = :user_id THEN inviter_id END FROM friends WHERE (inviter_id = :user_id OR receiver_id = :user_id) AND accepted = 1');
        $getFriendsAndFollowingList->execute(array(':user_id' => $this->id));
        return array_column($getFriendsAndFollowingList->fetchAll(PDO::FETCH_NUM), 0);
    }

    public function getDistance ($user) {
        $user_location = new Coordinate($user->lngLat->lat, $user->lngLat->lng);
        $this_location = new Coordinate($this->lngLat->lat, $this->lngLat->lng);
        $calculator = new Vincenty();
        return round($calculator->getDistance($user_location, $this_location) / 1000, 1);
    }

    // Function for uploading a profile picture
    function uploadPropic () {
        
        // Declaration of variables
        $img_blob   = '';
        $img_size   = 0;
        $max_size   = 500000;
        $img_name   = '';
        $img_type   = '';
            
        // Displays an error message if any problem through upload
        if (!is_uploaded_file($_FILES['propicfile']['tmp_name'])) {
            return array('error' => "A problem has occured during file upload.");
                
        } else {
                
            // Displays an error message if file size exceeds $max_size
            $img_size = $_FILES['propicfile']['size'];
            if ($img_size > $max_size) return array('error' => 'The image you uploaded exceeds size limit (500kb). Please reduce the size and try again.');
            
            // Displays an error message if format is not accepted
            $img_type = $_FILES['propicfile']['type'];
            if ($img_type != 'image/jpeg') return array('error' => 'The file you uploaded is not at *.jpg format. Please try again with a *.jpg image file.');
                
            // Sort upload data into variables
            $img_name = $_FILES['propicfile']['name'];
            $img_blob = file_get_contents($_FILES['propicfile']['tmp_name']);
                            
            // Check if connected user has already uploaded a picture
            $checkUserId = $this->getPdo()->prepare('SELECT user_id FROM profile_pictures WHERE user_id = ?');
            $checkUserId->execute(array($this->id));
                
            // If he does, update data in the database
            if ($checkUserId->rowCount() > 0) {
                $updateImage = $this->getPdo()->prepare('UPDATE profile_pictures SET img = ?, size = ?, name = ?, type = ? WHERE user_id = ?');
                $updateImage->execute(array($img_blob, $img_size, $img_name, $img_type, $this->id));
                    
            // If he doesn't, insert a new line into the database
            } else {
                $insertImage = $this->getPdo()->prepare('INSERT INTO profile_pictures (user_id, img, size, name, type) VALUES (?, ?, ?, ?, ?)');
                $insertImage->execute(array($this->id, $img_blob, $img_size, $img_name, $img_type));
            }
                		
            return array('success' => 'Profile picture has correctly been updated !');
        }
    }

    // Function for downloading users's profile picture
    public function downloadPropic () {
        // Check if there is an image that corresponds to connected user in the database
        $checkUserId = $this->getPdo()->prepare('SELECT user_id FROM profile_pictures WHERE user_id = ?');
        $checkUserId->execute(array($this->id));
        $checkUserId->fetch();
        // If there is one, execute the code
        if ($checkUserId->rowCount() > 0) {	
            $getImage = $this->getPdo()->prepare('SELECT * FROM profile_pictures WHERE user_id = ?');
            $getImage->execute(array($this->id));
            return $getImage->fetch(PDO::FETCH_ASSOC);	
        } else return 'couldn\'t get image data from database.';
    }

    // Function for getting user's profile picture element with defined height, width and border-radius attributes
    public function getPropicElement ($height = 60, $width = 60, $borderRadius = 60) {
        $propic = $this->downloadPropic();
        
        // If the user has uploaded a picture, use it as profile picture
        if (isset($propic['img'])) {
            return '<div style="height: ' .$height. 'px; width: ' .$width. 'px;" class="free-propic-container"><img style="border-radius: ' .$borderRadius. 'px;" class="free-propic-img" src="data:image/jpeg;base64,' .base64_encode($propic['img']). '" /></div>';
            
        // Else, use a profile picture corresponding to user's randomly attribuated icon
        } else {
            require '../actions/databaseAction.php';
            $getImage = $this->getPdo()->prepare('SELECT default_profilepicture_id FROM users WHERE id = ?');
            $getImage->execute(array($this->id));
            $picture = $getImage->fetch();
            return `
            <div style="height: ` .$height. `px; width: ` .$width. `px;" class="free-propic-container">
                <img style="border-radius: ` .$borderRadius. `px;" class="free-propic-img" src="\media\default-profile-` .$picture['default_profilepicture_id']. `.jpg" />
            </div>`;
        }
    }

    // Function for downloading & displaying user's profile picture with defined height, width and border-radius attributes
    public function displayPropic ($height = 60, $width = 60, $borderRadius = 60) {
        $propic = $this->downloadPropic();
        
        // If the user has uploaded a picture, use it as profile picture
        if (isset($propic['img'])) { ?>
            <div style="height: <?= $height ?>px; width: <?= $width ?>px;" class="free-propic-container">
                <img style="border-radius: <?= $borderRadius ?>px;" class="free-propic-img" src="data:image/jpeg;base64,<?= base64_encode($propic['img']) ?>" />
            </div> <?php
            
        // Else, use a profile picture corresponding to user's randomly attribuated icon
        } else {
            require '../actions/databaseAction.php';
            $getImage = $this->getPdo()->prepare('SELECT default_profilepicture_id FROM users WHERE id = ?');
            $getImage->execute(array($this->id));
            $picture = $getImage->fetch(); ?>
            <div style="height: <?= $height ?>px; width: <?= $width ?>px;" class="free-propic-container">
                <img style="border-radius: <?= $borderRadius ?>px;" class="free-propic-img" src="/media/default-profile-<?= $picture['default_profilepicture_id'] ?>.jpg" />
            </div> <?php
        }
    }

    // Get default profile picture of an user
    public function getDefaultPropicId () {
        $getDefaultPropicId = $this->getPdo()->prepare('SELECT default_profilepicture_id FROM users WHERE id = ?');
        $getDefaultPropicId->execute(array($this->id));
        return $getDefaultPropicId->fetch()[0];
    }

    public function getPropicSrc () {
        $profile_picture = $this->downloadPropic();
        if (!is_string($profile_picture)) {
            return 'data:image/jpeg;base64,' . base64_encode($profile_picture['img']);
        } else {
            $picture = $this->getDefaultPropicId();
            return '\media\default-profile-' . $picture . '.jpg';
        }
    }

    // Get bikes infos of a specific user from the bikes table
    public function getBikes () {
        $getBikes = $this->getPdo()->prepare('SELECT id FROM bikes WHERE user_id = ? ORDER BY number');
        $getBikes->execute(array($this->id));
        return $getBikes->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if user bikes and ride accepted bikes correspond or not
    public function checkIfAcceptedBikesMatches ($ride) {
        
        // Get accepted bikes info
        $getAcceptedBikesInfos = $this->getPdo()->prepare('SELECT citybike, roadbike, mountainbike, gravelcxbike FROM rides WHERE id = ?');
        $getAcceptedBikesInfos->execute(array($ride->id));
        $ride_accepted_bikes = $getAcceptedBikesInfos->fetch(PDO::FETCH_ASSOC);
        
        // Get user bikes info
        $user_bikes = $this->getBikes();
        
        // Iterates accepted bikes list of the ride
        foreach ($ride_accepted_bikes as $ride_bike_type => $boolean) {
            // For each bike accepted,
            if ($boolean) {
                // Check if there is a bike type matching in user's bike list
                foreach ($user_bikes as $entry) {
                    $user_bike = new Bike ($entry['id']);
                    if (getBikesFromColumnName($ride_bike_type) == $user_bike->type) {
                        // If there is one, return true
                        return true;
                    }
                }
            }
        }
        // If no match have been found, return false
        return false;
    }

    public function getRides () {
        $getRides = $this->getPdo()->prepare('SELECT * FROM rides WHERE author_id = ? ORDER BY posting_date DESC');
	    $getRides->execute(array($this->id));
        return $getRides->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoutes ($offset = 0, $limit = 20) {
        $getRoutes = $this->getPdo()->prepare("SELECT * FROM routes WHERE author_id = ? AND category = 'route' ORDER BY posting_date DESC LIMIT " .$offset. ", " .$limit);
	    $getRoutes->execute(array($this->id));
        return $getRoutes->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoutesNumber () {
        $getRoutes = $this->getPdo()->prepare("SELECT name FROM routes WHERE author_id = ? AND category = 'route'");
	    $getRoutes->execute(array($this->id));
        return $getRoutes->rowCount();
    }

    public function getActivities ($offset = 0, $limit = 20) {
        $getActivities = $this->getPdo()->prepare("SELECT * FROM activities WHERE user_id = ? ORDER BY datetime DESC LIMIT " .$offset. ", " .$limit);
	    $getActivities->execute(array($this->id));
        return $getActivities->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicActivities ($offset = 0, $limit = 20) {

        // Get period of rides to display
        $friends_list = $this->getFriends();
        $following_list = $this->getFollowingList();
        $following_number = count($this->getFriendsAndFollowingList());
        if ($following_number < 3) $period = 999;
        else if ($following_number < 8) $period = 28;
        else if ($following_number < 18) $period = 20;
        else if ($following_number < 25) $period = 14;
        else $period = 10;

        // Request activities
        $getActivities = $this->getPdo()->prepare(
            "SELECT
            id, user_id, title, privacy
            FROM
            activities
        WHERE 
            datetime > DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
            AND
                (
                    (privacy = 'private' AND user_id = ?)
                    OR
                    (privacy = 'friends_only' AND user_id IN ('".implode("','",$friends_list)."'))
                    OR
                    (privacy = 'public' AND (
                        user_id IN ('".implode("','",$friends_list)."') OR
                        user_id IN ('".implode("','",$following_list)."')
                    )
                )
            )
        ORDER BY
            datetime DESC
        LIMIT " .$offset. ", " .$limit);

	    $getActivities->execute(array($period, $this->id));
        $activities = $getActivities->fetchAll(PDO::FETCH_ASSOC);

        // If resulted array if shorter than [limit], complete with most liked public activities of last [period] days
        if ($results_number = count($activities) < $limit) {
            $getFurtherActivities = $this->getPdo()->prepare("SELECT id, user_id, title, privacy FROM activities WHERE datetime > DATE_SUB(CURRENT_DATE, INTERVAL ? DAY) AND privacy = 'public' ORDER BY likes, datetime, posting_date DESC LIMIT " .$offset. ", " .($limit - $results_number));
            $getFurtherActivities->execute(array($period));
            $further_activities = $getFurtherActivities->fetchAll(PDO::FETCH_ASSOC);
            foreach ($further_activities as $further_activity) {
                $already_listed = false;
                foreach ($activities as $activity) {
                    if ($activity['id'] == $further_activity['id']) $already_listed = true;
                }
                if (!$already_listed) array_push($activities, $further_activity);
            }
        }

        // If still shorter than [limit], complete with other most liked public activities, regardless of [period]
        if (count($activities) < $limit) {
            $getFurtherActivities2 = $this->getPdo()->prepare("SELECT id, user_id, title, privacy FROM activities WHERE privacy = 'public' ORDER BY likes, datetime, posting_date DESC LIMIT " .$offset. ", " .($limit - count($activities)));
            $getFurtherActivities2->execute();
            $further_activities2 = $getFurtherActivities2->fetchAll(PDO::FETCH_ASSOC);
            foreach ($further_activities2 as $further_activity) {
                $already_listed = false;
                foreach ($activities as $activity) {
                    if ($activity['id'] == $further_activity['id']) $already_listed = true;
                }
                if (!$already_listed) array_push($activities, $further_activity);
            }
        }

        return $activities;
    }

    public function getActivitiesNumber () {
        $getActivities = $this->getPdo()->prepare("SELECT title FROM activities WHERE user_id = ?");
	    $getActivities->execute(array($this->id));
        return $getActivities->rowCount();
    }

    // Get $number last activity photos
    public function getLastActivityPhotos ($number) {
        $getLastActivityPhotos = $this->getPdo()->prepare("SELECT id FROM activity_photos WHERE user_id = ? ORDER BY datetime DESC LIMIT " . $number);
        $getLastActivityPhotos->execute(array($this->id));
        return array_column($getLastActivityPhotos->fetchAll(PDO::FETCH_NUM), 0);
    }

    // Get all messages between two users
    public function getConversation ($user) {
        $getConversation = $this->getPdo()->prepare('SELECT id FROM messages WHERE sender_id = :user1 AND receiver_id = :user2 UNION SELECT id FROM messages WHERE sender_id = :user2 AND receiver_id = :user1 ORDER BY id');
        $getConversation->execute(array(":user1" => $this->id, ":user2" => $user->id));
        $log = $getConversation->fetchAll(PDO::FETCH_ASSOC);
        return new Log($log);
    }

    // Get last message between two users
    public function getLastMessage ($user) {
        $getLastMessage = $this->getPdo()->prepare('SELECT id FROM messages WHERE sender_id = :user1 AND receiver_id = :user2 UNION SELECT id FROM messages WHERE sender_id = :user2 AND receiver_id = :user1 ORDER BY id DESC');
        $getLastMessage->execute(array(":user1" => $this->id, ":user2" => $user->id));
        $lastmessage = $getLastMessage->fetch(PDO::FETCH_ASSOC);
        if (!empty($lastmessage)) {
            return new DirectMessage($lastmessage['id']);
        } else {
            return null;
        }
    }

    // Get all conversations
    public function getUsersWithMessages () {
        $getUsersWithMessages = $this->getPdo()->prepare('SELECT DISTINCT CASE WHEN sender_id = :user THEN receiver_id WHEN receiver_id = :user THEN sender_id END FROM messages WHERE sender_id = :user UNION SELECT DISTINCT CASE WHEN sender_id = :user THEN receiver_id WHEN receiver_id = :user THEN sender_id END FROM messages WHERE receiver_id = :user');
        $getUsersWithMessages->execute(array(":user" => $this->id));
        return array_column($getUsersWithMessages->fetchAll(PDO::FETCH_NUM), 0);
    }

    // Get last message of all conversations sorted from newest to oldest
    public function getLastMessages ($userslist) { // array of user ids
        $friends = array(); $last_messages = array();
        for ($i = 0; $i < count($userslist); $i++) {
            // Get last messages
            $friends[$i]       = new User($userslist[$i]);
            $last_messages[$i] = $this->getLastMessage($friends[$i]);
            if ($last_messages[$i] != null) {
                // Get friend info inside friend property
                if ($last_messages[$i]->sender->id === $this->id) {
                    $last_messages[$i]->friend = $last_messages[$i]->receiver;
                } else {
                    $last_messages[$i]->friend = $last_messages[$i]->sender;
                }
                $last_messages[$i]->friend->propic = $last_messages[$i]->friend->getPropicSrc();
            // If no message with this user, remove it from results
            } else {
                unset($last_messages[$i]);
            }
        }
        // Sort by id
		rsort($last_messages);
        
        return $last_messages;
    }

    // Insert a new message in the message table
    public function sendMessage ($receiver, $message){        
        $addMessage = $this->getPdo()->prepare('INSERT INTO messages (sender_id, sender_login, receiver_id, receiver_login, message, time) VALUES (?, ?, ?, ?, ?, ?)');
        $addMessage->execute(array($this->id, $this->login, $receiver->id, $receiver->login, $message, date('Y-m-d H:i:s')));
    }

    // Get currently saved cleared mkpoints list
    public function getClearedMkpoints ($limit = 99999) {
        $getClearedMkpoints = $this->getPdo()->prepare("SELECT u.mkpoint_id, u.activity_id FROM user_mkpoints AS u JOIN activities AS a ON u.activity_id = a.id WHERE u.user_id = ? ORDER BY a.datetime DESC LIMIT 0," .$limit. "");
        $getClearedMkpoints->execute(array($this->id));
        $cleared_mkpoints = $getClearedMkpoints->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cleared_mkpoints as $cleared_mkpoint) {
            $checkIfActivityExists = $this->getPdo()->prepare('SELECT id FROM activities WHERE id = ?');
            $checkIfActivityExists->execute(array($cleared_mkpoint['activity_id']));
            // If activity in which mkpoint has been cleared has been deleted, remove from cleared mkpoints list
            if ($checkIfActivityExists->rowCount() == 0) {
                if (($key = array_search($cleared_mkpoint, $cleared_mkpoints)) !== false) {
                    unset($cleared_mkpoints[$key]);
                    $removeClearedMkpoint = $this->getPdo()->prepare('DELETE FROM user_mkpoints WHERE mkpoint_id = ?');
                    $removeClearedMkpoint->execute(array($cleared_mkpoint['mkpoint_id']));
                }
            }
        }
        return $cleared_mkpoints;
    }

    public function countClearedMkpoints() {
        $countClearedMkpoints = $this->getPdo()->prepare("SELECT DISTINCT mkpoint_id FROM user_mkpoints WHERE user_id = ?");
        $countClearedMkpoints->execute(array($this->id));
        return $countClearedMkpoints->rowCount();
    }

    public function getPublicMkpoints ($offset = 0, $limit = 20) {
        // Get period of mkpoints to display
        $friends_and_following_list = $this->getFriendsAndFollowingList();
        $friends_and_following_number = count($friends_and_following_list);
        if ($friends_and_following_number < 3) $period = 999;
        else if ($friends_and_following_number < 8) $period = 28;
        else if ($friends_and_following_number < 18) $period = 20;
        else if ($friends_and_following_number < 25) $period = 14;
        else $period = 10;
        // Request mkpoints
        $getMkpoints = $this->getPdo()->prepare("SELECT id FROM map_mkpoint WHERE publication_date > DATE_SUB(CURRENT_DATE, INTERVAL ? DAY) AND user_id IN ('".implode("','",$friends_and_following_list)."','".$this->id."') ORDER BY publication_date DESC LIMIT " .$offset. ", " .$limit);
        $getMkpoints->execute(array($period));
        return $getMkpoints->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get currently saved cleared segments list
    public function getClearedSegments ($limit = 99999) {
        $getClearedSegments = $this->getPdo()->prepare("SELECT DISTINCT u.segment_id, u.activity_id FROM user_segments AS u JOIN activities AS a ON u.activity_id = a.id WHERE u.user_id = ? ORDER BY a.datetime DESC LIMIT 0," .$limit. "");
        $getClearedSegments->execute(array($this->id));
        $cleared_segments = $getClearedSegments->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cleared_segments as $cleared_segment) {
            $checkIfActivityExists = $this->getPdo()->prepare('SELECT id FROM activities WHERE id = ?');
            $checkIfActivityExists->execute(array($cleared_segment['activity_id']));
            // If activity in which segment has been cleared has been deleted, remove from cleared segments list
            if ($checkIfActivityExists->rowCount() == 0) {
                if (($key = array_search($cleared_segment, $cleared_segments)) !== false) {
                    unset($cleared_segments[$key]);
                    $removeClearedSegment = $this->getPdo()->prepare('DELETE FROM user_msegments WHERE segment_id = ?');
                    $removeClearedSegment->execute(array($cleared_segment['segment_id']));
                }
            }
        }
        return $cleared_segments;
    }

    public function countClearedSegments() {
        $countClearedSegments = $this->getPdo()->prepare("SELECT DISTINCT segment_id FROM user_segments WHERE user_id = ?");
        $countClearedSegments->execute(array($this->id));
        return $countClearedSegments->rowCount();
    }

    public function getThread ($offset = 0, $limit = null) {
        $activities_data = $this->getPublicActivities();
        $mkpoint_data = $this->getPublicMkpoints();
        // Build thread data
        $thread_data = [];
        foreach ($activities_data as $data) {
            $activity = new Activity($data['id']);
            $activity->type = 'activity';
            array_push($thread_data, $activity);
        }
        foreach ($mkpoint_data as $data) {
            $mkpoint = new Mkpoint($data['id']);
            $mkpoint->type = 'mkpoint';
            array_push($thread_data, $mkpoint);
        }
        // Sort thread data by date
        function sort_by_date ($a, $b) {
            if (isset($a->publication_date)) $a->date = $a->publication_date;
            else if (isset($a->datetime)) $a->date = $a->datetime;
            if (isset($b->publication_date)) $b->date = $b->publication_date;
            else if (isset($b->datetime)) $b->date = $b->datetime;
            return $a->date < $b->date;
        }
        usort($thread_data, 'sort_by_date');
        return array_slice($thread_data, $offset, $limit);
    }

    // Update cleared mkpoints list in the database according to newly uploaded activities
    /*public function updateClearedMkpoints ($activity = false) {

        $activities = $this->getActivities();

        // For each activity route, get close mkpoints and add them to cleared mkpoints
        if ($activity) {
            $cleared_mkpoints = $activity->route->getCloseMkpoints(500, false);
            foreach ($cleared_mkpoints as $cleared_mkpoint) $cleared_mkpoint['activity_id'] = $activity->id;
        } else {
            $cleared_mkpoints = [];
            foreach ($activities as $activity) {
                $activity = new Activity($activity['id']);
                $mkpoints = $activity->route->getCloseMkpoints(500, false);
                foreach ($mkpoints as $mkpoint) {
                    $mkpoint['activity_id'] = $activity->id;
                    if (!in_array_r($mkpoint['id'], $cleared_mkpoints, true)) array_push($cleared_mkpoints, $mkpoint);
                }
            }
        }

        // Filter mkpoints to add
        $saved_mkpoints = $this->getClearedMkpoints();
        $mkpoints_to_add = [];
        foreach ($cleared_mkpoints as $cleared_mkpoint) {
            $already_saved = false;
            foreach ($saved_mkpoints as $saved_mkpoint) {
                if ($saved_mkpoint['mkpoint_id'] == $cleared_mkpoint['id'] && $saved_mkpoint['activity_id'] == $cleared_mkpoint['activity_id']) $already_saved = true;
            }
            if (!$already_saved) array_push($mkpoints_to_add, $cleared_mkpoint);
        }

        // Add relevant mkpoints to user mkpoints table
        foreach ($mkpoints_to_add as $mkpoint) {
            $addMkpoint = $this->getPdo()->prepare('INSERT INTO user_mkpoints(user_id, mkpoint_id, activity_id) VALUES (?, ?, ?)');
            $addMkpoint->execute(array($this->id, $mkpoint['id'], $mkpoint['activity_id']));
        }
    }*/

}