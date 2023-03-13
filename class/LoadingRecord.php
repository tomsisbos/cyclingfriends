<?php

class LoadingRecord extends Model {

    protected $table = 'loading_records';
    public $user_id;
    public $entry_table;
    public $entry_id;
    
    function __construct($user_id, $entry_table, $entry_id = NULL) {
        $this->user_id = $user_id;
        $this->entry_table = $entry_table;
        $this->entry_id = $entry_id;
    }

    /**
     * Ensure that no entry record of entry_table and entry_id remains unless entry_id exist in entry_table
     */
    private function clean () {
        $checkIfEntryIdExists = $this->getPdo()->prepare("SELECT * FROM {$this->entry_table} WHERE id = ?");
        $checkIfEntryIdExists->execute(array($this->entry_id));
        if ($checkIfEntryIdExists->rowCount() > 0) {
            $this->clear();
            return true;
        } else return false;
    }

    public function setAsLastEntryId () {
        $getLastEntryId = $this->getPdo()->prepare("SELECT entry_id FROM {$this->table} WHERE user_id = ? AND entry_table = ? ORDER BY id DESC");
        $getLastEntryId->execute(array($this->user_id, $this->entry_table));
        $this->entry_id = $getLastEntryId->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Set or update loading record
     */
    public function register () {
        $this->clean();
        if (!$this->isPending()) {
            $setLoadingRecord = $this->getPdo()->prepare("INSERT INTO {$this->table}(user_id, entry_table, entry_id, status) VALUES (?, ?, ?, ?)");
            return $setLoadingRecord->execute(array($this->user_id, $this->entry_table, $this->entry_id, 'pending'));
        } else {
            $updateLoadingRecord = $this->getPdo()->prepare("UPDATE {$this->table} SET status = ?, message = NULL WHERE user_id = ? AND entry_table = ? AND entry_id = ?");
            return $updateLoadingRecord->execute(array('pending', $this->user_id, $this->entry_table, $this->entry_id));
        }
    }

    public function isPending () {
        $checkLoadingRecord = $this->getPdo()->prepare("SELECT id FROM {$this->table} WHERE user_id = ? AND entry_table = ? AND entry_id = ?");
        $checkLoadingRecord->execute(array($this->user_id, $this->entry_table, $this->entry_id));
        if ($checkLoadingRecord->rowCount() > 0) return true;
        else return false;
    }

    public function setStatus ($status, $message = NULL) {
        if ($message !== NULL) {
            $setStatus = $this->getPdo()->prepare("UPDATE {$this->table} SET status = ?, message = ? WHERE user_id = ? AND entry_table = ? AND entry_id = ?");
            $setStatus->execute(array($status, $message, $this->user_id, $this->entry_table, $this->entry_id));
        } else {
            $setStatus = $this->getPdo()->prepare("UPDATE {$this->table} SET status = ? WHERE user_id = ? AND entry_table = ? AND entry_id = ?");
            $setStatus->execute(array($status, $this->user_id, $this->entry_table, $this->entry_id));
        }
    }

    public function getStatus () {
        $getStatus = $this->getPdo()->prepare("SELECT status, message FROM {$this->table} WHERE user_id = ? AND entry_table = ? AND entry_id = ?");
        $getStatus->execute(array($this->user_id, $this->entry_table, $this->entry_id));
        return $getStatus->fetch(PDO::FETCH_ASSOC);
    }

    public function clear () {
        $removeLoadingRecord = $this->getPdo()->prepare("DELETE FROM {$this->table} WHERE user_id = ? AND entry_table = ? AND entry_id = ?");
        $removeLoadingRecord->execute(array($this->user_id, $this->entry_table, $this->entry_id));
        if ($removeLoadingRecord->rowCount() > 0) return true;
        else return false;
    }
}