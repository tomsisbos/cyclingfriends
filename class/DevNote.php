<?php

class DevNote extends Model {
    
    protected $table = 'dev_notes';
    public $id;
    public $user_id;
    public $time;
    public $type;
    public $title;
    public $content;
    public $url;
    public $browser;
    public $chat;
    
    function __construct($id = NULL) {
        parent::__construct();
        $this->id          = $id;
        $data = $this->getData($this->table);
        $this->user_id     = $data['user_id'];
        $this->time        = $data['time'];
        $this->type        = $data['type'];
        $this->type_string = $this->getTypeString($data['type']);
        $this->title       = $data['title'];
        $this->content     = $data['content'];
        $this->url         = $data['url'];
        $this->browser     = $data['browser'];
        $this->chat        = $this->getMessages();
    }

    private function getMessages () {
        $getMessages = $this->getPdo()->prepare("SELECT id FROM dev_chat WHERE note_id = ?");
        $getMessages->execute([$this->id]);
        $entries = $getMessages->fetchAll(PDO::FETCH_ASSOC);
        $chat = [];
        foreach ($entries as $entry) array_push($chat, new DevMessage($entry['id']));
        return $chat;
    }

    private function getTypeString ($type) {
        switch ($type) {
            case 'bug': return 'バグ';
            case 'opinion': return '意見';
            case 'proposal': return '提案';
            case 'other': return 'その他';
        }
    }

    public function getUser () {
        return new User($this->user_id);
    }

    public function isAnswered () {
        foreach($this->chat as $message) {
            if ($message->getUser()->hasModeratorRights()) return true;
        }
    }

    public function post ($message, $user_id) {
        $postDevChatMessage = $this->getPdo()->prepare("INSERT INTO dev_chat (note_id, number, user_id, content) VALUES (?, ?, ?, ?)");
        $postDevChatMessage->execute(array($this->id, count($this->chat) + 1, $user_id, $message));
        $this->notify($this->user_id, 'dev_message_post');
        $this->chat = $this->getMessages(); // Update chat content
        
        // Notify administrators
        $getAdmins = $this->getPdo()->prepare("SELECT id FROM users WHERE rights = 'administrator'");
        $getAdmins->execute();
        $ids = $getAdmins->fetchAll(PDO::FETCH_COLUMN);
        foreach ($ids as $id) $this->notify($id, 'dev_message_post');
    }

} ?>