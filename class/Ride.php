<?php

use \SendGrid\Mail\Mail;

class Ride extends Model {
    
    protected $table = 'rides';

    public $name;
    public $date;
    public $meeting_time;
    public $departure_time;
    public $finish_time;
    public $nb_riders_min;
    public $nb_riders_max;
    public $level_beginner;
    public $level_intermediate;
    public $level_athlete;
    public $citybike;
    public $roadbike;
    public $mountainbike;
    public $gravelcxbike;
    public $description;
    public $meeting_place;
    public $distance_about;
    public $distance;
    public $finish_place;
    public $terrain;
    public $course_description;
    public $posting_date;
    public $author_id;
    public $privacy;
    public $entry_start;
    public $entry_end;
    public $participants_number;
    public $status;
    public $substatus;
    public $lngLatFormat;
    
    function __construct($id = NULL, $lngLatFormat = true) {
        parent::__construct();
        $this->id                                      = $id;
        $data = $this->getData($this->table);
        $this->name                                    = $data['name'];
        $this->date                                    = $data['date'];
        $this->meeting_time                            = $data['meeting_time'];
        $this->departure_time                          = $data['departure_time'];
        $this->finish_time                             = $data['finish_time'];
        $this->nb_riders_min                           = $data['nb_riders_min'];
        $this->nb_riders_max                           = $data['nb_riders_max'];
        $this->level_beginner                          = $data['level_beginner'];
        $this->level_intermediate                      = $data['level_intermediate'];
        $this->level_athlete                           = $data['level_athlete'];
        $this->citybike                                = $data['citybike'];
        $this->roadbike                                = $data['roadbike'];
        $this->mountainbike                            = $data['mountainbike'];
        $this->gravelcxbike                            = $data['gravelcxbike'];
        $this->description                             = $data['description'];
        $this->meeting_place                           = $data['meeting_place'];
        $this->distance_about                          = $data['distance_about'];
        $this->distance                                = $data['distance'];
        $this->finish_place                            = $data['finish_place'];
        $this->terrain                                 = $data['terrain'];
        $this->course_description                      = $data['course_description'];
        $this->posting_date                            = $data['posting_date'];
        $this->author_id                               = $data['author_id'];
        $this->privacy                                 = $data['privacy'];
        $this->entry_start                             = $data['entry_start'];
        $this->entry_end                               = $data['entry_end'];
        $this->participants_number                     = count($this->getParticipants());
        if (isset($data['route_id'])) $this->route_id  = $data['route_id'];
        $this->lngLatFormat                            = $lngLatFormat;
        $this->status                                  = $this->getStatus()['status'];
        $this->substatus                               = $this->getStatus()['substatus'];
    }

    public function getAuthor () {
        return new User($this->author_id);
    }

    public function getRoute () {
        if (isset($this->route_id)) return new Route ($this->route_id, $this->lngLatFormat);
        else return false;
    }

    public function hasFeaturedImage () {
        $getFeaturedImage = $this->getPdo()->prepare('SELECT id FROM ride_checkpoints WHERE ride_id = ? AND featured = true AND filename IS NOT NULL');
        $getFeaturedImage->execute(array($this->id));
        if ($getFeaturedImage->rowCount() > 0) return true;
        else return false;
    }

    public function getFeaturedImage () {
        // Select image if exists for checkpoint set as featured
        $getFeaturedImage = $this->getPdo()->prepare('SELECT id FROM ride_checkpoints WHERE ride_id = ? AND featured = true AND filename IS NOT NULL');
        $getFeaturedImage->execute(array($this->id));
        if ($getFeaturedImage->rowCount() > 0) {
            $checkpoint_image_id = $getFeaturedImage->fetch(PDO::FETCH_COLUMN);
            return new CheckpointImage($checkpoint_image_id);
        // Else, select first checkpoint having an image set
        } else {
            $getFeaturedImage = $this->getPdo()->prepare('SELECT id FROM ride_checkpoints WHERE ride_id = ? AND filename IS NOT NULL');
            $getFeaturedImage->execute(array($this->id));
            if ($getFeaturedImage->rowCount() > 0) {
                $checkpoint_image_id = $getFeaturedImage->fetch(PDO::FETCH_COLUMN);
                return new CheckpointImage($checkpoint_image_id);
            // If still doesn't exist, return default image
            } else return '\media\default-photo-' . rand(0, 9) . '.svg';
        }
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
            // If level is accepted, then write it
            if ($boolean == true) {
                $string .= '<span class="tag-' .$this->colorLevel($level+1). '">' .getLevelFromKey($level+1). '</span>';
            }
        }
        return $string;
    }

    // Get accepted level list of a specific ride from the database
    public function getAcceptedLevelString () {
        $level_list = $this->getAcceptedLevels();
        // If all levels are true, return Anyone
        if ($level_list[0] && $level_list[1] && $level_list[2]) return '誰でも可';
        else {
            // Set variables to default value
            $i = 0;	$string = '';
            // Build the list string
            foreach ($level_list as $level => $boolean) {
                // If level is accepted, then write it
                if ($boolean == true) {
                    // Insert commas between level
                    if ($i > 0) $string .= '、';
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

        if ($accepted_bikes[0] && $accepted_bikes[1] && $accepted_bikes[2] && $accepted_bikes[3]) return '車種問わず';
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
                        if ($i > 0) $string .= '、';
                        $string .= getBikesFromColumnName($bike);
                        $i++;
                    }
                }
            }
            return $string;
        }
    }

    public function getTerrainIcon () {
        switch ($this->terrain) {
            case 1: return '<img class="terrain-icon" src="\media\flat.svg" />';
            case 2: return '<img class="terrain-icon" src="\media\smallhills.svg" />';
            case 3: return '<img class="terrain-icon" src="\media\hills.svg" />';
            case 4: return '<img class="terrain-icon" src="\media\mountain.svg" />';
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
                    $bike = new Bike($bikes[$i]);
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

    /**
     * Add a participant
     * @param User $participant
     */
    public function join ($participant) {
        // Add a line into participation database
        $joinRide = $this->getPdo()->prepare('INSERT INTO ride_participants(user_id, ride_id, entry_date) VALUES (?, ?, ?)');
        $joinRide->execute(array($participant->id, $this->id, (new DateTime('now'))->setTimezone(new DateTimeZone('Asia/Tokyo'))->format('Y-m-d H:i:s')));

        // Prepare additional fields data
        $additional_fields = $this->getAdditionalFields();
        $additional_fields_li = '';
        foreach ($additional_fields as $additional_field) {
            if ($additional_field->getAnswer($participant->id)) $additional_fields_li .= $additional_field->question. '：' .$additional_field->getAnswer($participant->id)->content. '<br>';
        }

        // Get origin
        if (array_key_exists('HTTP_ORIGIN', $_SERVER)) $origin = $_SERVER['HTTP_ORIGIN'];
        else $origin = parse_url($_SERVER['HTTP_REFERER'])['scheme'] . '://' . parse_url($_SERVER['HTTP_REFERER'])['host'];

        // Send confirmation email
        $email = new Mail();
        $email->setFrom(
            'contact@cyclingfriends.co',
            'CyclingFriends'
        );
        $email->setSubject($this->date. ' ' .$this->name. '【エントリー情報】');
        $email->addTo($participant->email);
        $email->addContent(
            'text/html',
            '<p>この度、' .$this->name. 'にエントリーを頂き、ありがとうございます！</p>
            <p>エントリー情報及びツアー情報は、下記の通りご確認頂けます。</p>
            <p>【ツアー情報】</p>
            <p><a href="' .$origin. '/ride/' .$this->id. '">ツアー情報はこちら</a></p><br>
            <p>【エントリー情報】</p>
            姓名：' .$participant->last_name. ' ' .$participant->first_name. '<br>
            性別：' .$participant->getGenderString(). '<br>
            生年月日：' .$participant->birthdate. '<br>'
                .$additional_fields_li.
            '<br><p>現在エントリーしているツアーの情報は<a href="' .$origin. '/ride/participations">こちら</a>からご確認頂けます。</p>'
        );
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $response = $sendgrid->send($email);

        // Send mail to guides and admin
        $getAdmins = $this->getPdo()->prepare("SELECT id FROM users WHERE rights = 'administrator'");
        $getAdmins->execute();
        $admin_ids = $getAdmins->fetchAll(PDO::FETCH_COLUMN);
        $to_email = [];
        foreach ($this->getGuides() as $guide) array_push($to_email, new User($guide->id));
        foreach ($admin_ids as $id) if (!in_array(new User($id), $to_email)) array_push($to_email, new User($id));
        foreach ($to_email as $user) $this->mail($user, $this->date. ' ' .$this->name. '【新規エントリー】',
            '<p>' .$this->date. ' 開催予定の「' .$this->name. '」に下記の通り新規エントリーがありましたので、お知らせします。</p>
            <p>これで合計エントリー人数が' .$this->participants_number. '名になりました。</p>
            <p>---</p>
            <p>【エントリー情報】</p>
            ユーザーネーム：' .$participant->login. '<br>
            メールアドレス：' .$participant->email. '<br>
            姓名：' .$participant->last_name. ' ' .$participant->first_name. '<br>
            性別：' .$participant->getGenderString(). '<br>
            生年月日：' .$participant->birthdate. '<br>'
                .$additional_fields_li. '
            <p>---</p>
            <p><a href="' .$origin. '/ride/' .$this->id. '">ツアー情報はこちら</a></p><br>'
        );

        // Set notification
        $this->notify($this->author_id, 'ride_join', $participant->id);
    }

    /**
     * Remove a participant
     * @param User $participant
     */
    public function quit ($participant) {

        // Remove participation data
		$quitRide = $this->getPdo()->prepare('DELETE FROM ride_participants WHERE user_id = ? AND ride_id = ?');
		$quitRide->execute(array($_SESSION['id'], $this->id));

        // Get origin
        if (array_key_exists('HTTP_ORIGIN', $_SERVER)) $origin = $_SERVER['HTTP_ORIGIN'];
        else $origin = parse_url($_SERVER['HTTP_REFERER'])['scheme'] . '://' . parse_url($_SERVER['HTTP_REFERER'])['host'];

        // Send confirmation email
        $email = new Mail();
        $email->setFrom(
            'contact@cyclingfriends.co',
            'CyclingFriends'
        );
        $email->setSubject($this->date. ' ' .$this->name. '【キャンセル】');
        $email->addTo($participant->email);
        $email->addContent(
            'text/html',
            '<p><a href="' .$origin. '/ride/' .$this->id. '">' .$this->name. '</a>へのエントリーが取り消されました。</p>
            <p>現在エントリーしているツアーの情報は<a href="' .$origin. '/ride/participations">こちら</a>からご確認頂けます。</p>'
        );
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $response = $sendgrid->send($email);

        // Send mail to guides and admin
        $getAdmins = $this->getPdo()->prepare("SELECT id FROM users WHERE rights = 'administrator'");
        $getAdmins->execute();
        $admin_ids = $getAdmins->fetchAll(PDO::FETCH_COLUMN);
        $to_email = [];
        foreach ($this->getGuides() as $guide) array_push($to_email, new User($guide->id));
        foreach ($admin_ids as $id) if (!in_array(new User($id), $to_email)) array_push($to_email, new User($id));
        foreach ($to_email as $user) $this->mail($user, $this->date. ' ' .$this->name. '【キャンセル】',
            '<p>' .$participant->last_name. ' ' .$participant->first_name. 'さんが ' .$this->date. ' 開催予定の「' .$this->name. '」への参加を取り下げました。</p>
            <p>これで合計エントリー人数が' .$this->participants_number. '名になりました。</p>
            <p>---</p>
            <p>【キャンセル情報】</p>
            ユーザーネーム：' .$participant->login. '<br>
            メールアドレス：' .$participant->email. '<br>
            姓名：' .$participant->last_name. ' ' .$participant->first_name. '<br>
            性別：' .$participant->getGenderString(). '<br>
            生年月日：' .$participant->birthdate. '<br>'
                .$additional_fields_li. '
            <p>---</p>
            <p><a href="' .$origin. '/ride/' .$this->id. '">ツアー情報はこちら</a></p><br>'
        );

        // Set notification
        $this->notify($this->author_id, 'ride_quit', $participant->id);
    }

    public function isOpen () {
        $current_date = new DateTime('now', new DateTimezone('Asia/Tokyo'));
        if ($current_date->format('Y-m-d') < $this->entry_start) return 'not yet';
        else if ($current_date->format('Y-m-d') > $this->entry_end) return 'closed';
        else if ($current_date->format('Y-m-d') >= $this->entry_start AND $current_date->format('Y-m-d') <= $this->entry_end) return 'open';
        else return false;
    }

    public function isParticipating ($user) {
        
        // Check if the user has already joined the ride
        $checkIfParticipate = $this->getPdo()->prepare('SELECT ride_id FROM ride_participants WHERE user_id = ? AND ride_id = ?');
        $checkIfParticipate->execute(array($user->id, $this->id));
    
        if ($checkIfParticipate->rowCount() > 0) return true;
        else return false;
    }

    // Get an array with participants list
    public function getParticipants () {
        $getParticipants = $this->getPdo()->prepare('SELECT user_id FROM ride_participants WHERE ride_id = ?');
        $getParticipants->execute(array($this->id));
        if ($getParticipants->rowCount() > 0) return $getParticipants->fetchAll(PDO::FETCH_COLUMN);
        else return [];
    }

    // Check if a ride is full or not
    public function isFull () {
        
        // Get current number of participants
        $participants = $this->getParticipants();
        if (!empty($participants)) $current_nb = count($participants);
        else $current_nb = 0;
        
        // Get maximum number of participants
        
        if ($current_nb >= $this->nb_riders_max) return true;
        else if ($current_nb < $this->nb_riders_max) return false;
    }

    // Check if all participants to a ride are in friends list of an user
    public function isEveryParticipantInFriendsList ($user) {
        $friends = $user->getFriends();
        $participants = $this->getParticipants();
        if ($participants) {
            $participating_friends = array_intersect($friends, $participants);
            $participants_not_friends = array_diff($participants, $participating_friends);
            if (count($participants_not_friends) == 0) return true;
            else return false;
        } else return true;
    }

    private function getStatus () {
        $substatus = NULL; // Set substatus to NULL for preventing errors in case of no substatus set
        $current_date = new DateTime('now', new DateTimezone('Asia/Tokyo'));
        
        // If ride date is passed
        if ($this->date < $current_date->format('Y-m-d')) {
            $status = 'ツアー終了'; } // status is Finished
        
        // If ride is full
        else if ($this->isFull()) {
            $status = '定員達成'; } // status is Full
            
        // If privacy is set as private
        else if ($this->privacy == 'private') {
            $status = '非公開'; } // status is Private
            
        // If not set as Finished, Full or Private
        else {
            
            // If not set as private, ride date is yet to come and entry start date is yet to come
            if (($this->privacy != 'private') AND ($this->date > $current_date->format('Y-m-d')) AND ($this->entry_start > $current_date->format('Y-m-d'))) {
                $status = '募集期間外'; // status is Closed
                $substatus = 'まもなく開始'; // substatus is opening soon
            }
    
            // If not set as private, ride date is yet to come and entries are open
            else if (($this->privacy != 'private') AND ($this->date > $current_date->format('Y-m-d')) AND ($this->entry_start <= $current_date->format('Y-m-d') AND $this->entry_end >= $current_date->format('Y-m-d'))) {
                // If number of applicants is lower than minimum number set
                $participants_number = $this->setParticipationInfos()['participants_number'];
                if ($participants_number < $this->nb_riders_min) {
                    $status = '募集中'; // status is Open 
                    $substatus = '最低催行人数に達成していません'; // substatus is riders wanted
                } else { // If minimum number is reached
                    $status = '募集中'; // status is Open
                    $substatus = '最低催行人数に達成しました'; //substatus is ready to depart
                }
            }
    
            // If not set as private, ride date is yet to come but entries are closed
            else if (($this->privacy != 'private') AND ($this->date >= $current_date->format('Y-m-d')) AND ($this->entry_start < $current_date->format('Y-m-d') AND $this->entry_end < $current_date->format('Y-m-d'))) {
                $participants_number = $this->setParticipationInfos()['participants_number'];
                if ($participants_number < $this->nb_riders_min) {
                    $status = '中止';
                    $substatus = '最低催行人数に達成しませんでした';
                } else { // If minimum number is reached
                    $status = 'エントリー終了';
                    $substatus = 'まもなく開催'; 
                }
            }
    
            else {
                $status = 'no status';
            }
            
        }
        
        return array('status' => $status, 'substatus' => $substatus);
    }

    public function getStatusClass () {
        switch ($this->status)
        {
            case '非公開' : // red
                return 'rd-status-red';
                break;
            case 'エントリー終了' : // blue
                return 'rd-status-blue';
                break;
            case '募集中' : // green
                return 'rd-status-green';
                break;
            case '定員達成' : // blue
                return 'rd-status-blue';
                break;
            case '終了' : // red
                return 'rd-status-red';
                break;
            case '中止' : // red
                return 'rd-status-red';
                break;
            default :
                return 'rd-status-black';
        }
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
        
        else $participation_color = 'black';

        return array('participants_number' => $participants_number, 'participation_color' => $participation_color);
    }

    public function exists ($id) {
        $checkIfExists = $this->getPdo()->prepare('SELECT id FROM rides WHERE id = ?');
        $checkIfExists->execute([$id]);
        if ($checkIfExists->rowCount() > 0) return true;
        else return false;
    }

    public function delete () {      
        $deleteCheckpoints = $this->getPdo()->prepare('DELETE FROM ride_checkpoints WHERE ride_id = ?');
        $deleteCheckpoints->execute(array($this->id));
        $deleteChat = $this->getPdo()->prepare('DELETE FROM ride_chat WHERE ride_id = ?');
        $deleteChat->execute(array($this->id));
        $deleteParticipation = $this->getPdo()->prepare('DELETE FROM ride_participants WHERE ride_id = ?');
        $deleteParticipation->execute(array($this->id));
        $deleteNotifications = $this->getPdo()->prepare("DELETE FROM notifications WHERE entry_table = {$this->table} AND entry_id = ?");
        $deleteNotifications->execute(array($this->id));
        $deleteRide = $this->getPdo()->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $deleteRide->execute(array($this->id));
        return true;
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
        $checkpoints = $this->getCheckpoints();
        if ($checkpoints[0]->lngLat->lng === $checkpoints[count($checkpoints)-1]->lngLat->lng) {
            return true;
        } else return false;
    }

    public function getChat () {
        $getChat = $this->getPdo()->prepare('SELECT * FROM ride_chat WHERE ride_id = ?');
        $getChat->execute(array($this->id));
        return $getChat->fetchAll(PDO::FETCH_ASSOC);
    }

    public function postMessage ($content) {
        $user = new User($_SESSION['id']);
        // Send variables into database
		$insertChatMessage = $this->getPdo()->prepare('INSERT INTO ride_chat(ride_id, author_id, user_login, message, time) VALUES (?, ?, ?, ?, ?)');
		$insertChatMessage->execute(array($this->id, $user->id, $user->login, $content, (new DateTime('now'))->setTimezone(new DateTimeZone('Asia/Tokyo'))->format('Y-m-d H:i:s')));
        $this->notify($this->author_id, 'ride_message_post', $user->id);
        $this->mail($this->getAuthor(), '【CyclingFriends】「' .$this->name. '」に新規投稿',
            '<p>' .$this->getAuthor()->login. 'が主催するツアー「' .$this->name. '」に新規の投稿がありました。内容は下記の通り：</p>
            <p>---</p>
            <p>' .$content. '</p>
            <p>---</p>
            <p>ご確認／返信用URLは<a href="' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/ride/' .$this->id. '">こちら</a></p>'
        );
    }

    public function getMapThumbnail () {
        // Get thumbnail filename
        $getMapThumbnail = $this->getPdo()->prepare('SELECT thumbnail_filename FROM routes WHERE id = ?');
        $getMapThumbnail->execute(array($this->route_id));
        $thumbnail_filename = $getMapThumbnail->fetch(PDO::FETCH_COLUMN);
        
        // Connect to blob storage
        require Ride::$root_folder . '/actions/blobStorage.php';

        // Retrieve blob url
        return $blobClient->getBlobUrl('route-thumbnails', $thumbnail_filename);
    }

    public function getAdditionalFields () {
        // First, get all additional fields of this ride
        $getAdditionalFields = $this->getPdo()->prepare('SELECT id FROM ride_additional_fields WHERE ride_id = ?');
        $getAdditionalFields->execute(array($this->id));
        $additional_fields = [];
        while ($field = $getAdditionalFields->fetch(PDO::FETCH_ASSOC)) array_push($additional_fields, new AdditionalField($field['id']));
        return $additional_fields;
    }

    /**
     * Retrieve most related images
     * @param int $imgs_number number of images to retrieve
     * @return CheckpointImage[]
     */
    public function getImages ($imgs_number) {
        $images = [];
        $checkpoints = $this->getCheckpoints();
        for ($i = 0; $i < count($checkpoints) && $i < $imgs_number; $i++) {
            if ($checkpoints[$i]->img->url) array_push($images, $checkpoints[$i]->img);
        }
        return $images;
    }

    /**
     * Outputs description with adding specific style for specific characters
     * @return string
     */
    public function getFormattedDescription () {
        $output = $this->description;

        // Change japanese brackets to strong text
        $output = str_replace('【', '<strong>', $output);
        $output = str_replace('】', '</strong>', $output);

        // Add anchor to urls
        $output = preg_replace('<https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)>', '<a href="$0" target="_blank">$0</a>', $output);

        return $output;
    }

    /**
     * Returns an array of guides for this ride
     * @return User[] An array of guides with position property attached to them
     */
    public function getGuides () {

        // Get results
        $getGuides = $this->getPdo()->prepare("SELECT position, user_id FROM ride_guides WHERE ride_id = ? ORDER BY position ASC");
        $getGuides->execute([$this->id]);
        $result = $getGuides->fetchAll(PDO::FETCH_ASSOC);

        // Return an array of users with position property added
        $guides = [];
        foreach ($result as $entry) {
            $guide = new Guide($entry['user_id'], $this->id, $entry['position']);
            array_push($guides, $guide);
        }
        return $guides;
    }

    /**
     * Returns chief guide user for this ride
     * @return User
     */
    public function getChiefGuide () {
        $getChiefGuide = $this->getPdo()->prepare("SELECT user_id FROM ride_guides WHERE ride_id = ? AND position = 1");
        $getChiefGuide->execute([$this->id]);
        if ($getChiefGuide->rowCount() > 0) {
            $result = $getChiefGuide->fetch(PDO::FETCH_COLUMN);
            return new Guide($result, $this->id, 1);
        } else return false;
    }

    /**
     * Add a guide to this ride
     * @param int $user_id
     * @param int $position Position to add guide as. 1: chief, 2: assistant, 3: trainee
     */
    public function addGuide ($user_id, $position) {
        $checkGuide = $this->getPdo()->prepare("SELECT id FROM ride_guides WHERE ride_id = ? AND user_id = ?");
        $checkGuide->execute([$this->id, $user_id]);
        // If guide has already been added, update it
        if ($checkGuide->rowCount() > 0) {
            $updateGuide = $this->getPdo()->prepare("UPDATE ride_guides SET position = ? WHERE ride_id = ? AND user_id = ?");
            $updateGuide->execute([$position, $this->id, $user_id]);
        // Else, add it
        } else {
            $addGuide = $this->getPdo()->prepare("INSERT INTO ride_guides (ride_id, user_id, position) VALUES (?, ?, ?)");
            $addGuide->execute([$this->id, $user_id, $position]);
        }
    }

    /**
     * Remove a guide from this ride
     * @param int $user_id
     */
    public function removeGuide ($user_id) {
        $checkGuide = $this->getPdo()->prepare("DELETE FROM ride_guides WHERE ride_id = ? AND user_id = ?");
        $checkGuide->execute([$this->id, $user_id]);
    }

    /**
     * Get activity report data
     * @return RideReport
     */
    public function getReport () {
        $getReport = $this->getPdo()->prepare("SELECT id FROM ride_reports WHERE ride_id = ?");
        $getReport->execute([$this->id]);
        $report_id = $getReport->fetch(PDO::FETCH_COLUMN);
        return new RideReport($report_id);
    }

    /**
     * Insert or update a new report for this ride
     * @param string $entry_type A value among RideReport::$report_types
     */
    public function setReportEntry ($entry_type, $entry_value) {
        if (in_array($entry_type, RideReport::$report_types)) {
            $checkIfReportEntryExists = $this->getPdo()->prepare("SELECT id FROM ride_reports WHERE ride_id = ?");
            $checkIfReportEntryExists->execute([$this->id]);
            if ($checkIfReportEntryExists->rowCount() > 0) {
                $updateReport = $this->getPdo()->prepare("UPDATE ride_reports SET {$entry_type} = ? WHERE ride_id = ?");
                $updateReport->execute([$entry_value, $this->id]);
            } else {
                $insertReport = $this->getPdo()->prepare("INSERT INTO ride_reports(ride_id, {$entry_type}) VALUES (?, ?)");
                $insertReport->execute([$this->id, $entry_value]);
            }
        } else throw new Exception("Specified entry type '" .$entry_type. "' doesn't exist");

    }

}