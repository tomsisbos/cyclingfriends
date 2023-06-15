<?php 

include '../actions/users/initSessionAction.php';
include '../actions/garmin/disconnectionAction.php';
include '../actions/garmin/authentificationAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/activity.css">
<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/lightbox-style.css">

<body> <?php

    include '../includes/navbar.php';
	
    // Space for general error messages
    include '../includes/result-message.php'; ?>

    <div class="main">

        <div id="topContainer" class="container new-ac-container mb-0">
        
            <h2 class="top-title">新規アクティビティ作成</h2>

                <div class="new-ac-upload-container">
                    <div class="new-ac-upload-manual">
                        <div class="new-ac-partner-logo">
                            対応可能ファイル形式：<br>*.gpx、*.fit
                        </div>
                        <label class="btn smallbutton" for="uploadActivity">
                            <div>ファイルをアップロード</div>
                        </label>
                        <input type="file" id="uploadActivity" class="hidden" name="uploadActivity" />
                        <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
                    </div>
                    <form method="POST" class="new-ac-upload-garmin">
                        <div class="new-ac-partner-logo">
                            <img src="/media/connect_logo_blue.png"></img>
                        </div> <?php
                        if ($connected_user->getGarmin()->isUserConnected()) { ?>
                            <input class="btn smallbutton" type="submit" value="Garmin Connectと接続中（接続を解除）" name="garmin_disconnect"></input><?php
                        } else { ?>
                            <a class="btn smallbutton" href="<?= $authenticate_url ?>">Garmin Connectと同期</a><?php
                        } ?>
                    </form>
                </div>
                <div class="new-ac-upload-guidelines">
                    <h4>１．走行データのインポート</h4>
                    <p>上記の方法を用いて、まずはログデータを取り組みましょう。</p>
                    <h4>２．アクティビティの設定</h4>
                    <p>はじめに、タイトル、プライバシー設定、使用した自転車を入力します。</p>
                    <h4>３．写真のインポート</h4>
                    <p>次に、走行中に撮影した写真を取り組みましょう。</p>
                    <p>ハイライト写真や絶景スポットの作成元に使う写真を指定します。必要に応じて、写真単体のプライバシー設定も確認します。</p>
                    <h4>４．ストーリーの作成</h4>
                    <p>どこで何が起きたかなど、コース上をクリックすることで、タイムラインにストーリーの記入欄が追加されるので、走行データとストーリーを結び付けることができます。</p>
                    <h4>５．アクティビティの保存</h4>
                    <p>走行データ、写真と文書が繋がっているブログ記事のようなページが出来上がりました。あとは保存するのみ！</p>
                    <p>保存の際、必要に応じて絶景スポットの作成に関する案内ウィンドウが表示されます。</p>
                    <p>詳細については<a href="/manual/activities" target="_blank">マニュアルを参照しましょう</a>！</p>
                </div>
            
            </div>

                <div id="activityForm" style="display: none">

                    <div class="container new-ac-container">
        
                        <h3>全体情報</h3>
                        
                        <input type="text" id="inputTitle" class="form-control bold" />
                        
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
                                        <option value="public" selected>公開</option>
                                        <option value="private">非公開</option>
                                        <option value="friends_only">友達のみ</option>
                                    </select>
                                </div>
                                <div class="new-ac-inputgroup">
                                    <label class="form-label">バイク</label>
                                    <select id="selectBikes" class="form-select"> <?php
                                        $bikes = $connected_user->getBikes();
                                        if (count($bikes) > 0) foreach ($bikes as $bike_id) {
                                            $bike = new Bike($bike_id) ?>
                                            <option value="<?= $bike->id ?>"><?= $bike->model . '(' . $bike->type . ')' ?></option><?php
                                        }
                                        else echo '<option value="null" disabled>登録バイクがありません。</option>' ?>
                                    </select>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div id="activityMapContainer">
                        <div class="cf-map" id="activityMap"></div>
                        <div class="grabber"></div>
                    </div>

                    <div class="container new-ac-container">
                        <h3>写真</h3>
                        <div class="new-ac-buttons">
                            <label for="uploadPhotos">
                                <div class="btn smallbutton">追加</div>
                            </label>
                            <input type="file" id="uploadPhotos" class="hidden" name="uploadPhotos" multiple/>
                            <div class="btn smallbutton hidden" id="clearPhotos">削除</div>
                            <div class="btn smallbutton hidden" id="changePhotosPrivacy">プライバシー設定変更</div>
                            <div id="photosNumberElement">写真は付随されていません。</div>
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

            </div>

        </div>
    </div>

</body>
</html>

<script src="/scripts/map/vendor.js"></script>
<script src="/node_modules/exif-js/exif.js"></script>
<script type="module" src="/scripts/activities/new.js"></script>
<script src="/scripts/helpers/activities/new.js"></script>