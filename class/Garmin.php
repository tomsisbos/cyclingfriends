<?php

use Stoufa\GarminApi\GarminApi;
use League\OAuth1\Client\Credentials\TokenCredentials;

class Garmin extends Model {

    private $api_types = ['activity', 'course'];
    private $callback_uri;
    private $consumer_key;
    private $garmin_user_id;
    private $oauth_token;
    private $oauth_token_secret;
    protected $table = 'user_garmin';
    public $permissions = [];

    function __construct ($garmin_user_id = null, $oauth_token = null, $oauth_token_secret = null) {
        $this->consumer_key = getenv('GARMIN_CONSUMER_KEY');
        $this->consumer_secret = getenv('GARMIN_CONSUMER_SECRET');
        $this->callback_uri = $_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST'] . '/api/garmin/connection.php';
        if ($garmin_user_id) $this->garmin_user_id = $garmin_user_id;
        if ($oauth_token) $this->oauth_token = $oauth_token;
        if ($oauth_token_secret) $this->oauth_token_secret = $oauth_token_secret;
        $this->setDefaultPermissions();
    }

    /**
     * Set instance default permissions to true
     */
    private function setDefaultPermissions () {
        foreach ($this->api_types as $api_type) {
            array_push($this->permissions, true);
        }
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
            $insertAccessTokens = $this->getPdo()->prepare("INSERT INTO {$this->table} (user_id, garmin_user_id, oauth_token, oauth_token_secret, permission_activity, permission_course) VALUES (?, ?, ?, ?, 1, 1)");
            $insertAccessTokens->execute([$user_id, $user_data['garmin_user_id'], $user_data['oauth_token'], $user_data['oauth_token_secret']]);
        } else {
            $updateAccessTokens = $this->getPdo()->prepare("UPDATE {$this->table} SET oauth_token = ?, oauth_token_secret = ?, garmin_user_id = ? WHERE user_id = ?");
            $updateAccessTokens->execute([$user_data['garmin_user_id'], $user_data['oauth_token'], $user_data['oauth_token_secret'], $user_id]);
        }
    }

    /**
     * Populate instance with garmin user id corresponding oauth token and oauth token secret from database
     * @return boolean false if garmin user id is not set
     */
    public function populateUserTokens () {
        if (isset($this->garmin_user_id)) {
            $getUserTokens = $this->getPdo()->prepare("SELECT oauth_token, oauth_token_secret, permission_activity, permission_course FROM {$this->table} WHERE garmin_user_id = ?");
            $getUserTokens->execute([$this->garmin_user_id]);
            $user_tokens = $getUserTokens->fetch(PDO::FETCH_ASSOC);
            $this->oauth_token = $user_tokens['oauth_token'];
            $this->oauth_token_secret = $user_tokens['oauth_token_secret'];
            foreach ($this->api_types as $api_type) {
                if ($user_tokens['permission_' .$api_type] == 1) $this->setPermission($api_type, 1);
                else $this->setPermission($api_type, 0);
            }
            return true;
        } else return false;
    }

    /**
     * Update permission
     * @param string $permission
     * @param int $boolean 0 or 1
     */
    public function setPermission ($permission, $boolean) {
        $setPermission = $this->getPdo()->prepare("UPDATE {$this->table} SET permission_{$permission} = ?");
        $setPermission->execute([$boolean]);
    }

    /**
     * Retrieve an activity details from a previously obtained uploadStartTimeInSeconds and uploadEndTimeInSeconds
     * @param string $uploadStartTimeInSeconds A timestamp sent from garmin through a ping notification
     * @param string $uploadEndTimeInSeconds A timestamp sent from garmin through a ping notification
     * @return $activity_details_summary Details summary in a variable
     */
    public function retrieveActivityDetails ($uploadStartTimeInSeconds, $uploadEndTimeInSeconds) {
        $server = new GarminApi($this->getConfig());
        $token_credentials = $this->getTokenCredentials();

        $params = [
            'uploadStartTimeInSeconds' => $uploadStartTimeInSeconds, // time in seconds utc
            'uploadEndTimeInSeconds' => $uploadEndTimeInSeconds // time in seconds utc
        ];
        
        // Activity details summaries
        $activity_details_summary = $server->getActivityDetailsSummary($token_credentials, $params);

        // Save result in a file
        $user_directory = $_SERVER["DOCUMENT_ROOT"]. '/api/garmin/files/' .$this->garmin_user_id;
        if (!file_exists($user_directory)) mkdir($user_directory, 0777, true); // Create user directory if necessary
        $temp_url = $user_directory. '/file.json';
        file_put_contents($temp_url, $activity_details_summary);

        // Return content to a variable
        return json_decode($activity_details_summary);
    }

    /**
     * Retrieve an activity file from a previously obtained uploadStartTimeInSeconds and uploadEndTimeInSeconds
     * @param int $file_id File id of the activity to request for (! Different from activity id)
     * @param string $activity_token Corresponding activity token
     * @param string $ext expected file extension (fit, gpx or tcx)
     * @param array $metadata Necessary data to save file (Contains ext, garmin_activity_id, garmin_user_id)
     * @return ActivityFile $activity_file
     */
    public function retrieveActivityFile ($file_id, $activity_token, $metadata) {
        $server = new GarminApi($this->getConfig());
        $token_credentials = $this->getTokenCredentials();

        $params = [
            'id' => $file_id,
            'token' => $activity_token
        ];
        
        // Activity file
        $file_content = $server->getActivityFile($token_credentials, $params);

        // Upload file to blob server and save data to database
        $activity_file = new ActivityFile();
        $metadata['user_id'] = $activity_file->getUserIdFromGarminId($metadata['garmin_user_id']); // Get user id from garmin user id
        $activity_file->create($file_content, $metadata);

        return $activity_file;
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
            unset($this->permissions);
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
            'uploadStartTimeInSeconds' => $upload_start, // time in seconds utc
            'uploadEndTimeInSeconds' => $upload_end // time in seconds utc
        ];
        $summary = $server->getActivitySummary($token_credentials, $params);

        var_dump($summary);
    }

}