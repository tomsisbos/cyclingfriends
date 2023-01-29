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

        // Connect to blob storage
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/blobStorageAction.php';

        // Select a random image amongst [imgs_number] most popular mkpoint images in the database
        $getPopularMkpointImages = $db->prepare('SELECT img.id, img.filename, img.date, mkpt.name, mkpt.city, mkpt.prefecture FROM img_mkpoint AS img JOIN map_mkpoint AS mkpt ON img.mkpoint_id = mkpt.id ORDER BY img.likes DESC LIMIT 0, ' .$imgs_number);
        $getPopularMkpointImages->execute();
        $data = $getPopularMkpointImages->fetchAll(PDO::FETCH_ASSOC);
        $images = [];
        foreach ($data as $image) {
            $datetime = new DateTime($image['date']);
            $image['month'] = intval($datetime->format('m'));
            $image['url'] = $blobClient->getBlobUrl('scenery-photos', $image['filename']);
            array_push($images, $image);
        }
        echo json_encode($images);
    }
}

?>
 
 
 
		
		
