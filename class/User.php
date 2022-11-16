<?php

use Location\Coordinate;
use Location\Polyline;
use Location\Distance\Vincenty;
use Location\Utility\PointToLineDistance;

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
    public $level;
    public $description;
    public $twitter;
    public $facebook;
    public $instagram;
    public $strava;
    protected $table = 'users';
    
    function __construct($id = NULL) {
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
        $this->place                     = $data['place'];
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
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $register = $db->prepare('INSERT INTO users(email, login, password, default_profilepicture_id, inscription_date, level) VALUES (?, ?, ?, ?, ?, ?)');
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
        $_SESSION['settings']                  = $this->getSettings();
		$_SESSION['rights']                    = $this->getRights();
    }

    // Get user settings from settings table
    public function getSettings() {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getSettings = $db->prepare('SELECT * FROM settings WHERE user_id = ?');
        $getSettings->execute(array($this->id));
        return $getSettings->fetch(PDO::FETCH_ASSOC);
    }

    // Get user rights from users table
    public function getRights() {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getRights = $db->prepare('SELECT rights FROM users WHERE id = ?');
        $getRights->execute(array($this->id));
        return $getRights->fetch(PDO::FETCH_NUM)[0];
    }

    // Check if there is an entry in settings table matching a specific user ID
    public function checkIfUserHasSettingsEntry(){
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        
        $checkIfUserHasSettingsEntry = $db->prepare('SELECT user_id FROM settings WHERE user_id = ?');
        $checkIfUserHasSettingsEntry->execute(array($user_id));

        if($checkIfUserHasSettingsEntry->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function checkIfLoginAlreadyExists ($login) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $checkIfUserAlreadyExists = $db->prepare('SELECT login FROM users WHERE login = ?');
        $checkIfUserAlreadyExists->execute(array($login));
        if($checkIfUserAlreadyExists->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function checkIfEmailAlreadyExists ($email) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $checkIfEmailAlreadyExists = $db->prepare('SELECT email FROM users WHERE email = ?');
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
    
    public function getInscriptionDate($user){
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getUserInfos = $db->prepare('SELECT * FROM users WHERE id = ?');
        $getUserInfos->execute(array($user));
        return $user_infos = $getUserInfos->fetch();
    }

    // Function calculating an age from birthdate
    public function calculateAge () {
        $today = date("Y-m-d");
        $diff = date_diff(date_create($this->birthdate), date_create($today));
        return $diff->format('%y');
    }

    // Register a friend request
    public function sendFriendRequest ($friend) {
        
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        // Check if an entry exists with inviter and receiver id
        $checkIfAlreadySentARequest = $db->prepare('SELECT * FROM friends WHERE (inviter_id = :inviter AND receiver_id = :receiver) OR (inviter_id = :receiver AND receiver_id = :inviter)');
        $checkIfAlreadySentARequest->execute([":inviter" => $this->id, ":receiver" => $friend->id]);
        $friendship = $checkIfAlreadySentARequest->fetch();
        
        // If there is one, return false with an error message depending on if the friends request has already been accepted by receiver or not
        if ($checkIfAlreadySentARequest->rowCount() > 0) {
            // If accepted is set to true
            if ($friendship['accepted']) {
                $error = "You already are friend with " .$friend->login. ".";
                return array(false, $error);
            // If accepted is set to false and current user is the inviter
            } else if ($friendship['inviter_id'] == $_SESSION['id']) {
                $error = "You already sent an invitation to " .$friend->login. ".";
                return array(false, $error);
            // else (If accepted is set to false and current user is the receiver)
            } else {
                $error = $friend->login. ' has already sent you an invitation. You can accept or dismiss it on <a href="/riders/friends.php">your friends page</a>.';
                return array(false, $error);
            }
            
        // If there is no existing entry, insert a new friendship relation (before validation) in friends table, and return true and a success message
        } else {
            $createNewFriendship = $db->prepare('INSERT INTO friends(inviter_id, inviter_login, receiver_id, receiver_login, invitation_date) VALUES (?, ?, ?, ?, ?)');
            $createNewFriendship->execute(array($this->id, $this->login, $friend->id, $friend->login, date('Y-m-d')));
            return array(true, ['success' => "Your friends request has been sent to " .$friend->login. " !"]);
        }
    }

    // Set a friend request to accepted
    public function acceptFriendRequest ($friend) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        // Set friendship status to "accepted"
        $acceptFriendsRequest = $db->prepare('UPDATE friends SET accepted = 1, approval_date = :approval_date WHERE (inviter_id = :inviter AND receiver_id = :receiver) OR (inviter_id = :receiver AND receiver_id = :inviter) AND accepted = 0');
        $acceptFriendsRequest->execute([":approval_date" => date('Y-m-d'), ":inviter" => $this->id, ":receiver" => $friend->id]);
        if ($acceptFriendsRequest->rowCount() > 0) return array('success' => $friend->login .' has been added to your friends list !');
        else return array('error' => 'You already are friends with ' .$friend->login. '.');
    }

    // Remove a friendship relation
    public function removeFriend ($friend) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
		$removeFriends = $db->prepare('DELETE FROM friends WHERE CASE WHEN inviter_id = :user_id THEN receiver_id = :friend WHEN receiver_id = :user_id THEN inviter_id = :friend END');
		$removeFriends->execute([":user_id" => $this->id, ":friend" => $friend->id]);
        if ($removeFriends->rowCount() > 0) return array('success' =>  $friend->login .' has been removed from your friends list.');
        else return array('error' =>  'You are not friends with ' .$friend->login. '.');
    }

    public function getFriends () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getFriends = $db->prepare('SELECT CASE WHEN inviter_id = :user_id THEN receiver_id WHEN receiver_id = :user_id THEN inviter_id END FROM friends WHERE (inviter_id = :user_id OR receiver_id = :user_id) AND accepted = 1');
        $getFriends->execute(array(":user_id" => $this->id));
        return array_column($getFriends->fetchAll(PDO::FETCH_NUM), 0);
    }

    public function isFriend ($friend) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $friendslist = $this->getFriends();
        if (in_array_r($friend->id, $friendslist)) return true;
        else return false;
    }

    public function getRequesters () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        // Get all infos about friends of connected user from database in a multidimensionnal array
        $getRequesters = $db->prepare('SELECT inviter_id FROM friends WHERE receiver_id = :user AND accepted = false');
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
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $friendslist = $this->getFriends();
        $getApprovalDate = $db->prepare('SELECT approval_date FROM friends WHERE (inviter_id = :user_id OR receiver_id = :user_id)');
        $getApprovalDate->execute(array(":user_id" => $friend_id));
        $approval_date = $getApprovalDate->fetch();
        return $approval_date[0];
    }

    // Insert a new entry in followers table
    public function follow ($user) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $follow = $db->prepare('INSERT INTO followers (following_id, followed_id, following_date) VALUES (?, ?, ?)');
        $follow->execute(array($this->id, $user->id, date("Y-m-d H:i:s")));
        if ($follow->rowCount() > 0) return array('success' => 'You now are following ' . $user->login . '!');
        else return array('error' => 'You are already following ' . $user->login . '.');
    }

    // Removes an entry in followers table
    public function unfollow ($user) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $unfollow = $db->prepare('DELETE FROM followers WHERE following_id = ? AND followed_id = ?');
        $unfollow->execute(array($this->id, $user->id));
        if ($unfollow->rowCount() > 0) return array('success' => 'You are no more following ' . $user->login . '.');
        else return array('error' => 'You have already unfollowed ' . $user->login . '.');
    }

    // Checks if follows a specific user
    public function follows ($user) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $checkIfFollows = $db->prepare('SELECT id FROM followers WHERE following_id = ? AND followed_id = ?');
        $checkIfFollows->execute(array($this->id, $user->id));
        if ($checkIfFollows->rowCount() > 0) return true;
        else return false;
    }

    // Checks if is followed by a specific user
    public function isFollowed ($user) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $checkIfIsFollowed = $db->prepare('SELECT id FROM followers WHERE following_id = ? AND followed_id = ?');
        $checkIfIsFollowed->execute(array($user->id, $this->id));
        if ($checkIfIsFollowed->rowCount() > 0) return true;
        else return false;
    }

    // Function for downloading users's profile picture
    public function downloadPropic () {
        // Check if there is an image that corresponds to connected user in the database
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $checkUserId = $db->prepare('SELECT user_id FROM profile_pictures WHERE user_id = ?');
        $checkUserId->execute(array($this->id));
        $checkUserId->fetch();
        // If there is one, execute the code
        if ($checkUserId->rowCount() > 0) {	
            $getImage = $db->prepare('SELECT * FROM profile_pictures WHERE user_id = ?');
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
            require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
            $getImage = $db->prepare('SELECT default_profilepicture_id FROM users WHERE id = ?');
            $getImage->execute(array($this->id));
            $picture = $getImage->fetch();
            return `
            <div style="height: ` .$height. `px; width: ` .$width. `px;" class="free-propic-container">
                <img style="border-radius: ` .$borderRadius. `px;" class="free-propic-img" src="\includes\media\default-profile-` .$picture['default_profilepicture_id']. `.jpg" />
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
            require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
            $getImage = $db->prepare('SELECT default_profilepicture_id FROM users WHERE id = ?');
            $getImage->execute(array($this->id));
            $picture = $getImage->fetch(); ?>
            <div style="height: <?= $height ?>px; width: <?= $width ?>px;" class="free-propic-container">
                <img style="border-radius: <?= $borderRadius ?>px;" class="free-propic-img" src="\includes\media\default-profile-<?= $picture['default_profilepicture_id'] ?>.jpg" />
            </div> <?php
        }
    }

    // Get default profile picture of an user
    public function getDefaultPropicId () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getDefaultPropicId = $db->prepare('SELECT default_profilepicture_id FROM users WHERE id = ?');
        $getDefaultPropicId->execute(array($this->id));
        return $getDefaultPropicId->fetch()[0];
    }

    public function getPropicSrc () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $profile_picture = $this->downloadPropic();
        if (!is_string($profile_picture)) {
            return 'data:image/jpeg;base64,' . base64_encode($profile_picture['img']);
        } else {
            $picture = $this->getDefaultPropicId();
            return '\includes\media\default-profile-' . $picture . '.jpg';
        }
    }

    // Get bikes infos of a specific user from the bikes table
    public function getBikes () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getBikes = $db->prepare('SELECT id FROM bikes WHERE user_id = ? ORDER BY number');
        $getBikes->execute(array($this->id));
        return $getBikes->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if user bikes and ride accepted bikes correspond or not
    public function checkIfAcceptedBikesMatches ($ride) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        
        // Get accepted bikes info
        $getAcceptedBikesInfos = $db->prepare('SELECT citybike, roadbike, mountainbike, gravelcxbike FROM rides WHERE id = ?');
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

    // Function for uploading profile gallery
    public function uploadProfileGallery () {
        
        // Declaration of variables
        $return     = false;
        $img_blob   = '';
        $img_size   = 0;
        $max_size   = 10000000;
        $img_name   = '';
        $img_type   = '';
                            
        // Count files and start the loop if there are from 1 to 5 files
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $countfiles = count($_FILES['file']['name']);
        if ($countfiles > 5) {
            $error = 'You can\'t upload more than 5 files. Please try again with 5 files or less.';
            return array(false, $error);
        } else if ($countfiles <= 0 OR empty($_FILES['file']['name'][0])) {
            return;
        } else if ($countfiles <= 5 AND $countfiles > 0) {

            for ($i = 0; $i < $countfiles; $i++) {
                // Display an error message if any problem occured through upload
                $return = is_uploaded_file($_FILES['file']['tmp_name'][$i]);
                if (!$return) {
                    $error = 'Upload problem for ' . $_FILES['file']['name'][$i] . '. Please try again.';
                    return array(false, $error);
                } else {
                    // Display an error message if file size exceeds $max_size
                    if ($_FILES['file']['size'][$i] > $max_size) {
                        $error = $_FILES['file']['name'][$i] . ' exceeds size limit (10Mb). Please reduce the size and try again.';
                        return array(false, $error);
                    } else {
                        $checksuccess = true;
                    }
                }
            }
            
            // If everything have been tested fine
            if ($checksuccess == true) {
                // First, delete current photos
                $deleteCurrentPhotos = $db->prepare('DELETE FROM user_photos WHERE user_id = ?');
                $deleteCurrentPhotos->execute(array($this->id));
                // Then, upload new photos
                for ($i = 0; $i < $countfiles; $i++) {
                    $return = img_compress($_FILES['file']['tmp_name'][$i], $_FILES['file']['size'][$i]);
                    if ($return[0] == true) {
                        $img_blob = $return[1];
                    } else {
                        return $return;
                    }
                    $img_size = $_FILES['file']['size'][$i];
                    $img_name = $_FILES['file']['name'][$i];
                    $img_type = $_FILES['file']['type'][$i];
                    // Upload photo
                    $insertImage = $db->prepare('INSERT INTO user_photos(user_id, img_id, img, size, name, type) VALUES (?, ?, ?, ?, ?, ?)');
                    $insertImage->execute(array($this->id, $i, $img_blob, $img_size, $img_name, $img_type));
                }
                return array(true, $countfiles . ' pictures have been successfully uploaded ! Click <a href="' .$_SERVER['HTTP_REFERER']. '">here</a> to refresh the page and display your changes.');
            }
        }
    }

    // Function for downloading profile gallery
    public function getProfileGallery () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getProfileGallery = $db->prepare('SELECT * FROM user_photos WHERE user_id = ? ORDER BY img_id');
        $getProfileGallery->execute(array($this->id));
        return $getProfileGallery->fetchAll();
    }

    public function getRides () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getRides = $db->prepare('SELECT * FROM rides WHERE author_id = ? ORDER BY posting_date DESC');
	    $getRides->execute(array($this->id));
        return $getRides->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoutes ($offset = 0, $limit = 20) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getRoutes = $db->prepare("SELECT * FROM routes WHERE author_id = ? AND category = 'route' ORDER BY posting_date DESC LIMIT " .$offset. ", " .$limit);
	    $getRoutes->execute(array($this->id));
        return $getRoutes->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoutesNumber () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getRoutes = $db->prepare("SELECT name FROM routes WHERE author_id = ? AND category = 'route'");
	    $getRoutes->execute(array($this->id));
        return $getRoutes->rowCount();
    }

    public function getActivities ($offset = 0, $limit = 20) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getActivities = $db->prepare("SELECT * FROM activities WHERE user_id = ? ORDER BY datetime DESC LIMIT " .$offset. ", " .$limit);
	    $getActivities->execute(array($this->id));
        return $getActivities->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicActivities ($offset = 0, $limit = 20) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        // Get period of rides to display
        $friends_number = count($this->getFriends());
        if ($friends_number < 3) $period = 999;
        else if ($friends_number < 8) $period = 14;
        else if ($friends_number < 18) $period = 10;
        else if ($friends_number < 25) $period = 7;
        else $period = 5;
        // Request activities
        $getActivities = $db->prepare("SELECT * FROM activities WHERE datetime > DATE_SUB(CURRENT_DATE, INTERVAL ? DAY) AND ((privacy = 'private' AND user_id = ?) OR privacy = 'friends_only') ORDER BY datetime, posting_date DESC LIMIT " .$offset. ", " .$limit);
	    $getActivities->execute(array($period, $this->id));
        $activities = $getActivities->fetchAll(PDO::FETCH_ASSOC);
        // Substract all activities with privacy set to friends_only for which user is not friend with connected user
        foreach ($activities as $number => $activity) {
            if ($activity['privacy'] == 'friends_only' AND !$this->isFriend(new User($activity['user_id']))) {
                array_splice($activities, $number, 1);
            }
        }
        // If resulted array if shorter than [limit], complete with most liked public activities of last [period] days
        if ($results_number = count($activities) < $limit) {
            $getFurtherActivities = $db->prepare("SELECT * FROM activities WHERE datetime > DATE_SUB(CURRENT_DATE, INTERVAL ? DAY) AND privacy = 'public' ORDER BY likes, datetime, posting_date DESC LIMIT " .$offset. ", " .($limit - $results_number));
            $getFurtherActivities->execute(array($period));
            $further_activities = $getFurtherActivities->fetchAll(PDO::FETCH_ASSOC);
            $activities = array_merge($activities, $further_activities);
        }
        // If still shorter than [limit], complete with other most liked public activities, regardless of [period]
        if (count($activities) < $limit) {
            $getFurtherActivities2 = $db->prepare("SELECT * FROM activities WHERE privacy = 'public' ORDER BY likes, datetime, posting_date DESC LIMIT " .$offset. ", " .($limit - count($activities)));
            $getFurtherActivities2->execute();
            $further_activities2 = $getFurtherActivities2->fetchAll(PDO::FETCH_ASSOC);
            $activities = array_merge($activities, $further_activities2);
        }
        return $activities;
    }

    public function getActivitiesNumber () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getActivities = $db->prepare("SELECT title FROM activities WHERE user_id = ?");
	    $getActivities->execute(array($this->id));
        return $getActivities->rowCount();
    }

    // Get all messages between two users
    public function getConversation ($user) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getConversation = $db->prepare('SELECT id FROM messages WHERE sender_id = :user1 AND receiver_id = :user2 UNION SELECT id FROM messages WHERE sender_id = :user2 AND receiver_id = :user1 ORDER BY id');
        $getConversation->execute(array(":user1" => $this->id, ":user2" => $user->id));
        $log = $getConversation->fetchAll(PDO::FETCH_ASSOC);
        return new Log($log);
    }

    // Get last message between two users
    public function getLastMessage ($user) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getLastMessage = $db->prepare('SELECT id FROM messages WHERE sender_id = :user1 AND receiver_id = :user2 UNION SELECT id FROM messages WHERE sender_id = :user2 AND receiver_id = :user1 ORDER BY id DESC');
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
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getUsersWithMessages = $db->prepare('SELECT DISTINCT CASE WHEN sender_id = :user THEN receiver_id WHEN receiver_id = :user THEN sender_id END FROM messages WHERE sender_id = :user UNION SELECT DISTINCT CASE WHEN sender_id = :user THEN receiver_id WHEN receiver_id = :user THEN sender_id END FROM messages WHERE receiver_id = :user');
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
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';        
        $addMessage = $db->prepare('INSERT INTO messages (sender_id, sender_login, receiver_id, receiver_login, message, time) VALUES (?, ?, ?, ?, ?, ?)');
        $addMessage->execute(array($this->id, $this->login, $receiver->id, $receiver->login, $message, date('Y-m-d H:i:s')));
    }

    // Get currently saved viewed mkpoints list
    public function getViewedMkpoints ($limit = 99999) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getViewedMkpoints = $db->prepare("SELECT u.mkpoint_id, u.activity_id FROM user_mkpoints AS u JOIN activities AS a ON u.activity_id = a.id WHERE u.user_id = ? ORDER BY a.datetime DESC LIMIT 0," .$limit. "");
        $getViewedMkpoints->execute(array($this->id));
        $viewed_mkpoints = $getViewedMkpoints->fetchAll(PDO::FETCH_ASSOC);
        foreach ($viewed_mkpoints as $viewed_mkpoint) {
            $checkIfActivityExists = $db->prepare('SELECT id FROM activities WHERE id = ?');
            $checkIfActivityExists->execute(array($viewed_mkpoint['activity_id']));
            // If activity in which mkpoint has been viewed has been deleted, remove from viewed mkpoints list
            if ($checkIfActivityExists->rowCount() == 0) {
                if (($key = array_search($viewed_mkpoint, $viewed_mkpoints)) !== false) {
                    unset($viewed_mkpoints[$key]);
                    $removeViewedMkpoint = $db->prepare('DELETE FROM user_mkpoints WHERE mkpoint_id = ?');
                    $removeViewedMkpoint->execute(array($viewed_mkpoint['mkpoint_id']));
                }
            }
        }
        return $viewed_mkpoints;
    }

    // Update viewed mkpoints list in the database according to newly uploaded activities
    /*public function updateViewedMkpoints ($activity = false) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';

        $activities = $this->getActivities();

        // For each activity route, get close mkpoints and add them to viewed mkpoints
        if ($activity) {
            $viewed_mkpoints = $activity->route->getCloseMkpoints(500, false);
            foreach ($viewed_mkpoints as $viewed_mkpoint) $viewed_mkpoint['activity_id'] = $activity->id;
        } else {
            $viewed_mkpoints = [];
            foreach ($activities as $activity) {
                $activity = new Activity($activity['id']);
                $mkpoints = $activity->route->getCloseMkpoints(500, false);
                foreach ($mkpoints as $mkpoint) {
                    $mkpoint['activity_id'] = $activity->id;
                    if (!in_array_r($mkpoint['id'], $viewed_mkpoints, true)) array_push($viewed_mkpoints, $mkpoint);
                }
            }
        }

        // Filter mkpoints to add
        $saved_mkpoints = $this->getViewedMkpoints();
        $mkpoints_to_add = [];
        foreach ($viewed_mkpoints as $viewed_mkpoint) {
            $already_saved = false;
            foreach ($saved_mkpoints as $saved_mkpoint) {
                if ($saved_mkpoint['mkpoint_id'] == $viewed_mkpoint['id'] && $saved_mkpoint['activity_id'] == $viewed_mkpoint['activity_id']) $already_saved = true;
            }
            if (!$already_saved) array_push($mkpoints_to_add, $viewed_mkpoint);
        }

        // Add relevant mkpoints to user mkpoints table
        foreach ($mkpoints_to_add as $mkpoint) {
            $addMkpoint = $db->prepare('INSERT INTO user_mkpoints(user_id, mkpoint_id, activity_id) VALUES (?, ?, ?)');
            $addMkpoint->execute(array($this->id, $mkpoint['id'], $mkpoint['activity_id']));
        }
    }*/

}