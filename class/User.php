<?php

use Location\Coordinate;
use Location\Distance\Vincenty;
use \SendGrid\Mail\Mail;

class User extends Model {

    private $container_name = 'user-profile-pictures';
    private $slug;
    private $verified;
    protected $table = 'users';
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
    public $rights;
    
    function __construct($id = NULL) {
        parent::__construct();
        if ($id != NULL) {
            $this->id = $id;
            $this->populate();
        }
    }

    private function populate () {
        $data = $this->getData($this->table);
        $this->slug                      = intval($data['slug']);
        $this->verified                  = (intval($data['verified']) === 1);
        $this->login                     = $data['login'];
        $this->email                     = $data['email'];
        $this->default_profilepicture_id = $data['default_profilepicture_id'];
        $this->inscription_date          = $data['inscription_date'];
        $this->first_name                = $data['first_name'];
        $this->last_name                 = $data['last_name'];
        $this->gender                    = $data['gender'];
        $this->birthdate                 = $data['birthdate'];
        $this->location                  = new Geolocation($data['city'], $data['prefecture']);
        $this->lngLat                    = $this->getLngLat();
        $this->level                     = $data['level'];
        $this->description               = $data['description'];
        $this->twitter                   = $data['twitter'];
        $this->facebook                  = $data['facebook'];
        $this->instagram                 = $data['instagram'];
        $this->strava                    = $data['strava'];
        $this->rights                    = new Role($data['rights']);
    }

    private function getLngLat () {
        $getPointToText = $this->getPdo()->prepare("SELECT ST_AsText(point) FROM {$this->table} WHERE id = ?");
        $getPointToText->execute([$this->id]);
        $point_text = $getPointToText->fetch(PDO::FETCH_COLUMN);
        if ($point_text !== NULL) {
            $lngLat = new LngLat();
            $lngLat->fromWKT($point_text);
        } else $lngLat = null;
        return $lngLat;
    }

    // Register user into database
    public function register ($email, $login, $password) {
        $this->default_profilepicture_id = rand(1,9);
        $this->email = $email;
        $this->login = $login;
        // Insert data into database
        $register = $this->getPdo()->prepare('INSERT INTO users(slug, email, login, password, default_profilepicture_id, inscription_date, level) VALUES (FLOOR(RAND() * 1000000000), ?, ?, ?, ?, ?, ?)');
        $register->execute(array($email, $login, $password, rand(1,9), date('Y-m-d'), 1));
        // Get id
        $getId = $this->getPdo()->prepare("SELECT id FROM users WHERE email = ? AND login = ?");
        $getId->execute([$email, $login]);
        $this->id = $getId->fetch(PDO::FETCH_COLUMN);
        $this->populate();
    }

    /**
     * Update user data
     * @param String $index
     * @param Any $value
     */
    public function update ($index, $value) {
        $updateData = $this->getPdo()->prepare("UPDATE users SET {$index} = ? WHERE id = ?");
        $updateData->execute(array($value, $this->id));
        $this->populate();
    }

    /**
     * Send email verification mail
     * @param Boolean $redirect Whether to redirect user to the page where email has been sent or not
     */
    public function sendVerificationMail ($redirect = true) {
        
        // Get uri to redirect user to
        if ($redirect) {
            $uri_array = explode('/', $_SERVER['REQUEST_URI']);
            array_pop($uri_array);
            $redirection_uri = implode('/', $uri_array);
        } else $redirection_uri = '';

        // Send verification mail
        $email = new Mail();
        $email->setFrom(
            'contact@cyclingfriends.co',
            'CyclingFriends'
        );
        $email->setSubject('アカウントのメールアドレス確認');
        $email->addTo($this->email);
        $email->addContent(
            'text/html',
            '<p>' .$this->login. 'さん、CyclingFriendsへようこそ！</p>
            <p>アカウントの作成は終わりましたが、ログインするにはまだメールアドレスの確認を行う必要があります。</p>
            <p>下記のURLにアクセスして、ログインしてください！</p>
            <a href="' .$_SERVER['HTTP_ORIGIN']. $redirection_uri. '/account/verification/' .$this->slug. '-' .$this->email. '">メールアドレスの確認用URLはこちら</a>'
        );
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $response = $sendgrid->send($email);
    }

    /**
     * Check whether user has verified his email or not
     */
    public function isVerified () {
        if ($this->verified) return true;
        else return false;
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
		$_SESSION['rights']                    = $this->rights;
    }

    /**
     * Get corresponding id from email address
     * @param String $email
     * @return Int Corresponding user id
     */
    public function getId ($email) {
        $getId = $this->getPdo()->prepare("SELECT id FROM {$this->table} WHERE email = ?");
        $getId->execute([$email]);
        if ($getId->rowCount() > 0) return intval($getId->fetch(PDO::FETCH_COLUMN));
        else return false;
    }

    public function getSettings() {
        return new Settings($this->id);
    }

    public function updateSettings ($settings) {
        $checkIfSettingsExist = $this->getPdo()->prepare("SELECT id FROM settings WHERE id = ?");
        $checkIfSettingsExist->execute([$this->id]);
        // If settings data don't exist yet for this user, create it
        if ($checkIfSettingsExist->rowCount() == 0) {
            $insertSetting = $this->getPdo()->prepare("INSERT settings (id) VALUES (?)");
            $insertSetting->execute([$this->id]);
        }
        // Set values according to $settings content
        foreach ($settings as $key => $setting) {
            if ($setting == true) $value = 1;
            else $value = 0;
            $updateSetting = $this->getPdo()->prepare("UPDATE settings SET {$key} = :value WHERE id = :id");
            $updateSetting->execute([':value' => $value, ':id' => $this->id]);
        }
        return true;
    }

    public function checkIfLoginAlreadyExists ($login) {
        $checkIfUserAlreadyExists = $this->getPdo()->prepare('SELECT login FROM users WHERE login = ?');
        $checkIfUserAlreadyExists->execute(array($login));
        if ($checkIfUserAlreadyExists->rowCount() > 0) return true;
        else return false;
    }

    public function checkIfEmailAlreadyExists ($email) {
        $checkIfEmailAlreadyExists = $this->getPdo()->prepare('SELECT email FROM users WHERE email = ?');
        $checkIfEmailAlreadyExists->execute(array($email));
        if ($checkIfEmailAlreadyExists->rowCount() > 0) return true;
        else return false;
    }

    public function hasAdministratorRights () {
        if ($this->rights->rank >= 40) return true;
        else return false;
    }
    
    public function hasModeratorRights () {
        if ($this->rights->rank >= 30) return true;
        else return false;
    }
    
    public function hasEditorRights () {
        if ($this->rights->rank >= 20) return true;
        else return false;
    }

    /**
     * Check if user is registered as a guide in the cyclingfriends guide database
     * @return boolean
     */
    public function isGuide () {
        $isGuide = $this->getPdo()->prepare("SELECT id FROM user_guides WHERE id = ?");
        $isGuide->execute([$this->id]);
        if ($isGuide->rowCount() > 0) return true;
        else return false;
    }
    
    public function isPremium () {
        if ($this->rights->rank >= 0) return true; /// Set back to 10 when premium version release
        else return false;
    }

    // Function for checking password strength : at least 6 characters
    public function checkPasswordStrength ($password) {
        $number = preg_match('@[0-9]@', $password);
        if (strlen($password) < 8) return false;
        else return true;
    }

    public function getPassword () {
        $getUserInfos = $this->getPdo()->prepare('SELECT password FROM users WHERE id = ?');
        $getUserInfos->execute(array($this->id));
        return $password = $getUserInfos->fetch(PDO::FETCH_NUM)[0];
    }
    
    public function getInscriptionDate ($user) {
        $getUserInfos = $this->getPdo()->prepare('SELECT * FROM users WHERE id = ?');
        $getUserInfos->execute(array($user));
        return $user_infos = $getUserInfos->fetch();
    }

    public function getLevelString () {
        switch ($this->level) {
            case 1: return '初心者';
            case 2: return '中級者';
            case 3: return '上級者';
        }
    }

    // Function calculating an age from birthdate
    public function calculateAge () {
        if (isset($this->birthdate)) {
            $today = date("Y-m-d");
            $diff = date_diff(date_create($this->birthdate), date_create($today));
            return $diff->format('%y');
        } else return '-';
    }

    // Update user location in database
    public function setLocation ($geolocation, $lngLat) {
        $setLocation = $this->getPdo()->prepare('UPDATE users SET city = ?, prefecture = ?, lng = ?, lat = ? WHERE id = ?');
        $setLocation->execute([$geolocation->city, $geolocation->prefecture, $lngLat->lng, $lngLat->lat, $this->id]);
        return true;
    }

    public function getGenderString () {
        switch ($this->gender) {
            case 'Man': return '男';
            case 'Woman': return '女';
            default: return '特定無し';
        }
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
            if ($friendship['accepted']) return array('error' => $friend->login. "が既に友達リストに入っています。");
            // If accepted is set to false and current user is the inviter
            else if ($friendship['inviter_id'] == $_SESSION['id']) return array('error' => $friend->login. "には既に友達申請を送っています。");
            // else (If accepted is set to false and current user is the receiver)
            else return array('error' => $friend->login. 'は既に友達申請をあなた宛てに出しています。<a href="/riders/friends.php">友達ページ</a>から承認、あるいは却下することができます。');
            
        // If there is no existing entry, insert a new friendship relation (before validation) in friends table, and return true and a success message
        } else {
            $createNewFriendship = $this->getPdo()->prepare('INSERT INTO friends(inviter_id, receiver_id, invitation_date) VALUES (?, ?, ?)');
            $createNewFriendship->execute(array($this->id, $friend->id, date('Y-m-d')));
            $this->notify($friend->id, 'friends_request');
            return array('success' => $friend->login. "に友達申請を送りました !");
        }
    }

    // Set a friend request to accepted
    public function acceptFriendRequest ($friend) {

        // Set friendship status to "accepted"
        $registerAsFriend = $this->getPdo()->prepare('UPDATE friends SET accepted = 1, approval_date = :approval_date WHERE (inviter_id = :inviter AND receiver_id = :receiver) OR (inviter_id = :receiver AND receiver_id = :inviter) AND accepted = 0');
        $registerAsFriend->execute([":approval_date" => date('Y-m-d'), ":inviter" => $this->id, ":receiver" => $friend->id]);
        if ($registerAsFriend->rowCount() > 0) {

            // Set follower relation
            $this->follow($friend);
            $friend->follow($this);
            
            $this->notify($friend->id, 'friends_approval');

            // Return message
            return array('success' => $friend->login .'が友達リストに追加されました !');
        }

        // If already friends, return message
        else return array('error' => $friend->login. 'とは既に友達になっています。');
    }

    // Remove a friendship relation
    public function removeFriend ($friend) {
		$removeFriends = $this->getPdo()->prepare('DELETE FROM friends WHERE CASE WHEN inviter_id = :user_id THEN receiver_id = :friend WHEN receiver_id = :user_id THEN inviter_id = :friend END');
		$removeFriends->execute([":user_id" => $this->id, ":friend" => $friend->id]);
        if ($removeFriends->rowCount() > 0) return array('success' =>  $friend->login .'が友達リストから削除されました。');
        else return array('error' => $friend->login. 'とは友達になっていません。');
    }

    public function getFriends () {
        $getFriends = $this->getPdo()->prepare('SELECT CASE WHEN inviter_id = :user_id THEN receiver_id WHEN receiver_id = :user_id THEN inviter_id END FROM friends WHERE (inviter_id = :user_id OR receiver_id = :user_id) AND accepted = 1');
        $getFriends->execute(array(":user_id" => $this->id));
        return $getFriends->fetchAll(PDO::FETCH_COLUMN);
    }

    public function isFriend ($friend) {
        require_once User::$root_folder .'/includes/functions.php';
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
        $getApprovalDate = $this->getPdo()->prepare('SELECT approval_date FROM friends WHERE CASE WHEN inviter_id = :user_id THEN receiver_id = :friend_id WHEN receiver_id = :user_id THEN inviter_id = :friend_id END');
        $getApprovalDate->execute(array(":user_id" => $this->id, ":friend_id" => $friend_id));
        $approval_date = $getApprovalDate->fetch(PDO::FETCH_COLUMN);
        return $approval_date;
    }

    // Insert a new entry in followers table
    public function follow ($user) {
        $follow = $this->getPdo()->prepare('INSERT INTO followers (following_id, followed_id, following_date) VALUES (?, ?, ?)');
        $follow->execute(array($this->id, $user->id, date("Y-m-d H:i:s")));
        if ($follow->rowCount() > 0) {
            if (!$this->isFriend($user)) $this->notify($user->id, 'follow');
            return array('success' => $user->login . 'をフォローしました !');
        }
        else return array('error' => $user->login . 'を既にフォローしています。');
    }

    // Removes an entry in followers table
    public function unfollow ($user) {
        $unfollow = $this->getPdo()->prepare('DELETE FROM followers WHERE following_id = ? AND followed_id = ?');
        $unfollow->execute(array($this->id, $user->id));
        if ($unfollow->rowCount() > 0) return array('success' => $user->login . 'のフォローを取りやめました。');
        else return array('error' => $user->login . 'をフォローしていません。');
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
        return $getFollowedList->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getScoutsList () {
        $getScoutsList = $this->getPdo()->prepare('SELECT followed_id FROM followers WHERE following_id = ?');
        $getScoutsList->execute(array($this->id));
        return $getScoutsList->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getFriendsAndScoutsList () {
        $getFriendsAndScoutsList = $this->getPdo()->prepare('SELECT followed_id FROM followers WHERE following_id = :user_id UNION SELECT CASE WHEN inviter_id = :user_id THEN receiver_id WHEN receiver_id = :user_id THEN inviter_id END FROM friends WHERE (inviter_id = :user_id OR receiver_id = :user_id) AND accepted = 1');
        $getFriendsAndScoutsList->execute(array(':user_id' => $this->id));
        return $getFriendsAndScoutsList->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getNotifications ($offset = 0, $limit = 10) {
        $getNotifications = $this->getPdo()->prepare("SELECT id FROM notifications WHERE user_id = ? ORDER BY checked ASC, datetime DESC LIMIT {$offset}, {$limit}");
        $getNotifications->execute([$this->id]);
        $notifications = [];
        while ($id = $getNotifications->fetch(PDO::FETCH_COLUMN)) array_push($notifications, new Notification($id));
        return $notifications;
    }

    public function getDistance ($user) {
        if ($this->lngLat != null && $user->lngLat != null) {
            $user_location = new Coordinate($user->lngLat->lat, $user->lngLat->lng);
            $this_location = new Coordinate($this->lngLat->lat, $this->lngLat->lng);
            $calculator = new Vincenty();
            return round($calculator->getDistance($user_location, $this_location) / 1000, 1);
        } else return false;
    }

    // Function for uploading a profile picture
    function uploadPropic () {

        require_once User::$root_folder .'/includes/functions.php';
        
        // Declaration of variables
        $img_blob   = '';
        $img_size   = 0;
        $max_size   = 5000000;
        $img_name   = '';
        $img_type   = '';
            
        // Displays an error message if any problem through upload
        if (!is_uploaded_file($_FILES['propicfile']['tmp_name'])) {
            return array('error' => "ファイルアップロード中に問題が発生しました。");
                
        } else {
            
            $temp_image = new TempImage($_FILES['propicfile']['name']);
                
            // Displays an error message if file size exceeds $max_size
            $img_size = $_FILES['propicfile']['size'];
            if ($img_size > $max_size) return array('error' => 'アップロードしたファイルがサイズ制限（5Mb）を超えています。サイズを縮小して再度試してください。');

            // Store temp file as *jpg or *png
            $result = $temp_image->convert($_FILES['propicfile']['tmp_name']);
            if (!$result) return array('error' => 'アップロードしたファイル形式は対応しておりません。対応可能なファイル形式：' .implode(', ', $temp_image->accepted_formats));

            // Sort upload data into variables
            $img_type = $_FILES['propicfile']['type'];
            $img_name = $temp_image->name;
            $img_blob = $temp_image->treatFile($temp_image->temp_path);
            $filename = setFilename('img');
            $metadata = [
                'user_id' => $this->id,
                'datetime' => date('Y-m-d H:i:s')
            ];

            require User::$root_folder . '/actions/blobStorageAction.php';
            $blobClient->createBlockBlob($this->container_name, $filename, $img_blob);
            $blobClient->setBlobMetadata($this->container_name, $filename, $metadata);

            // Check if connected user has already uploaded a picture
            $checkUserId = $this->getPdo()->prepare('SELECT user_id FROM profile_pictures WHERE user_id = ?');
            $checkUserId->execute(array($this->id));

            // If he does, update data in the database
            if ($checkUserId->rowCount() > 0) {
                $updateImage = $this->getPdo()->prepare('UPDATE profile_pictures SET filename = ?, size = ?, name = ?, type = ? WHERE user_id = ?');
                $updateImage->execute(array($filename, $img_size, $img_name, $img_type, $this->id));

            // If he doesn't, insert a new line into the database
            } else {
                $insertImage = $this->getPdo()->prepare('INSERT INTO profile_pictures (user_id, filename, size, name, type) VALUES (?, ?, ?, ?, ?)');
                $insertImage->execute(array($this->id, $filename, $img_size, $img_name, $img_type));
            }

            return array('success' => 'プロフィール画像が更新されました !');
        }
    }

    public function getDefaultPropicId () {
        $getDefaultPropicId = $this->getPdo()->prepare('SELECT default_profilepicture_id FROM users WHERE id = ?');
        $getDefaultPropicId->execute(array($this->id));
        return $getDefaultPropicId->fetch(PDO::FETCH_COLUMN);
    }

    // Function for downloading users's profile picture
    public function getPropicUrl () {

        // Check if there is an image that corresponds to connected user in the database
        $checkUserId = $this->getPdo()->prepare('SELECT user_id FROM profile_pictures WHERE user_id = ?');
        $checkUserId->execute(array($this->id));
        $checkUserId->fetch();

        // If there is one, get filename and return file url
        if ($checkUserId->rowCount() > 0) {	
            $getImage = $this->getPdo()->prepare('SELECT filename FROM profile_pictures WHERE user_id = ?');
            $getImage->execute(array($this->id));
            $filename = $getImage->fetch(PDO::FETCH_COLUMN);

            // Connect to blob storage
            require User::$root_folder . '/actions/blobStorageAction.php';

            // Retrieve blob url
            return $blobClient->getBlobUrl($this->container_name, $filename);

        // If there is not, return default propic url
        } else return '\media\default-profile-' .$this->getDefaultPropicId(). '.jpg';
    }

    // Function for getting user's profile picture element with defined height, width and border-radius attributes
    public function getPropicElement ($height = 60, $width = 60, $border_radius = 60) { ?>
        <div style="height: <?= $height ?>px; width: <?= $width ?>px;" class="free-propic-container">
            <img style="border-radius: <?= $border_radius ?>px;" class="free-propic-img" src="<?= $this->getPropicUrl() ?>" />
        </div> <?php
    }

    // Get user profile picture element inside a string
    public function getPropicHTML ($height = 60, $width = 60, $border_radius = 60) {
        return '<div style="height: ' .$height. 'px; width: ' .$width. 'px;" class="free-propic-container"><img style="border-radius: ' .$border_radius. 'px;" class="free-propic-img" src="'. $this->getPropicUrl() .'" /></div>';
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
                    $user_bike = new Bike();
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

    /**
     *  Returns percentage of setable user info set
     */
    public function userInfoQuantitySet () {
        $user_info = [$this->first_name, $this->last_name, $this->gender, $this->birthdate, $this->place, $this->lngLat, $this->level, $this->description, $this->location->prefecture];
        $set_info = [];
        foreach ($user_info as $item) if ($item != null) array_push($set_info, $item);
        return count($set_info) * 100 / count($user_info);
    }

    public function getRides ($offset = 0, $limit = 20) {
        $getRides = $this->getPdo()->prepare("SELECT id FROM rides WHERE author_id = ? ORDER BY posting_date DESC LIMIT " .$offset. ", " .$limit);
	    $getRides->execute(array($this->id));
        return $getRides->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRidesNumber () {
        $getRides = $this->getPdo()->prepare('SELECT id FROM rides WHERE author_id = ?');
	    $getRides->execute(array($this->id));
        return $getRides->rowCount();
    }

    public function getRideParticipations ($offset = 0, $limit = 20) {
        $getRides = $this->getPdo()->prepare("SELECT ride_id FROM ride_participants WHERE user_id = ? ORDER BY entry_date DESC LIMIT " .$offset. ", " .$limit);
	    $getRides->execute(array($this->id));
        return $getRides->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRideParticipationsNumber () {
        $getRides = $this->getPdo()->prepare("SELECT ride_id FROM ride_participants WHERE user_id = ?");
	    $getRides->execute(array($this->id));
        return $getRides->rowCount();
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
        $getActivities = $this->getPdo()->prepare("SELECT a.id FROM activities AS a JOIN routes AS r ON a.route_id = r.id WHERE a.user_id = ? ORDER BY r.posting_date DESC LIMIT " .$offset. ", " .$limit);
	    $getActivities->execute(array($this->id));
        return $getActivities->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicActivities ($offset = 0, $limit = 20) {

        // Get period of rides to display
        $friends_list = $this->getFriends();
        $scout_list = $this->getScoutsList();
        $scout_number = count($this->getFriendsAndScoutsList());
        if ($scout_number < 3) $period = 999;
        else if ($scout_number < 8) $period = 28;
        else if ($scout_number < 18) $period = 20;
        else if ($scout_number < 25) $period = 14;
        else $period = 10;

        // Request activities
        $getActivities = $this->getPdo()->prepare(
            "SELECT
            id, user_id, datetime, posting_date, title, privacy
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
                        user_id IN ('".implode("','",$scout_list)."')
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
            $getFurtherActivities = $this->getPdo()->prepare("SELECT id, user_id, datetime, posting_date, title, privacy FROM activities WHERE datetime > DATE_SUB(CURRENT_DATE, INTERVAL ? DAY) AND privacy = 'public' ORDER BY likes, datetime, posting_date DESC LIMIT " .$offset. ", " .($limit - $results_number));
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
            $getFurtherActivities2 = $this->getPdo()->prepare("SELECT id, user_id, datetime, posting_date, title, privacy FROM activities WHERE privacy = 'public' ORDER BY likes, datetime, posting_date DESC LIMIT " .$offset. ", " .($limit - count($activities)));
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
                $last_messages[$i]->friend->propic = $last_messages[$i]->friend->getPropicUrl();
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
        $addMessage = $this->getPdo()->prepare('INSERT INTO messages (sender_id, receiver_id, message, time) VALUES (?, ?, ?, ?)');
        $addMessage->execute(array($this->id, $receiver->id, $message, date('Y-m-d H:i:s')));
    }

    public function getFavorites ($type, $offset = 0, $limit = 9999) {
        $getFavorites = $this->getPdo()->prepare("SELECT object_id FROM favorites WHERE user_id = ? AND object_type = ? LIMIT " .$offset. ", " .$limit);
        $getFavorites->execute(array($this->id, $type));
        $favorites_data = $getFavorites->fetchAll(PDO::FETCH_ASSOC);
        $favorites = [];
        foreach ($favorites_data as $favorite_data) {
            if ($type == 'scenery') array_push($favorites, new Scenery($favorite_data['object_id']));
            if ($type == 'segment') array_push($favorites, new Segment($favorite_data['object_id']));
        }
        return $favorites;
    }

    public function getFavoritesNumber ($type) {
        $countFavorites = $this->getPdo()->prepare("SELECT id FROM favorites WHERE user_id = ? AND object_type = ?");
        $countFavorites->execute(array($this->id, $type));
        return $countFavorites->rowCount();
    }

    public function getPublicSceneries ($offset = 0, $limit = 20) {
        // Get period of sceneries to display
        $friends_and_scout_list = $this->getFriendsAndScoutsList();
        $friends_and_scout_number = count($friends_and_scout_list);
        if ($friends_and_scout_number < 3) $period = 999;
        else if ($friends_and_scout_number < 8) $period = 60;
        else if ($friends_and_scout_number < 18) $period = 28;
        else if ($friends_and_scout_number < 25) $period = 21;
        else $period = 14;
        // Request sceneries
        if ($friends_and_scout_number == 0) $getSceneries = $this->getPdo()->prepare("SELECT id, publication_date FROM sceneries WHERE publication_date > DATE_SUB(CURRENT_DATE, INTERVAL {$period} DAY)");
        else $getSceneries = $this->getPdo()->prepare("SELECT id, publication_date FROM sceneries WHERE publication_date > DATE_SUB(CURRENT_DATE, INTERVAL {$period} DAY) AND user_id IN ('".implode("','",$friends_and_scout_list)."','".$this->id."') ORDER BY publication_date DESC LIMIT " .$offset. ", " .$limit);
        $getSceneries->execute();
        if ($getSceneries->rowCount() > 2) return $getSceneries->fetchAll(PDO::FETCH_ASSOC);
        else { // If this request has returned less than 3 results, return scenery spots shared in the last 14 days
            $getOtherSceneries = $this->getPdo()->prepare("SELECT id, publication_date FROM sceneries WHERE publication_date > DATE_SUB(CURRENT_DATE, INTERVAL 14 DAY)");
            $getOtherSceneries->execute();
            return $getOtherSceneries->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function getThread ($offset = 0, $limit = null) {
        $activities_data = $this->getPublicActivities();
        $scenery_data = $this->getPublicSceneries();
        // Get thread data skeleton
        $thread_data = [];
        foreach ($activities_data as $data) {
            $data['type'] = 'activity';
            array_push($thread_data, $data);
        }
        foreach ($scenery_data as $data) {
            $data['type'] = 'scenery';
            array_push($thread_data, $data);
        }
        // Sort thread data by date
        function sort_by_date ($a, $b) {
            if (isset($a['publication_date'])) $a_date = $a['publication_date'];
            else if (isset($a['datetime'])) $a_date = $a['datetime'];
            if (isset($b['publication_date'])) $b_date = $b['publication_date'];
            else if (isset($b['datetime'])) $b_date = $b['datetime'];
            return $b_date <=> $a_date;
        }
        usort($thread_data, 'sort_by_date');
        return array_slice($thread_data, $offset, $limit);
    }

    /**
     * Check if user has allowed real name publication
     */
    public function isRealNamePublic () {
        if ($this->getSettings()->hide_realname) return false;
        else return true;
    }

}