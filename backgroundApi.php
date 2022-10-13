<?php
// Autoload
require_once $_SERVER["DOCUMENT_ROOT"] . '/class/Autoloader.php'; 
Autoloader::register();
require $_SERVER["DOCUMENT_ROOT"] . '/includes/functions.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['get-background-imgs'])) {
        $imgs_number = intval($_GET['get-background-imgs']);
        // Select a random image amongst 30 most popular mkpoint images in the database
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $getPopularMkpointImages = $db->prepare('SELECT * FROM img_mkpoint ORDER BY likes, id DESC LIMIT ?');
        $getPopularMkpointImages->execute(array($imgs_number));
        $mkpoint_images = $getPopularMkpointImages->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($mkpoint_images); $i++) {
            $mkpoint_images[$i] = new MkpointImage($mkpoint_images[$i]['id']);
        }
        echo json_encode($mkpoint_images);
    }
}

?>
 
 
 
		
		
