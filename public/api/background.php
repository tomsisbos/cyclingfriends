<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/database.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['public-scenery-imgs'])) {
        $imgs_number = intval($_GET['number']);

        // Connect to blob storage
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/blobStorage.php';

        // Select a random image amongst [imgs_number] most popular scenery images in the database
        $getPopularSceneryImages = $db->prepare("SELECT img.id, img.filename, img.date, sc.name, sc.city, sc.prefecture FROM scenery_photos AS img JOIN sceneries AS sc ON img.scenery_id = sc.id WHERE EXTRACT(MONTH FROM img.date) = EXTRACT(MONTH FROM NOW()) ORDER BY img.likes, RANDOM() DESC LIMIT {$imgs_number}");
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
        if ($ride->getCheckpoints()[0]->img->url) echo json_encode(array_slice($images, 1, count($images) - 2));
        else echo json_encode($images);
    }

    if (isset($_GET['activity-imgs'])) {
        $activity = new Activity($_GET['activity-imgs']);
        $imgs_number = intval($_GET['number']);
        $images = $activity->getPhotos($imgs_number);
        echo json_encode($images);
    }
}

?>
 
 
 
		
		
