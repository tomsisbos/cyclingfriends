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
        if ($this->type == 'select') $this->options = $this->getOptions();
    }

    public function getTypeString () {
        switch ($this->type) {
            case 'text': return '質問式';
            case 'select': return '選択式';
        }
    }

    public function getAnswers () {
        $getAnswers = $this->getPdo()->prepare('SELECT id FROM ride_additional_field_answers WHERE field_id = ?');
        $getAnswers->execute(array($this->id));
        $entries = $getAnswers->fetchAll(PDO::FETCH_ASSOC);
        $answers = [];
        foreach ($entries as $entry) array_push($answers, new AdditionalFieldAnswer($entry['id']));
        return $answers;
    }

    public function getAnswer ($user_id) {
        $answers = $this->getAnswers();
        foreach ($answers as $answer) {
            if ($answer->user_id == $user_id) return $answer;
        }
        return false;
    }

    public function setAnswer ($user_id, $answer) {
        $insertAnswer = $this->getPdo()->prepare('INSERT INTO ride_additional_field_answers(field_id, user_id, content) VALUES (?, ?, ?)');
        $insertAnswer->execute(array($this->id, $user_id, $answer));
        return true;
    }

    public function getOptions () {
        $getOptions = $this->getPdo()->prepare('SELECT content FROM ride_additional_field_options WHERE field_id = ? ORDER BY number ASC');
        $getOptions->execute(array($this->id));
        $options = [];
        if ($getOptions->rowCount() > 0) {
            while ($option = $getOptions->fetch(PDO::FETCH_ASSOC)) array_push($options, $option['content']);
        }
        return $options;
    }

    public function update ($type, $question) {
        $updateField = $this->getPdo()->prepare('UPDATE ride_additional_fields SET question = ?, type = ? WHERE id = ?');
        $updateField->execute(array($question, $type, $this->id));
    }

    public function setOptions ($options) {
        for ($number = 1; $number <= count($options); $number++) {
            $insertField = $this->getPdo()->prepare('INSERT INTO ride_additional_field_options(field_id, number, content) VALUES (?, ?, ?)');
            $insertField->execute(array($this->id, $number, $options[$number - 1]));
        }
    }

    public function updateOptions ($options) {
        $removeOptions = $this->getPdo()->prepare('DELETE FROM ride_additional_field_options WHERE field_id = ?');
        $removeOptions->execute(array($this->id));
        $this->setOptions($options);
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