<?php

include '../includes/rides/admin/head.php';
include '../includes/head.php';
include '../actions/databaseAction.php';
include '../actions/rides/admin/report.php';

// If ride author is not guide, don't show guide admin page
if (!$ride->getAuthor()->isGuide()) header('location: ' .$router->generate('ride-admin', ['ride_id' => $ride->id])) ?>

<!DOCTYPE html>
<html lang="en"> 
    
    <body> <?php

        include '../includes/navbar.php'; ?>

        <div class="main rd-ad-main container-shrink"> <?php

            include '../includes/rides/admin/header.php';
            include '../includes/rides/admin/navbar.php'; ?>

            <!-- Main section -->
            <div class="container rd-ad-container">

                <h3>ライドレポート</h3>
                <p>レポートページの自動生成</p> <?php

                if (new DateTime($ride->date) >= new DateTime('now', new DateTimezone('Asia/Tokyo'))) echo '<div class="text-center">ライドが終わったら、こちらでライドレポートの設定ができるようになります。</div>';

                else { ?>

                    <div class="rd-ad-section">

                        <h4>アクティビティレポート</h4>

                        <div class="rd-ad-form mb-3">
                            ガイドのアクティビティの中から、レポート元となるアクティビティを選択してください。<?php

                            // If an activity report has been selected for this ride, display it
                            if (isset($ride->getReport()->activity_id)) { ?>
                                <div class="rd-ad-report-container">
                                    <?= $ride->getReport()->getActivity()->title ?>
                                </div><?php
                            } ?>

                            <form method="POST" id="activityReport" class="d-flex gap">
                                <select name="selectGuide" class="form-select d-flex" id="selectGuide">
                                    <option value="none" selected disabled>ガイドを選択...</option> <?php
                                    foreach ($ride->getGuides() as $registered_guide) { ?>
                                        <option value="<?= $registered_guide->id ?>"><?= $registered_guide->login ?></option><?php
                                    } ?>                        
                                </select>
                                <div id="selectActivitiesContainer" class="d-flex gap"></div>
                            </form>
                        </div>
                        
                    </div>

                    <div class="rd-ad-section">
                        
                        <h4>フォトレポート</h4>

                        <div class="rd-ad-form mb-3">
                            Google PhotosアルバムのURLを記入してください。
                            <form method="POST" id="photoReport" class="d-flex gap">
                                <input type="text" name="url" class="form-control" value="<?php
                                    if (isset($ride->getReport()->photoalbum_url)) echo $ride->getReport()->photoalbum_url;
                                    else echo 'https://photos.app.goo.gl/' ?>
                                "></input>
                                <input type="submit" name="photoReport" class="btn smallbutton" value="確定"/>
                            </form>
                        </div>
                        
                    </div>

                    <div class="rd-ad-section">
                        
                        <h4>ビデオレポート</h4>

                        <div class="rd-ad-form mb-3"><?php
                            if (isset($ride->getReport()->video_url)) {
                                echo $ride->getReport()->getVideoIframe();
                            } ?>
                            Youtube動画のURLを記入してください。
                            <form method="POST" id="videoReport" class="d-flex gap">
                                <input type="text" name="url" class="form-control" id="floatingVideoInput" value="<?php
                                    if (isset($ride->getReport()->video_url)) echo $ride->getReport()->video_url;
                                    else echo 'https://www.youtube.com/' ?>
                                "></input>
                                <input type="submit" name="videoReport" class="btn smallbutton" value="確定"/>
                            </form>
                        </div>
                        
                    </div><?php

                } ?>

            </div>

        </div>

    </body>

</html>

<script type="module" src="/scripts/rides/admin/report.js"></script>