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

    public function getOptions () {
        $getOptions = $this->getPdo()->prepare('SELECT content FROM ride_additional_field_options WHERE field_id = ? ORDER BY number ASC');
        $getOptions->execute(array($this->id));
        $options = [];
        while ($option = $getOptions->fetch()[0]) array_push($options, $option);
        return $options;
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