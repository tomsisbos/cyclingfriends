<?php

class PrivateBetaMember extends Model {

    public $id;
    protected $table = 'privatebeta_members';
    public $token;
    public $email;
    public $firstname;
    public $lastname;
    public $zipcode;
    public $address;
    public $agreement;
    
    function __construct ($token = NULL) {
        parent::__construct();
        if ($token != NULL) {
            $this->token = $token;
            $data = $this->getData($token);
            $this->email = $data['email'];
            $this->firstname = $data['first_name'];
            $this->lastname = $data['last_name'];
            $this->zipcode = $data['zipcode'];
            $this->address = $data['address'];
            $this->agreement = $data['agreement'];
        }
    }

    // Get instance data from database
    protected function getData ($token) {
        if ($this->token != NULL) {
            $getData = $this->getPdo()->prepare("SELECT * FROM {$this->table} WHERE token = {$this->token}");
            $getData->execute();
            return $getData->fetch();
        }
    }

    public function register ($token, $email, $firstname, $lastname, $zipcode, $address, $agreement) {
        $registerPrivateBetaInfos = $this->getPdo()->prepare("INSERT INTO privatebeta_members(token, email, first_name, last_name, zipcode, address, agreement) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $registerPrivateBetaInfos->execute(array($token, $email, $firstname, $lastname, $zipcode, $address, $agreement));
        $this->token = $token;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->zipcode = $zipcode;
        $this->address = $address;
        $this->agreement = $agreement;
    }

    /**
     * Update user_id column with corresponding user data if exists (using email)
     */
    public function updateUserId () {
        $getUserId = $this->getPdo()->prepare("SELECT id FROM users WHERE email = ?");
        $getUserId->execute([$this->email]);
        if ($getUserId->rowCount() > 0) {
            $user_id = $getUserId->fetch(PDO::FETCH_COLUMN);
            $updateUserId = $this->getPdo()->prepare("UPDATE privatebeta_members SET user_id = ? WHERE email = ?");
            $updateUserId->execute([$user_id, $this->email]);
            $this->user_id = $user_id;
        }
    }
}