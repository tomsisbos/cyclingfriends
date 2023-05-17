<?php

declare(strict_types = 1);/*
require_once '../vendor/autoload.php';
Autoloader::register();*/
use \SendGrid\Mail\Mail;

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
        if (!$this->isRegistered()) {
            $registerEmail = $this->getPdo()->prepare('INSERT INTO mailing_list (email, token) VALUES (?, ?)');
            $registerEmail->execute(array($this->address, rand(1, 99999999)));
        }
    }
    
    public function sendRegistrationMail () {
        $email = new Mail();
        $email->setFrom(
            'contact@cyclingfriends.co',
            'CyclingFriends'
        );
        $email->setSubject('メール登録完了');
        $email->addTo($this->address);
        $email->addContent(
            'text/html',
            "<p>この度、CyclingFriendsにご登録頂き、ありがとうございます。</p>
            <p>初期のコミュニティへようこそ！</p>
            <p>これから始まるCyclingFriendsの長旅を、第一歩からご一緒頂けることをとても光栄に思っております。</p>
            <p>これからは本格スタートに向けて、CyclingFriendsのサービス内容、アカウント事前作成やベータテスト募集のご案内など、様々な情報を発信して参ります。</p>
            <p>次のぺージにて、いつでも登録を取り消して頂けます：</p>
            <p>" .$_SERVER['HTTP_HOST']. "/unsubscribe</p>
            <p>一緒に旅しましょう！</p>
            <p>CyclingFriendsチーム一同</p>"
        );
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $response = $sendgrid->send($email);
        return $response;
        /*
        try {
            $response = $sendgrid->send($email);
            printf("Response status: %d\n\n", $response->statusCode());
        
            $headers = array_filter($response->headers());
            echo "Response Headers\n\n";
            foreach ($headers as $header) {
                echo '- ' . $header . "\n";
            }
        } catch (Exception $e) {
            echo 'Caught exception: '. $e->getMessage() ."\n";
        }*/
    }

    public function unsubscribe () {
        $unsubscribeEmail = $this->getPdo()->prepare('DELETE FROM mailing_list WHERE email = ?');
        $unsubscribeEmail->execute(array($this->address));
        return true;
    }
    
    public function sendUnregistrationMail () {
        $email = new Mail();
        $email->setFrom(
            'contact@cyclingfriends.co',
            'CyclingFriends'
        );
        $email->setSubject('メールリストから除外しました');
        $email->addTo($this->address);
        $email->addContent(
            'text/html',
            "<p>日頃から、CyclingFriendsをご愛顧頂き、ありがとうございます。</p>
            <p>この度、こちらのメールアドレス（" .$this->address. "）を当社のメールリストから除外させて頂きました。</p>
            <p>またご一緒に旅できる日が待ち遠しいです。</p>
            <p>CyclingFriendsチーム一同</p>"
        );
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $response = $sendgrid->send($email);
        return $response;
    }

}