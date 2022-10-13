<?php
header('Content-Type: application/json, charset=UTF-8');
include $_SERVER["DOCUMENT_ROOT"] . '/includes/functions.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
session_start();

$json = file_get_contents('php://input'); // Get json file from course.js xhr request

$var = json_decode($json, true);

// Define form name
if (isset($var['edit'])) {
    $form_name = 'edit-course';
    unset($var['edit']);
} else $form_name = 'course';

// On updateSession call
if (isset($var['method'])) $_SESSION[$form_name]['method'] = $var['method'];
if (isset($var['data'])) {
    forEach ($var['data'] as $key => $data) {
        $_SESSION[$form_name][$key] = $data;
    }
    echo json_encode($_SESSION[$form_name]);
}
// On clearSession call
if (isset($var['clear'])) {
    if (isset($_SESSION[$form_name])) {
        foreach ($_SESSION[$form_name] as $key => $entry) {
            unset($_SESSION[$form_name][$key]);
        }
        $_SESSION[$form_name]['checkpoints'] = [];
        $_SESSION[$form_name]['meetingplace'] = [];
        $_SESSION[$form_name]['finishplace'] = [];
        echo json_encode($_SESSION[$form_name]);
    } else {
        $_SESSION[$form_name] = ['checkpoints', 'meetingplace', 'finishplace'];
        echo json_encode($_SESSION[$form_name]);
    }
}
// On editcaption call
if (isset($var['field'])) {
    $updateCaption = $db->prepare('UPDATE ride_checkpoints SET '. $var['field'] .' = ? WHERE ride_id = ? AND checkpoint_id = ? ');
    $updateCaption->execute(array(htmlspecialchars($var['value']), $var['ride_id'], $var['checkpoint_id']));
    echo json_encode($var);
}

        /*

        if (array_key_exists('draw-course-infos', $var)) { // route upload

            var_dump($var);

            $_SESSION['course'] = $var['course'];

        } else if (array_key_exists('options', $var)) { // Options upload

            $_SESSION['course']['options'] = $var;
    
        } else if (array_key_exists('meetingplace', $var) OR array_key_exists('finishplace', $var)) { // Meeting-place upload
    
            if (isset($var['meetingplace'])) {
                $_SESSION['course']['meetingplace'] = $var['meetingplace'];
            }
            if (isset($var['finishplace'])) {
                $_SESSION['course']['finishplace'] = $var['finishplace'];
            }
            
        } else if (array_key_exists('featuredimage', $var)) { // Featured image upload

            $_SESSION['course']['featuredimage'] = $var['checkpoint_id'];

        } else if (array_key_exists('updatecaption', $var)) { // Caption edition

            $updateCaption = $db->prepare('UPDATE ride_checkpoints SET description = ? WHERE ride_id = ? AND checkpoint_id = ? ');
            $updateCaption->execute(array(htmlspecialchars($var['caption']), $var['ride_id'], $var['checkpoint_id']));

        } else if (array_key_exists('updatename', $var)) { // Name edition

            $updateName = $db->prepare('UPDATE ride_checkpoints SET name = ? WHERE ride_id = ? AND checkpoint_id = ? ');
            $updateName->execute(array(htmlspecialchars($var['name']), $var['ride_id'], $var['checkpoint_id']));
    
        } else { 
    
            $_SESSION['course']['checkpoints'] = $var;

            for($i = 0; $i < count($var); $i++){
                if(isset($var[$i]['img'])){
                    try{
                        if($var[$i]['img_type'] !== 'image/jpeg'){
                            throw new Exception('This file is not a proper jpg file.');
                        }
                    }catch(Exception $e){
                        echo json_encode(['error' => $e->getMessage()]);
                        die();
                    }
                    $ext = strtolower(substr($var[$i]['img_name'], -3));
                    $temp = $_SERVER["DOCUMENT_ROOT"]. '/includes/media/course/temp/checkpoint' .$i. '.' .$ext; // Set temp path
                    // Temporary upload raw file on the server
                    $prefix = 'data:' .$var[$i]['img_type'].  ';base64,';
                    $base64_img = str_replace($prefix, '', $var[$i]['img']); // Remove URL data declaration prefix for allowing base64 decode
                    file_put_contents($temp, base64_decode($base64_img));
                    // Get the file into $img thanks to imagecreatefromjpeg
                    try{
                        $img = @imagecreatefromjpeg($temp);
                        if($img === false){
                            throw new Exception('An error have occured during JPEG processing.');
                        }
                    }catch(Exception $e){
                        echo json_encode(['error' => $e->getMessage()]);
                        die();
                    }
                    if(imagesx($img) > 1600){
                        $img = imagescale($img, 1600); // Only scale if img is wider than 1600px
                    }
                    // Compress it and move it into a new folder
                    $path = $_SERVER["DOCUMENT_ROOT"]. '/includes/media/course/temp/corrected_checkpoint' .$i. '.' .$ext; // Set path variable
                    if($var[$i]['img_size'] > 1000000){ // If uploaded file size exceeds 3Mb, set new quality
                        imagejpeg($img, $path, 60);
                    }else{ // If uploaded file size is between 1Mb and 3Mb set new quality
                        imagejpeg($img, $path, 80); 
                    }
                    // Get variable ready                    
                    $_SESSION['course']['checkpoints'][$i]['img'] = base64_encode(file_get_contents($path));
                    // Delete temporary files
                    // unlink($temp); unlink($path); unlink($thumbpath);  
                }
            }
        } */

