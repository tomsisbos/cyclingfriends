<!DOCTYPE html>
<html lang="en"> <?php

include '../includes/rides/admin/head.php'; ?>

    <body> <?php if (isset($_POST)) var_dump($_POST);

        include '../includes/navbar.php'; ?>

        <div class="main container-shrink"> <?php

            include '../includes/rides/admin/header.php';
            
            include '../includes/rides/admin/navbar.php' ?>

            <!-- Main section -->
            <div class="container end">

                <h3>参加者質問設定</h3>
                <p>エントリーの際にユーザーから集める追加情報欄の設定</p>

                
                <form method="POST">

                    <div class="mb-3 row g-2">
                        <div class="col-2">
                            <label class="form-label required">形式</label>
                            <select class="form-select" name="type[]">
                                <option value="text">記入式</option>
                                <option value="select">選択式</option>
                            </select>
                        </div>
                        
                        <div class="col-8">
                            <label class="form-label required">質問</label>
                            <input type="text" class="form-control" name="question">
                        </div>

                        <div class="col-2 mt-auto">
                            <button type="submit" class="btn smallbutton" name="add">追加</button>
                        </div>
                    </div>

                </form>

            </div>

        </div>

    </body>

</html>