<?php

session_start();
include 'includes/head.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php'; 

      
      
      /*
      $getMapMkpointsData = $db->prepare("SELECT id, user_id, user_login, date, month, period, file_blob, file_size, file_name, file_type FROM map_mkpoint");
      $getMapMkpointsData->execute();
      $mkpoints = $getMapMkpointsData->fetchAll(PDO::FETCH_ASSOC);
      var_dump($mkpoints);
      
      forEach($mkpoints as $mkpoint){
            $sendDataToImgMkpoints = $db->prepare("INSERT INTO img_mkpoint (mkpoint_id, user_id, user_login, date, month, period, file_blob, file_size, file_name, file_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sendDataToImgMkpoints->execute(array($mkpoint['id'], $mkpoint['user_id'], $mkpoint['user_login'], $mkpoint['date'], $mkpoint['month'], $mkpoint['period'], $mkpoint['file_blob'], $mkpoint['file_size'], $mkpoint['file_name'], $mkpoint['file_type']));
      }
      */
      
      /*
      $getMkpointBlobs = $db->prepare("SELECT id, file_blob FROM map_mkpoint");
      $getMkpointBlobs->execute();
      $mkpointBlobs = $getMkpointBlobs->fetchAll(PDO::FETCH_ASSOC);
      var_dump($mkpointBlobs);
      
      foreach($mkpointBlobs as $blob){
            $path = $_SERVER["DOCUMENT_ROOT"]. '/includes/media/map/blob_compress/mkp' .$blob['id']. '_thumb.jpg';
            file_put_contents($path, base6_decode($blob['file_blob']));
            $thumbnail = scaleImageFileToBlob($path, 8, 6);
            $updateThubmnail = $db->prepare('UPDATE map_mkpoint SET thumbnail = ? WHERE id = ?');
            $updateThubmnail->execute(array(base6_encode($thumbnail), $blob['id']));
      }
      */
      
      
      
      ?>
      
      