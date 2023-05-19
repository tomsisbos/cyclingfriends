<?php

use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter extends Model {

    private $consumer_key;
    private $consumer_secret;
    protected $table = 'user_twitter';

    /**
     * @param String $consumer_key A registered twitter application API consumer key
     * @param String $consumer_secret A registered twitter application API consumer secret
     */
    public function __construct ($consumer_key, $consumer_secret) {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
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
     * @param String $oauth_token
     * @param String $oauth_token_secret
     * @return Boolean
     */
    public function verifyCredentials ($oauth_token, $oauth_token_secret) {
        $oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $oauth_token, $oauth_token_secret);
        return $oauth->get('account/verify_credentials');
    }

}