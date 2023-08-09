<?php

// Additional fields
$a_fields = $ride->getAdditionalFields();
if (count($a_fields) > 0) { ?>
    <div class="d-flex flex-column gap"> <?php 
        foreach ($a_fields as $a_field) { ?>
            <div class="form-floating mb-3"> <?php
                if ($a_field->type == "text") { ?>
                    <input type="text" name="a_field_<?= $a_field->id ?>" id="floatingAField<?= $a_field->id ?>" class="form-control js-field"><?php if (isset($entry_data['a_field_' .$a_field->id])) echo ' value="' .$entry_data['a_field_' .$a_field->id]. '"'?></input> <?php
                } else if ($a_field->type == "select") { ?>
                    <select class="form-select js-field" name="a_field_<?= $a_field->id ?>">
                        <option value="default" <?php if (!isset($entry_data['a_field_' .$a_field->id])) echo 'selected ' ?>disabled>選択...</option> <?php
                        foreach ($a_field->getOptions() as $option) { ?>
                            <option value="<?= $option ?>" <?php if (isset($entry_data['a_field_' .$a_field->id]) AND $entry_data['a_field_' .$a_field->id] == $option) echo ' selected'?>><?= $option ?></option> <?php
                        } ?>
                    </select> <?php
                } ?>
                <label for="floatingAField<?= $a_field->id ?>"><?= $a_field->question ?></label>                        
            </div> <?php
        } ?>
    </div> <?php
} ?>