<?php

class AdditionalField extends Model {
    
    protected $table = 'ride_additional_fields';
    public $id;
    public $ride_id;
    public $question;
    public $type;

    function __construct ($id = NULL) {
        parent::__construct($id);
        $this->id          = $id;
        $data = $this->getData($this->table);
        $this->ride_id  = $data['ride_id'];
        $this->question = $data['question'];
        $this->type     = $data['type'];
        if ($this->type == 'select' || $this->type == 'product') $this->options = $this->getOptions();
    }

    public function getTypeString () {
        switch ($this->type) {
            case 'text': return '質問式';
            case 'select': return '選択式';
            case 'product': return '購入式';
        }
    }

    public function getAnswers () {
        $getAnswers = $this->getPdo()->prepare('SELECT id FROM ride_additional_field_answers WHERE field_id = ?');
        $getAnswers->execute(array($this->id));
        $ids = $getAnswers->fetchAll(PDO::FETCH_COLUMN);
        $answers = [];
        foreach ($ids as $id) array_push($answers, new AdditionalFieldAnswer($id));
        return $answers;
    }

    public function getAnswer ($user_id) {
        $answers = $this->getAnswers();
        foreach ($answers as $answer) {
            if ($answer->user_id == $user_id) return $answer;
        }
        return false;
    }

    /**
     * @param int $user_id
     * @param string $type text | select | product
     * @param int|string $data string (content) if text, int (option id) if select or product
     */
    public function setAnswer ($user_id, $type, $data) {
        if ($type == 'text') $parameter = 'content';
        else $parameter = 'option_id';
        if ($this->getAnswer($user_id)) {
            $updateAnswer = $this->getPdo()->prepare("UPDATE ride_additional_field_answers SET {$parameter} = ? WHERE field_id = ? AND user_id = ?");
            $updateAnswer->execute([$data, $this->id, $user_id]);
        } else {
            $insertAnswer = $this->getPdo()->prepare("INSERT INTO ride_additional_field_answers(field_id, user_id, type, {$parameter}) VALUES (?, ?, ?, ?)");
            $insertAnswer->execute(array($this->id, $user_id, $type, $data));
        }
        return true;
    }

    public function getOptions () {
        $getOptions = $this->getPdo()->prepare('SELECT id FROM ride_additional_field_options WHERE field_id = ? ORDER BY number ASC');
        $getOptions->execute(array($this->id));
        $options = [];
        if ($getOptions->rowCount() > 0) {
            while ($id = $getOptions->fetch(PDO::FETCH_COLUMN)) array_push($options, new AdditionalFieldOption($id));
        }
        return $options;
    }

    public function update ($type, $question) {
        $updateField = $this->getPdo()->prepare('UPDATE ride_additional_fields SET question = ?, type = ? WHERE id = ?');
        $updateField->execute(array($question, $type, $this->id));
    }

    public function setOptions ($options, $prices = []) {
        if (count($prices) > 0) $query = 'INSERT INTO ride_additional_field_options(field_id, number, content, price) VALUES (?, ?, ?, ?)';
        else $query = 'INSERT INTO ride_additional_field_options(field_id, number, content) VALUES (?, ?, ?)';
        for ($number = 1; $number <= count($options); $number++) {
            if (count($prices) > 0) $params = [$this->id, $number, $options[$number - 1], $prices[$number - 1]];
            else $params = [$this->id, $number, $options[$number - 1]];
            $insertField = $this->getPdo()->prepare($query);
            $insertField->execute($params);
        }
    }

    public function updateOptions ($options, $prices = []) {
        $removeOptions = $this->getPdo()->prepare('DELETE FROM ride_additional_field_options WHERE field_id = ?');
        $removeOptions->execute(array($this->id));
        $this->setOptions($options, $prices);
    }

    public function delete () {
        $removeField = $this->getPdo()->prepare('DELETE FROM ride_additional_fields WHERE id = ?');
        $removeField->execute(array($this->id));
        $removeAnswers = $this->getPdo()->prepare('DELETE FROM ride_additional_field_options WHERE field_id = ?');
        $removeAnswers->execute(array($this->id));
        $removeOptions = $this->getPdo()->prepare('DELETE FROM ride_additional_field_options WHERE field_id = ?');
        $removeOptions->execute(array($this->id));
    }

}