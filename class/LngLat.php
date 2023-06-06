<?php

class LngLat {
    
    public $lng;
    public $lat;
    
    /**
     * @param float $lng
     * @param float $lat
     */
    function __construct ($lng = NULL, $lat = NULL) {
        if ($lng) $this->lng = floatval($lng);
        if ($lat) $this->lat = floatval($lat);
    }

    /**
     * Converts coordinates to a WKT formatted point
     */
    public function toWKT () {
        return 'POINT(' .$this->lng. ' ' .$this->lat. ')';
    }

    /**
     * Loads coordinates from a WKT formatted point
     */
    public function fromWKT ($point_wkt) {
        $point = geoPHP::load($point_wkt,'wkt');
        $this->lng = $point->getX();
        $this->lat = $point->getY();
    }

    /**
     * Convert to a simple lng, lat array
     * @return float[]
     */
    public function getArray () {
        return [$this->lng, $this->lat];
    }

    /**
     * Send a geolocation query to mapbox server and return a Geolocation object containing city and prefecture string data
     * @return Geolocation
     */
    public function queryGeolocation () {

        $access_token = getenv('MAPBOX_API_KEY');
        $root_folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));

        $curl = curl_init("https://api.mapbox.com/search/v1/reverse/{$this->lng},{$this->lat}?language=ja&access_token={$access_token}");
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CAINFO => $root_folder. "bin/cacert.pem"
        ]);
        $response = curl_exec($curl);
        if (!$response) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception($error);
        } else if (curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 200) {
            curl_close($curl);
            throw new Exception($response);
        }
        $data = json_decode($response);

        return $this->decodeLocation($data);
    }

    /**
     * Decode a mapbox search api reverse geolocation response feature to a Geolocation object
     * @param StdClass $data
     * @return Geolocation
     */
    private function decodeLocation ($data) {
        $skip = false;
        // Look for prefecture data
        for ($i = 0; $i < count($data->features[0]->properties->context); $i++) {
            if (str_contains($data->features[0]->properties->context[$i]->layer, 'region') || str_contains($data->features[0]->properties->context[$i]->layer, 'prefecture')) {
                $prefecture = $data->features[0]->properties->context[$i]->name;
            }
        }
        // Look for city data
        for ($i = 0; $i < count($data->features[0]->properties->context); $i++) {
            if (str_contains($data->features[0]->properties->context[$i]->layer, 'locality')) {
                if (str_contains($data->features[0]->properties->context[$i]->name, '区')) {
                    if ($prefecture === '東京都') {
                        $city = $data->features[0]->properties->context[$i]->name;
                        break;
                    } else $skip = true;
                } else if (!$skip) {
                    $city = $data->features[0]->properties->context[$i]->name;
                    break;
                }
            }
            if (str_contains($data->features[0]->properties->context[$i]->layer, 'place')) {
                $city = $data->features[0]->properties->context[$i]->name;
                break;
            }
            if (str_contains($data->features[0]->properties->context[$i]->layer, 'city')) {
                $city = explode('(', $data->features[0]->properties->context[$i]->name)[0]; // Everything before parenthesis
                break;
            }
        }
        return new Geolocation($city, $prefecture);
    }

}