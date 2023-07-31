<?php

include '../includes/head.php';
include '../actions/database.php';
include '../actions/users/initAdminSession.php'; ?>

<!DOCTYPE html>
<html lang="en"> 

    <body> <?php

        include '../includes/navbar.php'; ?>

        <div class="main">

            <div class="container bg-admin">
                
                <h3>投稿スケジュール</h3> 
                
                <div class="mb-2" id="scheduleContainer"></div>
            
                <h3>絶景スポットを追加</h3> <?php

                $getPrefectureData = $db->prepare('SELECT DISTINCT prefecture FROM sceneries');
                $getPrefectureData->execute();
                $prefectures = $getPrefectureData->fetchAll(PDO::FETCH_COLUMN); ?>

                <div class="mb-2" id="filteringContainer">
                    <select id="prefecture"> <?php
                        foreach ($prefectures as $prefecture) { ?>
                            <option><?= $prefecture ?></option><?php
                        } ?>
                    </select>
                </div>
                
                <div id="selectingContainer">
                </div>

            </div>

        </div>

    </body>

</html>

<script type="module" src="/scripts/admin/autoposting/sceneries.js"></script>