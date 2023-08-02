<?php

include '../includes/head.php';
include '../actions/database.php';
include '../actions/users/initAdminSession.php'; ?>

<!DOCTYPE html>
<html lang="en"> 
<link rel="stylesheet" href="/assets/css/autoposting.css">

    <body> <?php

        include '../includes/navbar.php'; ?>

        <div class="main">

            <div class="container bg-admin">
            
                <h3>絶景スポットを追加</h3> <?php

                $getPrefectureData = $db->prepare('SELECT DISTINCT prefecture FROM sceneries');
                $getPrefectureData->execute();
                $prefectures = $getPrefectureData->fetchAll(PDO::FETCH_COLUMN); ?>

                <div id="filteringContainer" class="autoposting-container mb-2">
                    <select id="prefecture"> <?php
                        foreach ($prefectures as $prefecture) { ?>
                            <option><?= $prefecture ?></option><?php
                        } ?>
                    </select>
                </div>
                
                <div id="selectingContainer" class="autoposting-container mb-5">
                </div>                
                
                <h3>投稿スケジュール</h3> 
                
                <div id="scheduleContainer" class="autoposting-container mb-5"></div>

            </div>

        </div>

    </body>

</html>

<script type="module" src="/scripts/admin/autoposting/sceneries.js"></script>