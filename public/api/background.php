<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/databaseAction.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['public-scenery-imgs'])) {
        $imgs_number = intval($_GET['number']);

        // Connect to blob storage
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/blobStorageAction.php';

        // Select a random image amongst [imgs_number] most popular scenery images in the database
        $getPopularSceneryImages = $db->prepare('SELECT img.id, img.filename, img.date, mkpt.name, mkpt.city, mkpt.prefecture FROM scenery_photos AS img JOIN sceneries AS mkpt ON img.scenery_id = mkpt.id ORDER BY img.likes DESC LIMIT 0, ' .$imgs_number);
        $getPopularSceneryImages->execute();
        $data = $getPopularSceneryImages->fetchAll(PDO::FETCH_ASSOC);
        $images = [];
        foreach ($data as $image) {
            $datetime = new DateTime($image['date']);
            $image['month'] = intval($datetime->format('m'));
            $image['url'] = $blobClient->getBlobUrl('scenery-photos', $image['filename']);
            array_push($images, $image);
        }
        echo json_encode($images);
    }

    if (isset($_GET['ride-imgs'])) {
        $ride = new Ride($_GET['ride-imgs']);
        $imgs_number = intval($_GET['number']);
        $images = $ride->getImages($imgs_number);
        echo json_encode($images);

    }
}

?>
 
 
 
		
		
