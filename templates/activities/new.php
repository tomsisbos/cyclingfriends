<?php 

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/activity.css">
<link rel="stylesheet" href="/assets/css/ride.css" />

<body>

	<?php include '../includes/navbar.php'; ?>

    <div class="main">
        
        <h2 class="top-title">New activity</h2>
        
        <div class="container page">

            <div id="topContainer" class="container inner">

                <div class="new-ac-upload-container">
                    <label for="uploadActivity">
                        <div class="btn smallbutton">アップロード</div>
                    </label>
                    <input type="file" id="uploadActivity" class="hidden" name="uploadActivity" />
                    <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
                </div>
            
            </div>

                <div id="activityForm" style="display: none">

                    <div class="container inner">

                        <div class="mb-1">
                            <label class="form-label">タイトル</label>
                            <input type="text" id="inputTitle" class="form-control bold" />
                        </div>

                        <div class="new-ac-upload-photos-container">
                            <label for="uploadPhotos">
                                <div class="btn smallbutton">写真を追加する</div>
                            </label>
                            <input type="file" id="uploadPhotos" class="hidden" name="uploadPhotos" multiple/>
                            <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
                            <div class="new-ac-inputgroup">
                                <label class="form-label">バイク</label>
                                <select id="selectBikes" class="form-select"> <?php
                                    $bikes = $connected_user->getBikes();
                                    foreach ($bikes as $entry) {
                                        $bike = new Bike($entry['id']) ?>
                                        <option value="<?= $bike->id ?>"><?= $bike->model . '(' . $bike->type . ')' ?></option><?php
                                    } ?>
                                </select>
                            </div>
                            <div class="new-ac-inputgroup">
                                <label class="form-label">プライバシー設定</label>
                                <select id="selectPrivacy" class="form-select">
                                    <option value="private">非公開</option>
                                    <option value="public" selected>公開</option>
                                    <option value="friends_only">友達のみ</option>
                                </select>
                            </div>
                        </div>

                        <div class="new-ac-properties-container">
                            <div class="col-4 d-flex flex-column">
                                <div id="divStart"><strong>スタート : </strong></div>
                                <div id="divGoal"><strong>ゴール : </strong></div>
                            </div>
                            <div class="col-4 d-flex flex-column">
                                <div id="divDistance"><strong>距離 : </strong></div>
                                <div id="divDuration"><strong>時間 : </strong></div>
                                <div id="divElevation"><strong>獲得標高 : </strong></div>
                            </div>
                            <div class="col-4 d-flex flex-column">
                                <div id="divMinTemperature"><strong>最低気温 : </strong></div>
                                <div id="divAvgTemperature"><strong>平均気温 : </strong></div>
                                <div id="divMaxTemperature"><strong>最高気温 : </strong></div>
                            </div>
                        </div>
                    </div>

                    <div id="activityMapContainer">
                        <div class="cf-map" id="activityMap"></div>
                        <div class="grabber"></div>
                    </div>

                    <div id="divCheckpoints" class="container inner">
                    </div>

                    <div class="container inner">

                        <div class="new-ac-save-container">
                            <div id="saveActivity" class="btn smallbutton push">保存</div>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

</body>
</html>

<script src="/scripts/map/vendor.js"></script>
<script src="/node_modules/exif-js/exif.js"></script>
<script type="module" src="/scripts/activities/new.js"></script>