<?php

use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;

class Notification extends Model {
    
    protected $table = 'notifications';
    public $id;
    public $user_id;
    public $type;
    public $actor_id;
    public $entry_table;
    public $entry_id;
    public $checked;
    public $datetime;
    public $text;
    public $ref;

    function __construct ($id = NULL) {
        parent::__construct();
        if ($id !== NULL) $this->load($id);
    }

    public function load ($id) {
        $this->id          = intval($id);
        $data = $this->getData($this->table);
        $this->user_id     = $data['user_id'];
        $this->type        = $data['type'];
        $this->actor_id    = $data['actor_id'];
        $this->entry_table = $data['entry_table'];
        $this->entry_id    = $data['entry_id'];
        $this->checked     = (intval($data['checked']) === 1);
        $this->datetime    = new Datetime($data['datetime']);
    }

    public function register ($user_id, $type, $entry_table, $entry_id, $actor_id = NULL) {

        $datetime = new Datetime('now', new DateTimeZone('Asia/Tokyo'));
        $base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require_once $base_directory. 'includes/functions.php';
        if ($actor_id != NULL) $query = "SELECT id FROM {$this->table} WHERE user_id = ? AND type = ? AND actor_id = {$actor_id} AND entry_table = ? AND entry_id = ?";
        else $query = "SELECT id FROM {$this->table} WHERE user_id = ? AND type = ? AND entry_table = ? AND entry_id = ?";
        $checkIfExists = $this->getPdo()->prepare($query);
        $checkIfExists->execute([$user_id, $type, $entry_table, $entry_id]);

        // If similar entry exists, reset checked and datetime values
        if ($checkIfExists->rowCount() > 0) {
            $id = $checkIfExists->fetch(PDO::FETCH_COLUMN);
            $updateNotification = $this->getPdo()->prepare("UPDATE {$this->table} SET checked = 0, datetime = ? WHERE id = ?");
            $updateNotification->execute([$datetime->format('Y-m-d H:i:s'), $id]);
        
        // Else, insert it
        } else {
            $createNotification = $this->getPdo()->prepare("INSERT INTO {$this->table} (user_id, type, actor_id, entry_table, entry_id, datetime) VALUES (?, ?, ?, ?, ?, ?) RETURNING id");
            $createNotification->execute([$user_id, $type, $actor_id, $entry_table, $entry_id, $datetime->format('Y-m-d H:i:s')]);
            $id = $createNotification->fetchColumn();
        }

        // Set instance properties
        $this->id          = $id;
        $this->user_id     = $user_id;
        $this->type        = $type;
        $this->actor_id    = $actor_id;
        $this->entry_table = $entry_table;
        $this->entry_id    = $entry_id;
        $this->checked     = false;
        $this->datetime    = $datetime;
    }

    /**
     * Get the instance of which user notification is to be notified to
     */
    public function getUser () {
        return new User($this->user_id);
    }

    /**
     * Get the instance of what is notification related to
     */
    public function getEntry () {
        switch ($this->entry_table) {
            case 'activities': return new Activity($this->entry_id); break;
            case 'routes': return new Route($this->entry_id); break;
            case 'rides': return new Ride($this->entry_id); break;
            case 'users': return new User($this->entry_id); break;
            case 'sceneries': return new Scenery($this->entry_id); break;
            case 'dev_notes': return new DevNote($this->entry_id); break;
            /// [...]
        }
    }

    /**
     * Generate text notification according to type
     */
    public function getText () {

        $entry = $this->getEntry();

        // If synced activity custom error
        if (str_contains($this->type, 'new_synced_activity_othererror_')) {
            $this->text = str_replace('new_synced_activity_othererror_', '', $this->type);
            return;
        }

        switch ($this->type) {
            // Users
            case 'friends_request':
                $this->text = $entry->login. 'から友達リクエストが届いています。';
                $this->ref = 'friends';
                break;
            case 'friends_approval':
                $this->text = $entry->login. 'が友達リクエストを承認してくれました！';
                $this->ref = 'rider/' .$entry->id;
                break;
            case 'follow':
                $this->text = $entry->login. 'がフォローしてくれました！';
                $this->ref = 'rider/' .$entry->id;
                break;
            // Activities
            case 'activity_new_comment':
                $actor = new User($this->actor_id);
                $this->text = $actor->login. 'が「' .$entry->title. '」にコメントしました。';
                $this->ref = 'activity/' .$entry->id;
                break;
            case 'activity_new_like':
                $actor = new User($this->actor_id);
                $this->text = $actor->login. 'が「' .$entry->title. '」にいいねしました。';
                $this->ref = 'activity/' .$entry->id;
                break;
            case 'new_synced_activity':
                $this->text = '新規アクティビティ「' .$entry->title. '」が同期されました。ストーリーを完成させましょう！';
                $this->ref = 'activity/' .$entry->id. '/edit';
                break;
            case 'new_synced_activity_error_missing_coordinates':
                $this->text = '座標データの含まれていないファイルがあったため、Garmin Connectとの同期に失敗しました。';
                break;
            case 'new_synced_activity_file_not_found':
                $this->text = '該当するファイルがサーバー上に存在出来なかったため、アクティビティデータを解析できませんでした。';
                break;
            case 'new_synced_activity_error_missing_record':
                $this->text = 'データが不完全なファイルがあったため、Garmin Connectとの同期に失敗しました。';
                break;
            // Sceneries
            case 'scenery_review_posting':
                $this->text = '絶景スポット「' .$entry->name. '」に新しいコメントが投稿されました。';
                $this->ref = 'scenery/' .$entry->id;
                break;
            // Rides
            case 'ride_join':
                $actor = new User($this->actor_id);
                $this->text = $actor->login. 'が「' .$entry->name. '」にエントリーしました！';
                $this->ref = 'ride/' .$entry->id. '/admin';
                break;
            case 'ride_quit':
                $actor = new User($this->actor_id);
                $this->text = $actor->login. 'が「' .$entry->name. '」への参加をキャンセルしました。';
                $this->ref = 'ride/' .$entry->id. '/admin';
                break;
            case 'ride_message_post':
                $actor = new User($this->actor_id);
                $this->text = $actor->login. 'が「' .$entry->name. '」で新規メッセージを投稿しました。';
                $this->ref = 'ride/' .$entry->id. '#chat';
                break;
            case 'ride_edited':
                $this->text = 'エントリーしている「' .$entry->name. '」の開催概要が主催者によって編集されました。変更がないか、再度ご確認することをおススメします。';
                $this->ref = 'ride/' .$entry->id;
                break;
            case 'ride_privacy_change':
                $this->text = 'エントリーしている「' .$entry->name. '」のプライバシー設定が主催者によって「' .$entry->getPrivacyString(). '」に変更されました。';
                $this->ref = 'ride/' .$entry->id;
                break;
            case 'ride_entry_start_change':
                $this->text = 'エントリーしている「' .$entry->name. '」のエントリー開始日が主催者によって' .$entry->entry_start. 'に変更されました。';
                $this->ref = 'ride/' .$entry->id;
                break;
            case 'ride_entry_end_change':
                $this->text = 'エントリーしている「' .$entry->name. '」のエントリー締切日が主催者によって' .$entry->entry_end. 'に変更されました。';
                $this->ref = 'ride/' .$entry->id;
                break;
            case 'ride_payment_failed':
                $this->text = '決済が失敗したため、「' .$entry->name. '」へのエントリーを付け付けることができませんでした。大変お手数ですが、決済方法をご確認頂きますようお願い致します。';
                $this->ref = 'ride/' .$entry->id;
            // Dev notes
            case 'new_devnote':
                $this->text = '新しい開発ノートが「' .$entry->title. '」というタイトルで投稿されました。';
                $this->ref = 'dev/note/' .$entry->id;
                break;
            case 'dev_message_post':
                $this->text = '開発ノート「' .$entry->title. '」に新しいメッセージが投稿されました。';
                $this->ref = 'dev/note/' .$entry->id;
                break;
            default: '通知内容を取得できませんでした。';
        }
    }

    /**
     * Set this notification checked property to true
     */
    public function check () {
        $checkNotification = $this->getPdo()->prepare("UPDATE {$this->table} SET checked = 1, datetime = NOW() WHERE id = ?");
        $checkNotification->execute([$this->id]);
    }

    /**
     * Send a push notification to Expo Push API
     */
    public function sendPushNotification () {

        $user = new User($this->user_id);

        $this->getText();

        $message = (new ExpoMessage([
            'title' => 'CyclingFriends',
            'body' => $this->text,
        ]));

        $data = ['id' => $this->id];
        if ($this->entry_table && $this->entry_id) {
            $data['entryTable'] = $this->entry_table;
            $data['entryId'] = $this->entry_id;
        }
        $message->setData($data);

        // Only send a push notification if a push token has been registered for this user
        if ($user->push_token) (new Expo)->send($message)->to($user->push_token)->push();
    }
}