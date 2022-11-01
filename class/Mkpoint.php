<?php

class Mkpoint extends Model {
    
    protected $table = 'map_mkpoint';
    
    function __construct($id = NULL) {
        $this->id            = $id;
        $data = $this->getData($this->table);
        $this->user          = new User ($data['user_id']);
        $this->category      = $data['category'];
        $this->name          = $data['name'];
        $this->city          = $data['city'];
        $this->prefecture    = $data['prefecture'];
        $this->elevation     = $data['elevation'];
        $this->date          = $data['date'];
        $this->month         = $data['month'];
        $this->period        = $data['period'];
        $this->description   = $data['description'];
        $this->thumbnail     = $data['thumbnail'];
        $this->lngLat        = new LngLat($data['lng'], $data['lat']);
        $this->rating        = $data['rating'];
        $this->grades_number = $data['grades_number'];
        $this->popularity    = $data['popularity'];
        $this->likes         = $data['likes'];
    }

    public function getComments () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getComments = $db->prepare('SELECT * FROM chat_mkpoint WHERE mkpoint_id = ? ORDER BY time ASC');
        $getComments->execute(array($this->id));
        return $getComments->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get connected user's vote information
    public function getUserVote ($user) {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $checkUserVote = $db->prepare('SELECT grade FROM grade_mkpoint WHERE mkpoint_id = ? AND user_id = ?');
        $checkUserVote->execute(array($this->id, $user->id));
        if ($checkUserVote->rowCount() > 0) {
            $vote_infos = $checkUserVote->fetch(PDO::FETCH_ASSOC);
            return $vote_infos['grade'];
        } else {
            return false;
        }
    }

    // Get mkpoint images
    public function getImages () {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        $getImages = $db->prepare('SELECT id FROM img_mkpoint WHERE mkpoint_id = ?');
        $getImages->execute(array($this->id));
        $images_data = $getImages->fetchAll(PDO::FETCH_ASSOC);
        $images = [];
        for ($i = 0; $i < count($images_data); $i++) array_push($images, new MkpointImage($images_data[$i]['id']));
        return $images;
    }

}