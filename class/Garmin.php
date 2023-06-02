<?php

use Stoufa\GarminApi\GarminApi;
use League\OAuth1\Client\Credentials\TokenCredentials;

class Garmin extends Model {

    private $callback_uri;
    private $consumer_key;
    private $garmin_user_id;
    private $oauth_token;
    private $oauth_token_secret;
    protected $table = 'user_garmin';

    function __construct ($garmin_user_id = null, $oauth_token = null, $oauth_token_secret = null) {
        $this->consumer_key = getenv('GARMIN_CONSUMER_KEY');
        $this->consumer_secret = getenv('GARMIN_CONSUMER_SECRET');
        $this->callback_uri = $_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST'] . '/api/garmin/connection.php';
        if ($garmin_user_id) $this->garmin_user_id = $garmin_user_id;
        if ($oauth_token) $this->oauth_token = $oauth_token;
        if ($oauth_token_secret) $this->oauth_token_secret = $oauth_token_secret;
    }

    /**
     * Get GarminApi configuration parameter ready
     * @return array Configuration parameter
     */
    private function getConfig () {
        return [
            'identifier'     => $this->consumer_key,
            'secret'         => $this->consumer_secret,
            'callback_uri'   => $this->callback_uri
        ];
    }

    /**
     * Get GarminApi token credentials object instance
     */
    private function getTokenCredentials () {
        // recreate tokenCredentials from identifier and secret
        $token_credentials = new TokenCredentials();
        $token_credentials->setIdentifier($this->oauth_token);
        $token_credentials->setSecret($this->oauth_token_secret);
        return $token_credentials;
    }

    /**
     * Get URL to use for user authorization
     * @return String user authorization URL
     */
    public function getAuthenticateUrl () {
        try {
            $server = new GarminApi($this->getConfig());
            $temporary_credentials = $server->getTemporaryCredentials();
            $_SESSION['garmin_temporary_credentials'] = $temporary_credentials; // Save temporary crendentials in session to use after redirection in order to retreive authorization token
            return $server->getAuthorizationUrl($temporary_credentials);

        } catch (\Throwable $th) {
            echo 'ERROR1';
            var_dump($th);
            // catch your exception here
        }
    }

    /**
     * Retrieve an user's oauth token, secret and garmin id from temporary credentials, token and verifiers
     * @param string $temporary_credentials
     * @param string $oauth_token
     * @param string $oauth_verifier
     * @return array Access tokens
     */
    public function getAccessToken ($temporary_credentials, $oauth_token, $oauth_verifier) {
        $server = new GarminApi($this->getConfig());
        $token_credentials = $server->getTokenCredentials($temporary_credentials, $oauth_token, $oauth_verifier);
        $garmin_user_id = $server->getUserUid($token_credentials);
        return ['garmin_user_id' => $garmin_user_id, 'oauth_token' => $token_credentials->getIdentifier(), 'oauth_token_secret' => $token_credentials->getSecret()];
    }

    /**
     * Create or update connected user garmin tokens entry in the database
     * @param int $user_id
     * @param array $user_data An array containing oauth_token, oauth_token_secret and garmin_id
     */
    public function saveUserData ($user_id, $user_data) {
        $checkIfEntryExists = $this->getPdo()->prepare("SELECT id FROM {$this->table} WHERE user_id = ?");
        $checkIfEntryExists->execute([$user_id]);
        if ($checkIfEntryExists->rowCount() == 0) {
            $insertAccessTokens = $this->getPdo()->prepare("INSERT INTO {$this->table} (user_id, garmin_user_id, oauth_token, oauth_token_secret) VALUES (?, ?, ?, ?)");
            $insertAccessTokens->execute([$user_id, $user_data['garmin_user_id'], $user_data['oauth_token'], $user_data['oauth_token_secret']]);
        } else {
            $updateAccessTokens = $this->getPdo()->prepare("UPDATE {$this->table} SET oauth_token = ?, oauth_token_secret = ?, garmin_user_id = ? WHERE user_id = ?");
            $updateAccessTokens->execute([$user_data['garmin_user_id'], $user_data['oauth_token'], $user_data['oauth_token_secret'], $user_id]);
        }
    }    

    /**
     * Check whether instance stores property of a garmin user
     * @return boolean
     */
    public function isUserConnected () {
        if (isset($this->garmin_user_id)) return true;
        else return false;
    }

    /**
     * Remove user twitter tokens from the database and unpopulate instance
     */
    public function disconnect () {
        if ($this->isUserConnected()) {

            // Delete entry
            $removeUserTokens = $this->getPdo()->prepare("DELETE FROM user_garmin WHERE garmin_user_id = ?");
            $removeUserTokens->execute([$this->garmin_user_id]);

            // Unpopulate
            unset($this->garmin_user_id);
            unset($this->oauth_token);
            unset($this->oauth_token_secret);
        }
    }

    public function backfill () {
        $server = new GarminApi($this->getConfig());
        $token_credentials = $this->getTokenCredentials();
        $upload_start = (new DateTime())->modify('-1 month')->getTimestamp(); // start time in seconds UTC
        $upload_end = (new DateTime())->getTimestamp(); // end time in seconds UTC

        // Backfill activities before pulling activities (probably you must wait before it fills the summaries)
        $params = [
            'summaryStartTimeInSeconds' => $upload_start,
            'summaryEndTimeInSeconds' => $upload_end
        ];
        $server->backfillActivitySummary($token_credentials, $params);

        echo 'backfilled';

        // Activity summaries
        $params = [
            'uploadStartTimeInSeconds' => $uploadStartTimeInSeconds, // time in seconds utc
            'uploadEndTimeInSeconds' => $uploadEndTimeInSeconds // time in seconds utc
        ];
        $summary = $server->getActivitySummary($token_credentials, $params);

        var_dump($summary);
    }

}