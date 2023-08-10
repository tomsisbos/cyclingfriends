<?php

include '../includes/head.php';
include '../includes/rides/admin/head.php';
include '../actions/database.php';
include '../actions/rides/admin/forms.php' ?>

<!DOCTYPE html>
<html lang="en"> 
    
    <body> <?php

        include '../includes/navbar.php'; ?>

        <div class="main rd-ad-main container-shrink"> <?php

            include '../includes/rides/admin/header.php';
            include '../includes/rides/admin/navbar.php'; ?>

            <!-- Main section -->
            <div class="container rd-ad-container">

                <h3>参加者質問設定</h3>
                <p>エントリーの際にユーザーから集める追加情報欄の設定</p>

                <div class="rd-ad-section"> <?php

                    // Display all currently registered fields for this ride
                    if (count($ride->getAdditionalFields()) > 0) { ?>
                        <div class="rd-ad-form"> <?php
                            foreach ($ride->getAdditionalFields() as $field) { 

                                // If edit button has been pressed on this entry, display editable form
                                if (isset($_POST['edit']) && $_POST['edit'] == $field->id) { ?>

                                    <div class="rd-ad-form-row">

                                        <form method="POST" class="mb-3 row g-2" id="editForm<?= $field->id ?>">

                                            <div class="col-2">
                                                <label class="form-label required">形式</label>
                                                <select class="form-select" name="type" id="editType">
                                                    <option value="default" disabled>質問形式を選択...</option>
                                                    <option value="text" <?php if ($field->type == 'text') echo 'selected' ?>>記入式</option>
                                                    <option value="select" <?php if ($field->type == 'select') echo 'selected' ?>>選択式</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-10 d-flex gap-10 js-question" id="editQuestion">
                                                <div class="col-10 g-2 mt-auto">
                                                    <label class="form-label required">質問</label>
                                                    <input type="text" class="form-control" name="question" value="<?= $field->question ?>">
                                                </div>

                                                <div class="col-2 g-2 mt-auto" id="buttonContainer">
                                                    <button type="submit" class="btn smallbutton" id="editSave" name="editSave" value="<?= $field->id ?>">保存</button>
                                                </div>
                                            </div> <?php

                                            if ($field->type == 'select') { ?>
                                                <div class="d-flex flex-column col-10 gap-10 rd-ad-options-container">
                                                    <label class="form-label required">選択</label> <?php
                                                    $options = $field->getOptions();
                                                    for ($i = 0; $i < count($options); $i++) { ?>
                                                        <div class="d-flex align-items-center rd-ad-options-block">
                                                            <div class="rd-ad-options-label"><?= $i + 1 ?>. </div>
                                                            <input type="text" value="<?= $options[$i]->content ?>" class="form-control rd-ad-options-input" name="select_option_<?= $i + 1 ?>">
                                                            <div class="btn smallbutton rd-ad-options-remove" id="removeOption<?= ($i + 1) ?>">x</div>
                                                        </div> <?php
                                                    } ?>
                                                    <div class="btn smallbutton align-self-center" id="addOptionField">選択を追加...</div>
                                                </div> <?php
                                            } ?>
                                        </form>
                                    </div> <?php

                                // Else, display static entry
                                } else { ?>
                                    <div class="rd-ad-form-row">
                                        <div class="rd-ad-form-type"><?= $field->getTypeString() ?></div>
                                        <div class="rd-ad-form-question">
                                            <div class="rd-ad-form-prefix">Q.</div> <?php
                                            echo $field->question;
                                            $options = $field->getOptions();
                                            if (count($options) > 0) {
                                                for ($i = 0; $i < count($options); $i++) { ?>
                                                    <div class="rd-ad-form-option">
                                                        <div class="rd-ad-form-prefix">n°<?= $i + 1 ?></div>
                                                        <div class="rd-ad-form-option-content"><?= $options[$i]->content ?></div>
                                                    </div> <?php
                                                }
                                            } ?>
                                        </div>
                                        <form method="POST" id="edit<?= $field->id ?>">
                                            <button type="submit" value="<?= $field->id ?>" name="edit" class="btn smallbutton">編集</button>
                                            <button type="submit" value="<?= $field->id ?>" name="delete" value="delete" id="deleteEntry" class="btn smallbutton">削除</button>
                                            <input type="hidden" id="onSubmit"></input>
                                        </form>
                                    </div> <?php
                                }
                            } ?>
                        </div> <?php
                    } ?>

                </div>

                <div class="rd-ad-section">
                
                    <h4>追加</h4>
                    
                    <form method="POST" class="mb-3 row g-2" id="add">

                        <div class="col-2">
                            <label class="form-label required">形式</label>
                            <select class="form-select" name="type" id="type">
                                <option value="default" disabled selected>質問形式を選択...</option>
                                <option value="text">記入式</option>
                                <option value="select">選択式</option>
                            </select>
                        </div>
                        
                        <div class="col-10 d-flex gap-10 js-question hidden" id="text">
                            <div class="col-10 g-2 mt-auto">
                                <label class="form-label required">質問</label>
                                <input type="text" class="form-control" name="text_question">
                            </div>

                            <div class="col-2 g-2 mt-auto" id="buttonContainer">
                                <button type="submit" class="btn smallbutton" name="add">質問を追加</button>
                            </div>
                        </div>
                        
                        <div class="col-10 d-flex flex-column gap-10 js-question hidden" id="select">
                            <div class="g-2 d-flex gap-10">
                                <div class="col-10">
                                    <label class="form-label required">質問</label>
                                    <input type="text" class="form-control mt-auto" name="select_question">
                                </div>
                                <div class="col-2 mt-auto" id="buttonContainer">
                                    <button type="submit" class="btn smallbutton" name="add">質問を追加</button>
                                </div>
                            </div>

                            <div class="d-flex flex-column col-10 gap-10 rd-ad-options-container">
                                <label class="form-label required">選択</label> <?php
                                for ($i = 1; $i < 3; $i++) { ?>
                                    <div class="d-flex align-items-center rd-ad-options-block">
                                        <div class="rd-ad-options-label"><?= $i ?>. </div>
                                        <input type="text" class="form-control rd-ad-options-input" name="select_option_<?= $i ?>">
                                    </div> <?php
                                } ?>
                                <div class="btn smallbutton align-self-center" id="addOptionField">選択を追加...</div>
                            </div>
                        </div>
                    </form>

                </div>

            </div>

        </div>

    </body>

    <script src="/scripts/rides/admin/forms.js"></script>

</html>