<?php

class Geolocation extends Model {

    public $city;
    public $prefecture;
    public $country_code;
    public $timezone;

    function __construct($city, $prefecture, $country_code = null) {
        $this->city         = $city;
        $this->prefecture   = $prefecture;
        if (isset($country_code)) {
            $this->country_code = strtoupper($country_code);
            $this->timezone     = $this->getTimeZone();
        }
    }

    /**
     * Retrieve corresponding timezone
     * @return array
     */
    private function getTimeZone () {
        $getTimeZone = $this->getGis()->prepare("SELECT `country_code`, `zone_name`, `abbreviation`, `gmt_offset`, `dst`
        FROM `time_zone`
        WHERE `time_start` <= UNIX_TIMESTAMP(UTC_TIMESTAMP()) AND `country_code` = ?
        ORDER BY `time_start` DESC LIMIT 1");
        $getTimeZone->execute([$this->country_code]);
        return $getTimeZone->fetch(PDO::FETCH_ASSOC);
    }

    public function toString () {
        return $this->city . '（' . $this->prefecture . '）';
    }

}