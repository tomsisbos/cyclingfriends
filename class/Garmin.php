<?php

use Stoufa\GarminApi\GarminApi;

class Garmin extends Model {

    private $callback_uri;
    private $consumer_key;


    function __construct () {
        $this->consumer_key = getenv('GARMIN_CONSUMER_KEY');
        $this->consumer_secret = getenv('GARMIN_CONSUMER_SECRET');
        $this->callback_uri = $_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST'] . '/api/garmin/connection.php';

        $this->test();
    }


    public function test () {

        try {

            $config = [
                'identifier'     => getenv('GARMIN_KEY'),
                'secret'         => getenv('GARMIN_SECRET'),
                'callback_uri'   => $this->callback_uri
            ];

            $server = new GarminApi($config);

            var_dump($server);

            /*

            // Retreive temporary credentials from server 
            $temporaryCredentials = $server->getTemporaryCredentials();

            // Save temporary crendentials in session to use later to retreive authorization token
            $_SESSION['temporaryCredentials'] = $temporaryCredentials;

            // Get authorization link 
            $link = $server->getAuthorizationUrl($temporaryCredentials);
*/
        } catch (\Throwable $th) {
            echo 'ERROR';
            var_dump($tg);
            // catch your exception here
        }
    }

}