<?php

if (isset($_POST['activityReport'])) {
    $ride->setReportEntry('activity_id', $_POST['id']);
    $successmessage = '「' .$ride->name. '」のアクティビティレポートを登録しました！';
}

if (isset($_POST['videoReport'])) {
    $ride->setReportEntry('video_url', htmlspecialchars($_POST['url']));
    $successmessage = '「' .$ride->name. '」のビデオレポートを登録しました！';
}

if (isset($_POST['photoReport'])) {
    $ride->setReportEntry('photoalbum_url', htmlspecialchars($_POST['url']));
    $successmessage = '「' .$ride->name. '」のフォトレポートを登録しました！';
}