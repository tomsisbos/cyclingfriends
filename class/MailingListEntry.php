<?php

class MailingListEntry extends Model {
    
    protected $table = 'mailing_list';
    public $id;
    public $address;

    function __construct($address) {
        parent::__construct();
        $this->address = $address;
        $this->id = $this->getId();
        $data = $this->getData($this->table);
    }

    private function getId () {
        $getId = $this->getPdo()->prepare('SELECT id FROM mailing_list WHERE email = ?');
        $getId->execute([$this->address]);
        if ($getId->rowCount() > 0) return intval($getId->fetch(PDO::FETCH_ASSOC)['id']);
        else return NULL;
    }

    public function isRegistered () {
        if (isset($this->id) AND is_int($this->id)) return true;
        else return false;
    }

    public function register () {
        $registerEmail = $this->getPdo()->prepare('INSERT INTO mailing_list (email) VALUES (?)');
        $registerEmail->execute(array($this->address));
    }
    
    public function sendRegistrationMail () {
        
        // Define headers
        $headers = 'From: "CyclingFriends" <contact@cyclingfriends.co>' . "\r\n";

        // Define message content
        $subject = 'メール登録完了';
        $msg = "
            この度、CyclingFriendsにご登録頂き、ありがとうございます。\n
            初期のコミュニティへようこそ！\n
            これから始まるCyclingFriendsの長旅を、第一歩からご一緒頂けることをとても光栄に思っております。\n
            これからは本格スタートに向けて、CyclingFriendsのサービス内容、アカウント事前作成やベータテスト募集のご案内など、様々な情報を発信して参ります。\n
            次のぺージにて、いつでも登録を取り消して頂けます：\n
            " .$_SERVER['SERVER_NAME']. "/unsubscribe\n
            一緒に旅しましょう！\n
            CyclingFriendsチーム一同
        ";

        // Send
        $result = mail($this->address, $subject, $msg, $headers);

        return $result;
    }

    public function unsubscribe () {
        $unsubscribeEmail = $this->getPdo()->prepare('DELETE FROM mailing_list WHERE email = ?');
        $unsubscribeEmail->execute(array($this->address));
        return true;
    }
    
    public function sendUnregistrationMail () {
        
        // Define headers
        $headers = 'From: "CyclingFriends" <contact@cyclingfriends.co>' . "\r\n";

        // Define message content
        $subject = 'メールリストから除外しました';
        $msg = "
            日頃から、CyclingFriendsをご愛顧頂き、ありがとうございます。\n
            この度、こちらのメールアドレス（" .$this->address. "）を当社のメールリストから除外させて頂きました。\n
            またご一緒に旅できる日が待ち遠しいです。\n
            CyclingFriendsチーム一同
        ";

        // Send
        $result = mail($this->address, $subject, $msg, $headers);

        return $result;
    }

}