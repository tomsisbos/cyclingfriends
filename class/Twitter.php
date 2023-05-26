<?php

use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter extends Model {

    private $consumer_key;
    private $consumer_secret;
    private $oauth_token;
    private $oauth_token_secret;
    protected $table = 'user_twitter';

    /**
     * Populate instance with oauth token corresponding user info
     */
    private function populateUser () {
        if (isset($this->oauth_token) && isset($this->oauth_token_secret)) {
            $oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->oauth_token, $this->oauth_token_secret);
            $data = $oauth->get('account/verify_credentials');
            $this->id = $data->id;
            $this->name = $data->name;
            $this->username = $data->screen_name;
            $this->description = $data->description;
            $this->url = 'https://twitter.com/' .$data->screen_name;
            $this->profile_image = $data->profile_image_url_https;
            $this->background_image = $data->profile_background_image_url_https;
            $this->site = $data->url;
            $this->followers_count = $data->followers_count;
            $this->following_count = $data->friends_count;
            $this->tweets_count = $data->statuses_count;
            $this->created_at = $data->created_at;
            $this->verified = $data->verified;
            if (isset($data->status)) $this->last_tweet = $data->status;
            $this->style = [
                'profile_background_color' => $data->profile_background_color,
                'profile_background_tile' => $data->profile_background_tile,
                'profile_link_color' => $data->profile_link_color,
                'profile_sidebar_border_color' => $data->profile_sidebar_border_color,
                'profile_sidebar_fill_color' => $data->profile_sidebar_fill_color,
                'profile_text_color' => $data->profile_text_color,
                'profile_use_background_image' => $data->profile_use_background_image
            ];
        }
    }

    /**
     * @param String $oauth_token A registered user token
     * @param String $oauth_token_secret A registered user token secret
     */
    public function __construct ($oauth_token = null, $oauth_token_secret = null) {
        $this->consumer_key = getenv('TWITTER_API_CONSUMER_KEY');
        $this->consumer_secret = getenv('TWITTER_API_CONSUMER_SECRET');
        if ($oauth_token) $this->oauth_token = $oauth_token;
        if ($oauth_token_secret) $this->oauth_token_secret = $oauth_token_secret;
        if ($this->oauth_token && $this->oauth_token_secret) $this->populateUser();
    }

    /**
     * Get URL to use for user authorization
     * @param String $callback redirection URL after authorization process has terminated
     * @return String user authorization URL
     */
    public function getAuthenticateUrl ($callback) {
        $oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret);
        $request_token = $oauth->oauth('oauth/request_token', ['oauth_callback' => $callback]);
        $_SESSION['twitter']['request_token'] = $request_token;
        return $oauth->url('oauth/authorize', ['oauth_token' => $request_token['oauth_token']]);
    }

    /**
     * Get user's twitter account access tokens from the oauth verifier
     * @param String $oauth_token
     * @param String $oauth_verifier
     * @return Array Access tokens
     */
    public function getAccessToken ($oauth_token, $oauth_verifier) {
        $request_token = $_SESSION['twitter']['request_token'];
        if ($oauth_token != $request_token['oauth_token']) throw new Exception('Oauth token mismatch');
        else {
            $oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $request_token['oauth_token'], $request_token['oauth_token_secret']);
            return $oauth->oauth('oauth/access_token', ['oauth_verifier' => $oauth_verifier]);
        }
    }

    /**
     * Create or update connected user twitter tokens entry in the database
     * @param Int $user_id
     * @param Array $user_data An array containing oauth_token, oauth_token_secret, twitter_id and screen_name
     */
    public function saveUserData ($user_id, $user_data) {
        $checkIfEntryExists = $this->getPdo()->prepare("SELECT id FROM {$this->table} WHERE user_id = ?");
        $checkIfEntryExists->execute([$user_id]);
        if ($checkIfEntryExists->rowCount() == 0) {
            $insertAccessTokens = $this->getPdo()->prepare("INSERT INTO {$this->table} (user_id, oauth_token, oauth_token_secret, twitter_id, screen_name) VALUES (?, ?, ?, ?, ?)");
            $insertAccessTokens->execute([$user_id, $user_data['oauth_token'], $user_data['oauth_token_secret'], $user_data['user_id'], $user_data['screen_name']]);
        } else {
            $updateAccessTokens = $this->getPdo()->prepare("UPDATE {$this->table} SET oauth_token = ?, oauth_token_secret = ?, twitter_id = ?, screen_name = ? WHERE user_id = ?");
            $updateAccessTokens->execute([$user_data['oauth_token'], $user_data['oauth_token_secret'], $user_data['user_id'], $user_data['screen_name'], $user_id]);
        }
    }

    /**
     * Check if saved tokens are still up to date
     * @param string $oauth_token
     * @param string $oauth_token_secret
     * @return STDClass|boolean an object containing account data if succeed, false otherwise
     */
    public function verifyCredentials ($oauth_token, $oauth_token_secret) {
        $oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $oauth_token, $oauth_token_secret);
        $result = $oauth->get('account/verify_credentials');
        if (isset($result->id)) return $result;
        else return false;
    }

    /**
     * Check whether instance stores property of a twitter user
     * @return boolean
     */
    public function isUserConnected () {
        if (isset($this->username)) return true;
        else return false;
    }

    /**
     * Remove user twitter tokens from the database and unpopulate instance
     */
    public function disconnect () {
        if ($this->isUserConnected()) {

            // Delete entry
            $removeUserTokens = $this->getPdo()->prepare("DELETE FROM user_twitter WHERE oauth_token = ? AND oauth_token_secret = ?");
            $removeUserTokens->execute([$this->oauth_token, $this->oauth_token_secret]);

            $username = $this->username;

            // Unpopulate
            unset($this->id);
            unset($this->name);
            unset($this->username);
            unset($this->description);
            unset($this->url);
            unset($this->profile_image);
            unset($this->background_image);
            unset($this->site);
            unset($this->followers_count);
            unset($this->following_count);
            unset($this->tweets_count);
            unset($this->created_at);
            unset($this->verified);
            unset($this->last_tweet);
            unset($this->style);
            unset($this->oauth_token);
            unset($this->oauth_token_secret);
            $_SESSION['successmessage'] = '@' .$username. 'との接続が解除されました。';
        }
    }

    /**
     * Post a new tweet
     * @param string $text
     * @param array $photos Array of photo urls (up to 4)
     * @return any
     */
    public function post ($text, $photos) {
        
        $oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->oauth_token, $this->oauth_token_secret);

        // Upload photos to twitter
        if (count($photos) > 4) throw new Exception(count($photos). '枚の写真が添付されていますが、上限は4枚までです。');
        $photo_ids = [];
        for ($i = 0; $i < count($photos); $i++) {
            $temp_url = $_SERVER["DOCUMENT_ROOT"]. '/media/temp/tweet_' .$i. '.jpg';
            $file = file_get_contents($photos[$i]);
            file_put_contents($temp_url, $file);
            $media = $oauth->upload('media/upload', ['media' => $temp_url], true);
            array_push($photo_ids, $media->media_id_string);
        }
        $oauth->setApiVersion('2');
        $result = $oauth->post('tweets', ['text' => $text, 'media' => ['media_ids' => $photo_ids]], true);
        return $result;
    }

}