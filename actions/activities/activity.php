<?php
 
	require '../actions/database.php';
	
	// Get id from URL
	$slug = basename($_SERVER['REQUEST_URI']);
	if (is_numeric($slug)) {

		// Check if activity exists and access is allowed
		$checkIfActivityExists = $db->prepare("SELECT a.title FROM activities as A JOIN friends AS fr ON a.user_id = fr.receiver_id JOIN friends as fi ON a.user_id = fi.inviter_id WHERE a.id = :activity_id AND a.privacy != 'private' AND (a.privacy = 'friends_only' AND ((:connected_user_id = fi.receiver_id AND fi.accepted = 1) OR (:connected_user_id = fr.inviter_id AND fr.accepted = 1))) != true");
		$checkIfActivityExists->execute([':activity_id' => $slug, ':connected_user_id' => getConnectedUser()->id]);
		if ($checkIfActivityExists->rowCount() > 0) {
			
			$activity_id = $slug;
			$activity_title = $checkIfActivityExists->fetch(PDO::FETCH_COLUMN);

			// Get featured image url
			$getFeaturedImageFilename = $db->prepare("SELECT filename FROM activity_photos WHERE activity_id = ? AND featured = 1");
			$getFeaturedImageFilename->execute([$activity_id]);
			if ($getFeaturedImageFilename->rowCount() > 0) $activity_featured_image_filename = $getFeaturedImageUrl->fetch(PDO::FETCH_COLUMN);
			else {
				$getFirstImageFilename = $db->prepare("SELECT filename FROM activity_photos WHERE activity_id = ?");
				$getFirstImageFilename->execute([$activity_id]);
				if ($getFirstImageFilename->rowCount() > 0) $activity_featured_image_filename = $getFirstImageFilename->fetch(PDO::FETCH_COLUMN);
			}

			// If an image to feature could have been found
            if (isset($activity_featured_image_filename)) {
				$root_folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
				require $root_folder . '/actions/blobStorage.php';
				$activity_featured_image_url = $blobClient->getBlobUrl('activity-photos', $activity_featured_image_filename);
			}


		// If id doesn't exist, redirect to myactivities.php
		} else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/activities');
	
	// If id is not set, redirect to myactivities.php
	} else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/activities');
	
?>