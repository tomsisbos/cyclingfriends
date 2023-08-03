<?php

class AutopostingEntry extends Model {

    private $twitter_account_user_id = 11; // 11 = TestCfds, 2 = cyclingfds

    protected $table = 'autoposting';

    public $id;

    public $entry_type;

    public $entry_id;

    public $api;

    public $text;

    /**
     * @param array $medias Up to 4 urls
     */
    public $medias = [];

    public $instance;

    public $history;


    function __construct ($entry_type = null, $entry_id = null, $api = null) {
        parent::__construct();
        if ($entry_type != null) {
            $this->entry_type = $entry_type;
            $this->entry_id = $entry_id;
            $this->api = $api;
            $this->instance = $this->getInstance();
            $this->text = $this->generateText();
            $this->medias = $this->generateMedias();
            $this->history = $this->getHistory();
        }
    }


    /**
     * Get relevant entry instance
     */
    private function getInstance () {
        switch ($this->entry_type) {
            case 'scenery': return new Scenery($this->entry_id);
        }
    }

    private function generateText () {
        switch ($this->api) {

            case 'twitter':

            $nl = chr(13) . chr(10);

            $tweet_head = '【絶景スポット紹介】' . $nl . $nl;
            $name = $this->instance->name . $nl;
            $place = $this->instance->city . '（' . $this->instance->prefecture . '）' . $nl . $nl;
            $description = $this->instance->description;
            $url = $nl . $nl . $_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/scenery/' .$this->instance->id;

            $text = $tweet_head . $name . $place . $description;

            if (mb_strlen($text, 'UTF-8') > 116) $text = mb_substr($text, 0, 120, "UTF-8") . '...';

            return $text . $url;
        }
    }
    
    private function generateMedias () {
        switch ($this->api) {

            case 'twitter':
               
            $medias = [];
            $images = $this->instance->getImages(4);

            for ($i = 0; $i < 4; $i++) if (isset($images[$i])) array_push($medias, $images[$i]->url);

            return $medias;
        }
    }

    public function populate ($id) {
        $getEntryFromId = $this->getPdo()->prepare("SELECT entry_type, entry_id, api, text FROM autoposting WHERE id = ?");
        $getEntryFromId->execute([$id]);
        $data = $getEntryFromId->fetch(PDO::FETCH_ASSOC);
        $this->id = $id;
        $this->entry_type = $data['entry_type'];
        $this->entry_id = $data['entry_id'];
        $this->api = $data['api'];
        $this->text = $data['text'];
        $this->instance = $this->getInstance();
        $this->medias = $this->generateMedias();
        $this->history = $this->getHistory();
        return $this;
    }

    public function generate () {
        $id = getNextAutoIncrement('autoposting');
        $insertAutopostingEntry = $this->getPdo()->prepare('INSERT INTO autoposting (entry_type, entry_id, api, text, media1, media2, media3, media4) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $insertAutopostingEntry->execute([$this->entry_type, $this->entry_id, $this->api, $this->text, $this->medias[0], $this->medias[1], $this->medias[2], $this->medias[3]]);
        $this->id = $id;
    }

    public function remove () {        
        $removePostingEntry = $this->getPdo()->prepare("DELETE FROM autoposting WHERE id = ?");
        $removePostingEntry->execute([$this->id]);
    }

    /**
     * Return posting history for this specific entry
     */
    public function getHistory () {
        $getPostingHistory = $this->getPdo()->prepare("SELECT datetime FROM autoposting_history WHERE entry_type = ? AND entry_id = ? AND api = ?");
        $getPostingHistory->execute([$this->entry_type, $this->entry_id, $this->api]);
        return $getPostingHistory->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Post entry to twitter
     * @return $result
     */
    public function post () {
        $photos = [];
        $twitter = (new User($this->twitter_account_user_id))->getTwitter();
        $result = $twitter->post($this->text, $this->medias);
        if (isset($result->data)) $this->addToHistory();
        return $result;
    }

    /**
     * Add entry to posting history
     */
    public function addToHistory () {
        $addToHistory = $this->getPdo()->prepare("INSERT INTO autoposting_history (entry_type, entry_id, api, datetime) VALUES (?, ?, ?, ?)");
        $addToHistory->execute([$this->entry_type, $this->entry_id, $this->api, (new DateTime('now'))->setTimezone(new DateTimeZone('Asia/Tokyo'))->format('Y-m-d H:i:s')]);
    }
}