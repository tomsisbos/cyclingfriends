<!DOCTYPE html>
<html lang="en"> <?php

include '../includes/rides/admin/head.php';
include '../actions/databaseAction.php';
include '../actions/rides/admin/forms.php' ?>

    <body> <?php

        include '../includes/navbar.php'; ?>

        <div class="main container-shrink"> <?php

            include '../includes/rides/admin/header.php';
            
            include '../includes/rides/admin/navbar.php' ?>

            <!-- Main section -->
            <div class="container end">

                <h3>参加者質問設定</h3>
                <p>エントリーの際にユーザーから集める追加情報欄の設定</p>

                <h4>変更</h4> <?php

                    if (isset($additional_fields)) {
                        foreach ($additional_fields as $field) { ?>
                            <div class="rd-ad-form-type"><?= $field['type'] ?></div>
                            <div class="rd-ad-form-type"><?= $field['question'] ?></div> <?php
                        }
                    }
                    ?>
                
                <h4>追加</h4>
                
                <form method="POST">

                    <div class="mb-3 row g-2">
                        <div class="col-2">
                            <label class="form-label required">形式</label>
                            <select class="form-select" name="type" id="type">
                                <option value="default" disabled selected>質問形式を選択...</option>
                                <option value="text">記入式</option>
                                <option value="select">選択式</option>
                            </select>
                        </div>
                        
                        <div class="col-10 row js-question hidden" id="text">
                            <div class="col-9 g-2" id="contentContainer">
                                <label class="form-label required">質問</label>
                                <input type="text" class="form-control" name="text_question">
                            </div>

                            <div class="col-3 g-2 mt-auto" id="buttonContainer">
                                <button type="submit" class="btn smallbutton" name="add">追加</button>
                            </div>
                        </div>
                        
                        <div class="col-10 row js-question hidden" id="select">
                            <div class="col-9 g-2" id="contentContainer">
                                <label class="form-label required">質問</label>
                                <input type="text" class="form-control" name="select_question">
                            </div>

                            <div class="col-3 g-2 mt-auto" id="buttonContainer">
                                <button type="submit" class="btn smallbutton" name="add">追加</button>
                            </div>
                        </div>
                    </div>

                </form>

            </div>

        </div>

    </body>

    <script src="/scripts/rides/admin/forms.js"></script>

</html>