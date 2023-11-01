<?php

class RentalBike extends Model {
    
    private $container_name = 'rental-bikes';
    protected $table = 'rental_bikes';
    public $id;
    public $name;
    public $description;
    public $type;
    public $frame_model;
    public $gears_number;
    public $ebike;
    public $size;
    public $allowed_height;
    public $price_ride;
    public $price_hour;
    public $price_day;
    public $price_2days;
    public $price_week;
    public $price_month;
    public $photo_url;
    

    function __construct($id = NULL) {
        parent::__construct();
        if ($id != NULL) $this->load($id);
    }

    public function load ($id) {
        $this->id             = $id;
        $data = $this->getData($this->table);
        $this->name           = $data['name'];
        $this->description    = $data['description'];
        $this->type           = $this->getBikeTypeString($data['type']);
        $this->frame_model    = $data['frame_model'];
        $this->gears_number   = $data['gears_number'];
        $this->ebike          = $data['ebike'];
        $this->size           = $data['size'];
        $this->allowed_height = $data['allowed_height'];
        $this->price_ride     = $data['price_ride'];
        $this->price_hour     = $data['price_hour'];
        $this->price_day      = $data['price_day'];
        $this->price_2days    = $data['price_2days'];
        $this->price_week     = $data['price_week'];
        $this->price_month    = $data['price_month'];
        $this->photo_url      = $data['photo_url'];
    }

    public function getProduct () {
        return new Product($this->name. 'レンタル', $this->price_ride);
    }

    public function getBikeTypeString ($slug) {
        $getBikeTypeString = $this->getPdo()->prepare("SELECT name FROM bike_types WHERE slug = ?");
        $getBikeTypeString->execute([$slug]);
        return $getBikeTypeString->fetch(PDO::FETCH_COLUMN);
    }

}