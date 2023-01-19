<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/class/Autoloader.php'; 
Autoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/databaseAction.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['get-background-imgs'])) {
        $imgs_number = intval($_GET['get-background-imgs']);
        // Select a random image amongst 30 most popular mkpoint images in the database
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $getPopularMkpointImages = $db->prepare('SELECT * FROM img_mkpoint ORDER BY likes DESC LIMIT ?');
        $getPopularMkpointImages->execute(array($imgs_number));
        $mkpoint_images = $getPopularMkpointImages->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        for ($i = 0; $i < count($mkpoint_images); $i++) {
            $data[$i] = new MkpointImage($mkpoint_images[$i]['id']);
            $data[$i]->mkpoint = new Mkpoint($mkpoint_images[$i]['mkpoint_id']);
        }
        echo json_encode($data);
    }
}

?>
 
 
 
		
		
