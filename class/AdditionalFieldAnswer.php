<?php

class AdditionalFieldAnswer extends Model {
    
    protected $table = 'ride_additional_field_answers';
    public $id;
    public $field_id;
    public $user_id;
    public $type;

    function __construct ($id = NULL) {
        parent::__construct($id);
        $this->id       = $id;
        $data = $this->getData($this->table);
        $this->field_id = $data['field_id'];
        $this->user_id  = $data['user_id'];
        $this->type     = $data['type'];
        if ($this->type == 'text') $this->content = $data['content'];
        else {
            $this->option = new AdditionalFieldOption($data['option_id']);
            $this->content = $this->option->content;
        }
    }

}