<?php

// Additional fields
$a_fields = $ride->getAdditionalFields();

if (count($a_fields) > 0) { ?>
    <div class="d-flex flex-column"> <?php 
        foreach ($a_fields as $a_field) { ?>
            <div class="form-floating mt-3"> <?php

                if ($a_field->type == "text") { ?>
                    <input type="text" name="a_field_<?= $a_field->id ?>" id="floatingAField<?= $a_field->id ?>" class="form-control js-field js-a-field"><?php if (isset($entry_data['a_field_' .$a_field->id])) echo ' value="' .$entry_data['a_field_' .$a_field->id]. '"'?></input>
                    <input type="hidden" name="a_field_<?= $a_field->id ?>_type" value="text" /> <?php
                
                } else if ($a_field->type == "select") { ?>
                    <select class="form-select js-field js-a-field" name="a_field_<?= $a_field->id ?>">
                        <option value="default" <?php if (!isset($entry_data['a_field_' .$a_field->id])) echo 'selected ' ?>disabled>選択...</option> <?php
                        foreach ($a_field->getOptions() as $option) { ?>
                            <option value="<?= $option->id ?>" <?php if (isset($entry_data['a_field_' .$a_field->id]) AND $entry_data['a_field_' .$a_field->id] == $option->id) echo ' selected'?>><?= $option->content ?></option> <?php
                        } ?>
                    </select>
                    <input type="hidden" name="a_field_<?= $a_field->id ?>_type" value="select" /> <?php
                    
                } else if ($a_field->type == "product") { ?>
                    <select class="form-select js-field js-a-field" data-type="product" data-field-id="<?= $a_field->id ?>" name="a_field_<?= $a_field->id ?>">
                        <option value="default" <?php if (!isset($entry_data['a_field_' .$a_field->id])) echo 'selected ' ?>disabled>選択...</option> <?php
                        foreach ($a_field->getOptions() as $option) { ?>
                            <option value="<?= $option->id ?>" <?php if ((isset($entry_data['a_field_' .$a_field->id]) && $entry_data['a_field_' .$a_field->id] == $option->id) || $a_field->getAnswer(getConnectedUser()->id) && $a_field->getAnswer(getConnectedUser()->id)->option->id == $option->id) echo ' selected'?>><?= $option->content. ' - ' .$option->product->currency_symbol . $option->product->price ?></option> <?php
                        } ?>
                    </select>
                    <input type="hidden" name="a_field_<?= $a_field->id ?>_type" value="product" /> <?php
                } ?>

                <label for="floatingAField<?= $a_field->id ?>"><?= $a_field->question ?></label>                        
            </div> <?php
        } ?>
    </div> <?php
} ?>