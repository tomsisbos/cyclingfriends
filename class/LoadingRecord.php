<?php

class LoadingRecord extends Model {

    protected $table = 'loading_records';
    public $user_id;
    public $entry_type;
    public $entry_id;
    
    function __construct($user_id, $entry_type, $entry_id = NULL) {
        $this->user_id = $user_id;
        $this->entry_type = $entry_type;
        $this->entry_id = $entry_id;
    }

    public function setAsLastEntryId () {
        $getLastEntryId = $this->getPdo()->prepare("SELECT entry_id FROM {$this->table} WHERE user_id = ? AND entry_type = ? ORDER BY entry_id DESC");
        $getLastEntryId->execute(array($this->user_id, $this->entry_type));
        $this->entry_id = intval($getLastEntryId->fetch(PDO::FETCH_COLUMN));
    }

    public function register () {
        if (!$this->isPending()) {
            $setLoadingRecord = $this->getPdo()->prepare("INSERT INTO {$this->table}(user_id, entry_type, entry_id, status) VALUES (?, ?, ?, ?)");
            return $setLoadingRecord->execute(array($this->user_id, $this->entry_type, $this->entry_id, 'pending'));
        } else {
            $updateLoadingRecord = $this->getPdo()->prepare("UPDATE {$this->table} SET status = ?, message = NULL WHERE user_id = ? AND entry_type = ? AND entry_id = ?");
            return $updateLoadingRecord->execute(array('pending', $this->user_id, $this->entry_type, $this->entry_id));
        }
    }

    public function isPending () {
        $checkLoadingRecord = $this->getPdo()->prepare("SELECT id FROM {$this->table} WHERE user_id = ? AND entry_type = ? AND entry_id = ?");
        $checkLoadingRecord->execute(array($this->user_id, $this->entry_type, $this->entry_id));
        if ($checkLoadingRecord->rowCount() > 0) return true;
        else return false;
    }

    public function setStatus ($status, $message = NULL) {
        if ($message !== NULL) {
            $setStatus = $this->getPdo()->prepare("UPDATE {$this->table} SET status = ?, message = ? WHERE user_id = ? AND entry_type = ? AND entry_id = ?");
            $setStatus->execute(array($status, $message, $this->user_id, $this->entry_type, $this->entry_id));
        } else {
            $setStatus = $this->getPdo()->prepare("UPDATE {$this->table} SET status = ? WHERE user_id = ? AND entry_type = ? AND entry_id = ?");
            $setStatus->execute(array($status, $this->user_id, $this->entry_type, $this->entry_id));
        }
    }

    public function getStatus () {
        $getStatus = $this->getPdo()->prepare("SELECT status, message FROM {$this->table} WHERE user_id = ? AND entry_type = ? AND entry_id = ?");
        $getStatus->execute(array($this->user_id, $this->entry_type, $this->entry_id));
        return $getStatus->fetch(PDO::FETCH_ASSOC);
    }

    public function clear () {
        $removeLoadingRecord = $this->getPdo()->prepare("DELETE FROM {$this->table} WHERE user_id = ? AND entry_type = ? AND entry_id = ?");
        $removeLoadingRecord->execute(array($this->user_id, $this->entry_type, $this->entry_id));
        if ($removeLoadingRecord->rowCount() > 0) return true;
        else return false;
    }
}