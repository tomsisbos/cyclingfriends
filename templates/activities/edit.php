<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php';
include '../actions/activities/getActivityAction.php';

if (getConnectedUser()->id != $activity->user_id) header('location: /' .getConnectedUser()->login. '/activities') ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/activity.css">
<link rel="stylesheet" href="/assets/css/lightbox-style.css">

<body>

	<?php include '../includes/navbar.php'; ?>

    <h2 class="top-title">Edit activity</h2>

    <div class="main" id="activityForm">

        <div class="container new-ac-container">
        
            <h3>全体情報</h3>
            
            <input type="text" id="inputTitle" class="form-control bold" value="<?= $activity->title ?>" />

            <div class="new-ac-columns pt-0">
                <div class="new-ac-part">
                    <div class="new-ac-part-line">
                        <div id="divStart"><strong>スタート : </strong></div>
                        <div id="divGoal"><strong>ゴール : </strong></div>
                    </div>
                    <div class="new-ac-part-columns">
                        <div class="new-ac-part-line">
                            <div id="divDistance"><strong>距離 : </strong></div>
                            <div id="divDuration"><strong>時間 : </strong></div>
                            <div id="divElevation"><strong>獲得標高 : </strong></div>
                        </div>
                        <div class="new-ac-part-line">
                            <div id="divMinTemperature"><strong>最低気温 : </strong></div>
                            <div id="divAvgTemperature"><strong>平均気温 : </strong></div>
                            <div id="divMaxTemperature"><strong>最高気温 : </strong></div>
                        </div>
                    </div>
                </div>
                <div class="new-ac-columns">
                    <div class="new-ac-inputgroup">
                        <label class="form-label">プライバシー設定</label>
                        <select id="selectPrivacy" class="form-select">
                            <option value="private" <?php if ($activity->privacy == 'private') echo 'selected'; ?>>非公開</option>
                            <option value="public" <?php if ($activity->privacy == 'public') echo 'selected'; ?>>公開</option>
                            <option value="friends_only" <?php if ($activity->privacy == 'friends_only') echo 'selected'; ?>>友達のみ</option>
                        </select>
                    </div>
                    <div class="new-ac-inputgroup">
                        <label class="form-label">バイク</label>
                        <select id="selectBikes" class="form-select"> <?php
                            $bikes = getConnectedUser()->getBikes();
                            if (count($bikes) > 0) foreach ($bikes as $bike_id) {
                                $bike = new Bike($bike_id) ?>
                                <option value="<?= $bike->id ?>" <?php if ($activity->bike == $bike_id) echo 'selected'; ?>><?= $bike->model . ' (' . $bike->type . ')' ?></option><?php
                            }
                            else echo '<option value="null" disabled>登録バイクがありません。</option>' ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="container p-0"> <?php
            include '../includes/activities/map.php' ?>
        </div>

        <div class="container new-ac-container">
            <h3>写真</h3>
            <div id="photosNumberElement">写真は付随されていません。</div>
            <div class="new-ac-buttons">
                <label for="uploadPhotos">
                    <div class="btn smallbutton">追加</div>
                </label>
                <input type="file" id="uploadPhotos" class="hidden" name="uploadPhotos" multiple/>
                <div class="btn smallbutton hidden" id="clearPhotos">削除</div>
                <div class="btn smallbutton hidden" id="changePhotosPrivacy">プライバシー設定変更</div>
            </div>
        </div>

        <div class="container">
        
            <h3>ストーリー</h3>

            <div id="divCheckpoints" style="margin: 0px"></div>
        
        </div>

        <div class="container">
            <div class="new-ac-save-container">
                <div id="saveActivity" class="btn smallbutton push">保存</div>
            </div>
        </div>
    </div>

</body>
</html>

<script src="/scripts/vendor.js"></script>
<script src="/node_modules/exif-js/exif.js"></script>
<script type="module" src="/scripts/activities/edit.js"></script>