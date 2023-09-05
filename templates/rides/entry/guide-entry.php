<?php

include '../actions/users/initSession.php';
include '../actions/rides/entry/guide-entry.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<body> <?php

	include '../includes/navbar.php'; ?>

	<div class="main"><?php

    if (getConnectedUser()->id != $guide->id) {
        
        $errormessage = 'このページを表示する権利がありません。';

        // Space for general error messages
        include '../includes/result-message.php'; 

    } else {

        // Space for general error messages
        include '../includes/result-message.php'; ?>
        
        <div class="container end"> 
                
                <form class="d-flex flex-column" method="POST">
                    
                    <h2><?= $ride->name ?></h2>
                    <p>@<?= $guide->login ?>のエントリー情報記入専用ページ</p><?php

                    // Additional fields
                    $a_fields = $ride->getAdditionalFields();

                    if (count($a_fields) > 0) { ?>
                        <div class="d-flex flex-column"> <?php 
                            foreach ($a_fields as $a_field) { ?>
                                <div class="form-floating mt-3"> <?php

                                    if ($a_field->type == "text") { ?>
                                        <input type="text" name="a_field_<?= $a_field->id ?>" id="floatingAField<?= $a_field->id ?>" class="form-control js-field js-a-field"><?php
                                            if ($a_field->getAnswer($guide->id)) echo ' value="' .$a_field->getAnswer($guide->id)->content;
                                        ?></input>
                                        <input type="hidden" name="a_field_<?= $a_field->id ?>_type" value="text" /> <?php
                                    
                                    } else if ($a_field->type == "select") { ?>
                                        <select class="form-select js-field js-a-field" name="a_field_<?= $a_field->id ?>">
                                            <option value="default" disabled>選択...</option> <?php
                                            foreach ($a_field->getOptions() as $option) { ?>
                                                <option value="<?= $option->id ?>" <?php if ($a_field->getAnswer($guide->id)->option->id == $option->id) echo ' selected'?>><?= $option->content ?></option> <?php
                                            } ?>
                                        </select>
                                        <input type="hidden" name="a_field_<?= $a_field->id ?>_type" value="select" /> <?php
                                        
                                    } else if ($a_field->type == "product") { ?>
                                        <select class="form-select js-field js-a-field" data-type="product" data-field-id="<?= $a_field->id ?>" name="a_field_<?= $a_field->id ?>">
                                            <option value="default" disabled>選択...</option> <?php
                                            foreach ($a_field->getOptions() as $option) { ?>
                                                <option value="<?= $option->id ?>" <?php if ($a_field->getAnswer($guide->id)->option->id == $option->id) echo ' selected'?>><?= $option->content. ' - ' .$option->product->currency_symbol . $option->product->price ?></option> <?php
                                            } ?>
                                        </select>
                                        <input type="hidden" name="a_field_<?= $a_field->id ?>_type" value="product" /> <?php
                                    } ?>

                                    <label for="floatingAField<?= $a_field->id ?>"><?= $a_field->question ?></label>                        
                                </div> <?php
                            } ?>
                        </div> <?php
                    } ?>
                                
                    <button type="submit" class="btn button mt-2 push" id="next">送信</button>

                </form>

            </div> <?php
        } ?>

	</div>

</body>
</html>