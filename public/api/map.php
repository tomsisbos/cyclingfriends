<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['get-session'])) {
        echo json_encode($_SESSION);
    }

    // In case a 'saveMkpoint' index have been detected
    if (isset($_POST['saveMkpoint']) AND !empty($_POST['saveMkpoint'])) {

        try {

            if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
                throw new Exception('A problem have occured during file upload.');
            } else {
                // Get extension from file name
                $ext = strtolower(substr($_FILES['file']['name'], -3));

                if ($_FILES['file']['error'] == 2) { // If error is file_exceed_limit
                    throw new Exception('The file you uploaded exceeds size limit (10Mb). Please reduce the size and try again.');
                } else if (!getimagesize($_FILES["file"]["tmp_name"])) {
                    throw new Exception('The file you uploaded is not an image file.');
                } else if (!isset(exif_read_data($_FILES['file']['tmp_name'], 0, true)['EXIF']['DateTimeOriginal'])) { // If image header doesn't contain DateTimeOriginal
                    throw new Exception('This file is not a raw photography taken with a camera device.');
                }
                
                // Preparing variables
                $mkpoint['user_id']          = $_SESSION['id'];
                $mkpoint['user_login']       = $_SESSION['login'];
                $mkpoint['category']         = 'marker';
                $mkpoint['name']             = htmlspecialchars($_POST['name']);
                $mkpoint['city']             = $_POST['city'];
                $mkpoint['prefecture']       = $_POST['prefecture'];
                $mkpoint['elevation']        = $_POST['elevation'];
                $mkpoint['date']             = exif_read_data($_FILES['file']['tmp_name'], 0, true)['EXIF']['DateTimeOriginal'];
                $mkpoint['month']            = date("n", strtotime(exif_read_data($_FILES['file']['tmp_name'], 0, true)['EXIF']['DateTimeOriginal']));
                $mkpoint['period']           = getPeriod($mkpoint['date']);
                $mkpoint['description']      = htmlspecialchars($_POST['description']);
                $mkpoint['tags']             = explode(",", $_POST['tags']);
                $mkpoint['file_size']        = $_FILES['file']['size'];
                $mkpoint['file_name']        = $_FILES['file']['name'];
                $mkpoint['file_type']        = $_FILES['file']['type'];
                $mkpoint['lng']              = $_POST['lng'];
                $mkpoint['lat']              = $_POST['lat'];
                $mkpoint['publication_date'] = date('Y-m-d H:i:s');
                $mkpoint['error']            = $_FILES['file']['error'];
                $mkpoint['popularity']       = 30;

                /* Photo treatment */
                $img_name = 'temp.'.$ext; // Set image name
                $temp = $temp = $_SERVER["DOCUMENT_ROOT"]. '/map/media/temp/' .$img_name; // Set temp path
                // Temporary upload raw file on the server
                move_uploaded_file($_FILES['file']['tmp_name'], $temp);
                // Get the file into $img thanks to imagecreatefromjpeg
                $img = imagecreatefromjpegexif($temp);
                // Only scale if img is wider than 1600px
                if (imagesx($img) > 1600) $img = imagescale($img, 1600);
                // Correct image gamma and contrast
                imagegammacorrect($img, 1.0, 1.1);
                imagefilter($img, IMG_FILTER_CONTRAST, -5);
                // Compress it and move it into a new folder
                $path = $_SERVER["DOCUMENT_ROOT"]. '/map/media/temp/photo_' .$img_name; // Set path variable
                // If uploaded file size exceeds 3Mb, set new quality to 15
                if ($_FILES['file']['size'] > 3000000) imagejpeg($img, $path, 75);
                // If uploaded file size is between 1Mb and 3Mb set new quality to 30
                else imagejpeg($img, $path, 90);
                // Get variable ready
                $mkpoint['file_blob'] = base64_encode(file_get_contents($path));
                
                /* Thumbnail treatment */
                // Get image and scale it to thumbnail size
                $thumbnail = imagecreatefromjpegexif($path);
                $thumbnail = imagescale($thumbnail, 48, 36);
                // Correct image gamma and contrast
                imagegammacorrect($thumbnail, 1.0, 1.275);
                imagefilter($thumbnail, IMG_FILTER_CONTRAST, -12);
                $thumbpath = $_SERVER["DOCUMENT_ROOT"]. '/map/media/temp/thumb_' .$img_name; // Set path variable
                imagejpeg($thumbnail, $thumbpath);
                // Get variable ready
                $mkpoint['thumbnail'] = base64_encode(file_get_contents($thumbpath));

                // Delete temporary files
                unlink($temp); unlink($path); unlink($thumbpath);
            }
        
        // If any exception have been catched, response the error message set in the exception
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            die();
        }

        // If everything went fine, response the mkpoint data
        echo json_encode($mkpoint);
        
        // Check if there is an existing index with the same lng and lat (or less than a 0,001 difference) in the database
        $checkLngLat = $db->prepare('SELECT id, lng, lat FROM map_mkpoint WHERE ROUND(lng, 3) = ? AND ROUND(lat, 3) = ?');
        $checkLngLat->execute(array(round($mkpoint['lng'], 3), round($mkpoint['lat'], 3)));
        // If there is one, update it
        if ($checkLngLat->rowCount() > 0) {
            $isMkpoint = $checkLngLat->fetch();
            $updateMapMkpoint = $db->prepare('UPDATE map_mkpoint SET user_id = ?, user_login = ?, category = ?, name = ?, city = ?, prefecture = ?, elevation = ?, date = ?, month = ?, period = ?, description = ?, thumbnail = ?, popularity = ? WHERE ROUND(lng, 3) = ROUND(?, 3) AND ROUND(lat, 3) = ROUND(?, 3)');
            $updateMapMkpoint->execute(array($mkpoint['user_id'], $mkpoint['user_login'], $mkpoint['category'], $mkpoint['name'], $mkpoint['city'], $mkpoint['prefecture'], $mkpoint['elevation'], $mkpoint['date'], $mkpoint['month'], $mkpoint['period'], $mkpoint['description'], $mkpoint['thumbnail'], $mkpoint['popularity'], $mkpoint['lng'], $mkpoint['lat']));
            $updateImgMkpoint = $db->prepare('UPDATE img_mkpoint SET user_id = ?, user_login = ?, date = ?, month = ?, period = ?, file_blob = ?, file_size = ?, file_name = ?, file_type = ? WHERE mkpoint_id = ?');
            $updateImgMkpoint->execute(array($mkpoint['user_id'], $mkpoint['user_login'], $mkpoint['date'], $mkpoint['month'], $mkpoint['period'], $mkpoint['file_blob'], $mkpoint['file_size'], $mkpoint['file_name'], $mkpoint['file_type'], $isMkpoint['id']));
        // Else, create it
        } else {
		    $insertMapMkpoint = $db->prepare('INSERT INTO map_mkpoint (user_id, user_login, category, name, city, prefecture, elevation, date, month, period, description, thumbnail, popularity, lng, lat, publication_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		    $insertMapMkpoint->execute(array($mkpoint['user_id'], $mkpoint['user_login'], $mkpoint['category'], $mkpoint['name'], $mkpoint['city'], $mkpoint['prefecture'], $mkpoint['elevation'], $mkpoint['date'], $mkpoint['month'], $mkpoint['period'], $mkpoint['description'], $mkpoint['thumbnail'], $mkpoint['popularity'], $mkpoint['lng'], $mkpoint['lat'], $mkpoint['publication_date']));
            $getMkpointId = $db->prepare('SELECT id FROM map_mkpoint WHERE ROUND(lng, 3) = ? AND ROUND(lat, 3) = ?');
            $getMkpointId->execute(array(round($mkpoint['lng'], 3), round($mkpoint['lat'], 3)));
            $mkpointId = $getMkpointId->fetch();
            $insertImgMkpoint = $db->prepare('INSERT INTO img_mkpoint (mkpoint_id, user_id, user_login, date, month, period, file_blob, file_size, file_name, file_type, likes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insertImgMkpoint->execute(array($mkpointId['id'], $mkpoint['user_id'], $mkpoint['user_login'], $mkpoint['date'], $mkpoint['month'], $mkpoint['period'], $mkpoint['file_blob'], $mkpoint['file_size'], $mkpoint['file_name'], $mkpoint['file_type'], 0));
        }

        // Insert tags data
        $checkLngLat->execute(array(round($mkpoint['lng'], 3), round($mkpoint['lat'], 3)));
        $mkpoint_data = $checkLngLat->fetch(PDO::FETCH_ASSOC);
        if (!empty($mkpoint['tags'][0])) {
            foreach ($mkpoint['tags'] as $tag) {
                $insertTag = $db->prepare('INSERT INTO tags (object_type, object_id, tag) VALUES (?, ?, ?)');
                $insertTag->execute(array('scenery', $mkpoint_data['id'], $tag));
            }
        }
    }

    if (isset($_POST['addphoto-button-form']) AND !empty($_POST['addphoto-button-form'])) {
                
        try {

            if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
                throw new Exception('A problem have occured during file upload.');
            } else {
                // Get extension from file name
                $ext = strtolower(substr($_FILES['file']['name'], -3));

                if ($_FILES['file']['error'] == 2) { // If error is file_exceed_limit
                    throw new Exception('The file you uploaded exceeds size limit (10Mb). Please reduce the size and try again.');
                } else if (!getimagesize($_FILES["file"]["tmp_name"])) {
                    throw new Exception('The file you uploaded is not an image file.');
                } else if (@!isset(exif_read_data($_FILES['file']['tmp_name'], 0, true)['EXIF']['DateTimeOriginal'])) { // If image header doesn't contain DateTimeOriginal
                    throw new Exception('This file is not a raw photography taken with a camera device.');
                }
                
                // Preparing variables
                $user = new User($_SESSION['id']);
                $mkpointimg['mkpoint_id']  = $_POST['mkpoint_id'];
                $mkpointimg['user_id']     = $user->id;
                $mkpointimg['user_login']  = $user->login;
                $mkpointimg['date']        = exif_read_data($_FILES['file']['tmp_name'], 0, true)['EXIF']['DateTimeOriginal'];
                $mkpointimg['month']       = date("n", strtotime(exif_read_data($_FILES['file']['tmp_name'], 0, true)['EXIF']['DateTimeOriginal']));
                $mkpointimg['period']      = getPeriod($mkpointimg['date']);
                $mkpointimg['file_size']   = $_FILES['file']['size'];
                $mkpointimg['file_name']   = $_FILES['file']['name'];
                $mkpointimg['file_type']   = $_FILES['file']['type'];
                $mkpointimg['error']       = $_FILES['file']['error'];

                /* Photo treatment */
                $img_name = 'img.'.$ext; // Set image name
                $temp = $_SERVER["DOCUMENT_ROOT"]. '/map/media/temp/' .$img_name; // Set temp path
                // Temporary upload raw file on the server
                move_uploaded_file($_FILES['file']['tmp_name'], $temp);
                // Get the file into $img thanks to imagecreatefromjpeg
                $img = imagecreatefromjpegexif($temp);
                $img = imagescale($img, 1600, 900);
                // Correct image gamma and contrast
                imagegammacorrect($img, 1.0, 1.1);
                imagefilter($img, IMG_FILTER_CONTRAST, -5);
                // Compress it and move it into a new folder
                $path = $_SERVER["DOCUMENT_ROOT"]. '/map/media/temp/photo_' .$img_name; // Set path variable
                if ($_FILES['file']['size'] > 3000000) { // If uploaded file size exceeds 3Mb, set new quality
                    imagejpeg($img, $path, 75);
                } else { // If uploaded file size is between 1Mb and 3Mb set new quality
                    imagejpeg($img, $path, 90); 
                }
                // Get variable ready
                $mkpointimg['file_blob'] = base64_encode(file_get_contents($path));
                
                // Delete temporary files
                unlink($temp); unlink($path);

            }
        
        // If any exception have been catched, response the error message set in the exception
        } catch(Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            die();
        }

        // Check if the same image have already been uploaded
        $checkImg = $db->prepare('SELECT date, file_size FROM img_mkpoint WHERE mkpoint_id = ?');
        $checkImg->execute(array($mkpointimg['mkpoint_id']));
        $photos = $checkImg->fetchAll(PDO::FETCH_ASSOC);
        $isAlreadyUploaded = false;
        foreach ($photos as $photo) {
            if (strtotime($photo['date']) == strtotime($mkpointimg['date']) && $photo['file_size'] == $mkpointimg['file_size']) {
                $isAlreadyUploaded = true;
            }
        }
        
        // If not, insert image in the database img_mkpoint table and send response
        if ($isAlreadyUploaded != true) {
            $insertMkpoint = $db->prepare('INSERT INTO img_mkpoint(mkpoint_id, user_id, user_login, date, month, period, file_blob, file_size, file_name, file_type, likes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insertMkpoint->execute(array($mkpointimg['mkpoint_id'], $mkpointimg['user_id'], $mkpointimg['user_login'], $mkpointimg['date'], $mkpointimg['month'], $mkpointimg['period'], $mkpointimg['file_blob'], $mkpointimg['file_size'], $mkpointimg['file_name'], $mkpointimg['file_type'], 0));    
            echo json_encode($mkpointimg);
        } else {
            echo json_encode(['error' => 'This image have already been uploaded.']);
        }
		
    }

    if (isset($_GET['mkpoint-photos'])) {
        $mkpoint = new Mkpoint($_GET['mkpoint-photos']);
        echo json_encode($mkpoint->getImages());
    }

    if (isset($_GET['mkpoint-closest-photo'])) { // Get photo whose period is the soonest
        $getMkpointPhoto = $db->prepare('SELECT * FROM img_mkpoint WHERE mkpoint_id = ? AND month > ? ORDER BY month ASC');
        $getMkpointPhoto->execute([$_GET['mkpoint-closest-photo'], date('m')]);
        if ($getMkpointPhoto->rowCount() == 0) {
            $getMkpointPhoto = $db->prepare('SELECT * FROM img_mkpoint WHERE mkpoint_id = ? ORDER BY month DESC');
            $getMkpointPhoto->execute([$_GET['mkpoint-closest-photo']]);
        }
        $mkpointphoto = $getMkpointPhoto->fetch(PDO::FETCH_ASSOC);
        echo json_encode($mkpointphoto);
    }

    if (isset($_GET['getpropic'])) {
        if (is_numeric($_GET['getpropic'])) $user = new User($_GET['getpropic']);
        else $user = $connected_user;
        $profile_picture_src = $user->getPropicSrc();
        echo json_encode([$profile_picture_src]);
    }

    if (isset($_GET['display-mkpoints'])) {
        $getMkpoints = $db->prepare('SELECT id FROM map_mkpoint ORDER BY popularity, rating, grades_number DESC, elevation ASC');
        $getMkpoints->execute();
        $result = $getMkpoints->fetchAll(PDO::FETCH_ASSOC);
        $mkpoints = [];
        foreach ($result as $mkpoint_data) {
            $mkpoint = new Mkpoint($mkpoint_data['id']);
            $mkpoint->isFavorite = $mkpoint->isFavorite();
            $mkpoint->isCleared = $mkpoint->isCleared();
            array_push($mkpoints, $mkpoint);
        }
        echo json_encode($mkpoints);
    }

    if (isset($_GET['mkpoint'])) {
        $getMkpoint = $db->prepare('SELECT id FROM map_mkpoint WHERE id = ?');
        $getMkpoint->execute(array($_GET['mkpoint']));
        if ($getMkpoint->rowCount() > 0) {
            $mkpoint = new Mkpoint($_GET['mkpoint']);
            echo json_encode(['data' => $mkpoint, 'photos' => $mkpoint->getImages()]);
        } else echo json_encode(['error' => 'There is no mkpoint corresponding to this id.']);
    }

    if (isset($_GET['display-mkpoints-list'])) {
        $querystring = "SELECT * FROM map_mkpoint WHERE id IN (" .$_GET['display-mkpoints-list']. ")";
        $getMkpoints = $db->prepare($querystring);
        $getMkpoints->execute();
        $mkpointsList = $getMkpoints->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($mkpointsList);
    }

    if (isset($_GET['get-close-mkpoints'])) {
        $route = new Route($_GET['get-close-mkpoints']);
        $close_mkpoints = $route->getCloseMkpoints();
        echo json_encode($close_mkpoints);
    }

    if (isset($_GET['mkpoint-dragged'])) {
        if (isset($_GET['lng']) && isset($_GET['lat'])) {
            $mkpoint_id  = $_GET['mkpoint-dragged'];
            $mkpoint_lng = $_GET['lng'];
            $mkpoint_lat = $_GET['lat'];
            $updateMkpointLngLat = $db->prepare('UPDATE map_mkpoint SET lng = ?, lat = ? WHERE id = ?');
            $updateMkpointLngLat->execute(array($mkpoint_lng, $mkpoint_lat, $mkpoint_id));
            echo json_encode([$_GET['lng'], $_GET['lat']]);
        }
    }

    if (isset($_GET['islike-img'])) {
        $img_id = $_GET['islike-img'];
        $checkIfUserHasAlreadyGivenALike = $db->prepare('SELECT * FROM islike_mkpoint WHERE user_id = ? AND img_id = ?');
        $checkIfUserHasAlreadyGivenALike->execute(array($_SESSION['id'], $img_id));
        if ($checkIfUserHasAlreadyGivenALike->rowcount() > 0) $islike = true;
        else $islike = false;
        echo json_encode($islike);
    }

    if (isset($_GET['togglelike-img'])) {
        // Get all mkpoint infos
        $img = new MkpointImage($_GET['togglelike-img']);

        // Get mkpoint id
        $getMkpointId = $db->prepare('SELECT mkpoint_id FROM img_mkpoint WHERE id = ?');
        $getMkpointId->execute([$_GET['togglelike-img']]);
        $mkpoint_id = $getMkpointId->fetch(PDO::FETCH_NUM)[0];
        $mkpoint = new Mkpoint($mkpoint_id);

        // Check if user has already given a like
        $checkIfUserHasAlreadyGivenALike = $db->prepare('SELECT * FROM islike_mkpoint WHERE user_id = ? AND img_id = ?');
        $checkIfUserHasAlreadyGivenALike->execute(array($connected_user->id, $img->id));

        // If user has already liked
        if ($checkIfUserHasAlreadyGivenALike->rowcount() > 0) {
            // Decrease likes in img_mkpoint table
            $removeLikeFromImg = $db->prepare('UPDATE img_mkpoint SET likes = likes - 1 WHERE id = ?');
            $removeLikeFromImg->execute(array($img->id));
            // Decrease likes and popularity in map_mkpoint table
            $removeLikeFromMkpoint = $db->prepare('UPDATE map_mkpoint SET likes = likes - 1, popularity = popularity - 5 WHERE id = ?');
            $removeLikeFromMkpoint->execute(array($mkpoint->id));
            // Remove corresponding entry in islike_mkpoint table
            $removeEntryFromIslikeMkpointTable = $db->prepare('DELETE FROM islike_mkpoint WHERE user_id = ? AND img_id = ?');
            $removeEntryFromIslikeMkpointTable->execute(array($connected_user->id, $img->id));
            $is_given_point = false;

        // If user has not liked yet
        } else {
            // Increase likes in img_mkpoint table
            $addLikeFromImg = $db->prepare('UPDATE img_mkpoint SET likes = likes + 1 WHERE id = ?');
            $addLikeFromImg->execute(array($img->id));
            // Increase likes and popularity in map_mkpoint table
            $addLikeFromMkpoint = $db->prepare('UPDATE map_mkpoint SET likes = likes + 1, popularity = popularity + 5 WHERE id = ?');
            $addLikeFromMkpoint->execute(array($mkpoint->id));
            // Add corresponding entry in islike_mkpoint table
            $setEntryInIslikeMkpointTable = $db->prepare('INSERT INTO islike_mkpoint(user_id, img_id) VALUES (?, ?)');
            $setEntryInIslikeMkpointTable->execute(array($connected_user->id, $img->id));
            $is_given_point = true;
        }
        // Update mkpoint infos
        $updated_img     = new MkpointImage($_GET['togglelike-img']);
        $updated_mkpoint = new Mkpoint($mkpoint_id);

        echo json_encode(['islike' => $is_given_point, 'imgLikes' => $updated_img->likes, 'mkpointLikes' => $updated_mkpoint->likes]);
    }

    if (isset($_GET['edit-mkpoint'])) {
        $mkpoint_id          = $_GET['edit-mkpoint'];
        $mkpoint_name        = $_GET['name'];
        $mkpoint_description = $_GET['description'];
        // Update mkpoint data
        $removeMkpoint = $db->prepare('UPDATE map_mkpoint SET name = ?, description = ? WHERE id = ?');
        $removeMkpoint->execute(array($mkpoint_name, $mkpoint_description, $mkpoint_id));
        echo json_encode(['id' => $mkpoint_id, 'name' =>  $mkpoint_name, 'description' => $mkpoint_description]);
    }

    if (isset($_GET['delete-mkpoint'])) {
        $mkpoint = new Mkpoint($_GET['delete-mkpoint']);
        // Remove mkpoint data
        $removeMkpoint = $db->prepare('DELETE FROM map_mkpoint WHERE id = ?');
        $removeMkpoint->execute(array($mkpoint->id));
        // Remove photo data
        $removeMkpointPhotos = $db->prepare('DELETE FROM img_mkpoint WHERE mkpoint_id = ?');
        $removeMkpointPhotos->execute(array($mkpoint->id));
        // Remove tags data
        $removeMkpointPhotos = $db->prepare('DELETE FROM tags WHERE object_type = ? AND object_id = ?');
        $removeMkpointPhotos->execute(array('scenery', $mkpoint->id));
        echo json_encode([$mkpoint->id]);
    }

    // Delete one photo from a mkpoint
    if (isset($_GET['delete-photo-mkpoint'])) {
        $mkpoint  = new Mkpoint($_GET['delete-photo-mkpoint']);
        $photo_id = $_GET['photo'];
        $removeMkpointPhoto = $db->prepare('DELETE FROM img_mkpoint WHERE id = ? AND mkpoint_id = ?');
        $removeMkpointPhoto->execute(array($photo_id, $mkpoint->id));
        echo json_encode(['mkpoint_id' => $mkpoint->id, 'photo_id' => $photo_id]);

    }

    if (isset($_GET['get-rating'])) {
        if ($_GET['type'] == 'mkpoint') {
            $object = new Mkpoint($_GET['id']);
            $table = "map_mkpoint";
        } else if ($_GET['type'] == 'segment') {
            $object = new Segment($_GET['id']);
            $table = "segments";
        }
        // Get rating info
        $checkRating = $db->prepare("SELECT rating, grades_number FROM {$table} WHERE id = ?");
        $checkRating->execute(array($object->id));
        $rating_infos = $checkRating->fetch(PDO::FETCH_ASSOC);
        // Add user vote info
        $vote = $object->getUserVote($connected_user);
        $rating_infos['vote'] = $vote;
        echo json_encode($rating_infos);
    }

    if (isset($_GET['check-user-vote'])) {
        $mkpoint = new Mkpoint($_GET['check-user-vote']);
        $user    = new User($_GET['user_id']);
        $vote    = $mkpoint->getUserVote($user);
        echo json_encode($vote);
    }

    if (isset($_GET['set-rating'])) {
        if ($_GET['type'] == 'mkpoint') {
            $object       = new Mkpoint($_GET['id']);
            $table        = "map_mkpoint";
            $grades_table = "grade_mkpoint";
        } else if ($_GET['type'] == 'segment') {
            $object       = new Segment($_GET['id']);
            $table        = "segments";
            $grades_table = "segment_grade";
        }
        $id_entry = $_GET['type'] . "_id";
        $grade    = $_GET['grade'];

        // Add or update grade to table
        $checkIfUserAlreadyRated = $db->prepare("SELECT grade FROM {$grades_table} WHERE {$id_entry} = ? AND user_id = ?");
        $checkIfUserAlreadyRated->execute(array($object->id, $connected_user->id));
        $grade_infos = $checkIfUserAlreadyRated->fetch(PDO::FETCH_NUM);
        if ($checkIfUserAlreadyRated->rowCount() > 0){ // If user already rated this object, update the corresponding grade
            $current_grade = $grade_infos[0]; // Get user's current grade 
            $updateGrade = $db->prepare("UPDATE {$grades_table} SET grade = ? WHERE {$id_entry} = ? AND user_id = ?");
            $updateGrade->execute(array($grade, $object->id, $connected_user->id));
            $operation_type = 'update';
        } else { // Else, insert a new grade
            $insertGrade = $db->prepare("INSERT INTO {$grades_table} ({$id_entry}, user_id, grade) VALUES (?, ?, ?)");
            $insertGrade->execute(array($object->id, $connected_user->id, $grade));
            $operation_type = 'insertion';
        }

        // Get current rating infos
        $checkRating = $db->prepare("SELECT rating, grades_number FROM {$table} WHERE id = ?");
        $checkRating->execute(array($object->id));
        $rating_infos = $checkRating->fetch(PDO::FETCH_ASSOC);

        if ($operation_type == 'update') {
            // Substract previous grade from current rating
            if ($rating_infos['grades_number'] == 1) {
                $rating_without_grade = 0;
            } else {
                $rating_without_grade = ($rating_infos['grades_number'] * $rating_infos['rating'] - $current_grade) / ($rating_infos['grades_number'] - 1);
            }
            // Add new grade instead
            $new_rating = (($rating_infos['grades_number'] - 1) * $rating_without_grade + $grade) / $rating_infos['grades_number'];
            // Calculate new popularity
            $popularity = setPopularity($new_rating, $rating_infos['grades_number']);
            // Update rating and popularity in table
            $updateRating = $db->prepare("UPDATE {$table} SET rating = ?, popularity = ? WHERE id = ?");
            $updateRating->execute(array($new_rating, $popularity, $object->id));

        } else if ($operation_type == 'insertion') {
            // Calculate new rating
            $new_rating = ($rating_infos['grades_number'] * $rating_infos['rating'] + $grade) / ($rating_infos['grades_number'] + 1);
            // Calculate new popularity
            $popularity = setPopularity($new_rating, $rating_infos['grades_number'] + 1);
            // Update rating and grades number in table
            $updateRating = $db->prepare("UPDATE {$table} SET rating = ?, grades_number = grades_number + 1, popularity = ? WHERE id = ?");
            $updateRating->execute(array($new_rating, $popularity, $object->id));
        }
        $getNewRating = $db->prepare("SELECT rating, grades_number FROM {$table} WHERE id = ?");
        $getNewRating->execute(array($object->id));
        $new_rating_infos = $getNewRating->fetch(PDO::FETCH_ASSOC);
        $new_rating_infos['vote'] = $grade;
        $new_rating_infos['popularity'] = $popularity;

        echo json_encode($new_rating_infos);
    }

    if (isset($_GET['cancel-rating'])) {
        if ($_GET['type'] == 'mkpoint') {
            $object       = new Mkpoint($_GET['id']);
            $table        = "map_mkpoint";
            $grades_table = "grade_mkpoint";
        } else if ($_GET['type'] == 'segment') {
            $object       = new Segment($_GET['id']);
            $table        = "segments";
            $grades_table = "segment_grade";
        }
        $id_entry = $_GET['type'] . "_id";

        // Get current grade
        $getCurrentGrade = $db->prepare("SELECT grade FROM {$grades_table} WHERE {$id_entry} = ? AND user_id = ?");
        $getCurrentGrade->execute(array($object->id, $connected_user->id));
        $grade_infos = $getCurrentGrade->fetch(PDO::FETCH_NUM);
        if ($getCurrentGrade->rowCount() > 0){
            $current_grade = $grade_infos[0];
        } else {
            echo 'CURRENT GRADE NOT FOUND';
            return false;
        }

        // Remove grade entry from grade table
        $removeGrade = $db->prepare("DELETE FROM {$grades_table} WHERE {$id_entry} = ? AND user_id = ?");
        $removeGrade->execute(array($object->id, $connected_user->id));
        // Get current rating infos
        $getRating = $db->prepare("SELECT rating, grades_number FROM {$table} WHERE id = ?");
        $getRating->execute(array($object->id));
        $rating_infos = $getRating->fetch(PDO::FETCH_ASSOC);
        // Recalculate and update rating and popularity
        if ($rating_infos['grades_number'] == 1) {
            $new_rating = NULL;
            $popularity = NULL;
            $updateRating = $db->prepare("UPDATE {$table} SET rating = NULL, grades_number = 0, popularity = NULL WHERE id = ?");
            $updateRating->execute(array($object->id));
        } else {
            $new_rating = ($rating_infos['grades_number'] * $rating_infos['rating'] - $current_grade) / ($rating_infos['grades_number'] - 1);
            $popularity = setPopularity($new_rating, $rating_infos['grades_number'] - 1);
            // Update rating and popularity in table
            $updateRating = $db->prepare("UPDATE {$table} SET rating = ?, grades_number = grades_number - 1, popularity = ? WHERE id = ?");
            $updateRating->execute(array($new_rating, $popularity, $object->id));
        }
        
        echo json_encode(['rating' => $new_rating, 'grades_number' => $rating_infos['grades_number'] - 1, 'vote' => false, 'popularity' => $popularity]);
    }

    if (isset($_GET['get-reviews-mkpoint'])) {
        $mkpoint = new Mkpoint($_GET['get-reviews-mkpoint']);
        $reviews = $mkpoint->getReviews();
        // Add profile picture src to the response
        for ($i = 0; $i < count($reviews); $i++) {
            $propic = $reviews[$i]->user->getPropicSrc();
            $reviews[$i]->propic = $propic;
        }
        echo json_encode($reviews);
    }

    if (isset($_GET['add-review-mkpoint'])) {
        $mkpoint    = new Mkpoint($_GET['add-review-mkpoint']);
        $content    = $_GET['content'];
        $time       = date('Y-m-d H:i:s');
        $propic     = $connected_user->getPropicSrc();
        // Check if user has already posted a review
        $reviews = $mkpoint->getUserReview($connected_user);
        // If there is one..
        if (!empty($reviews)) {
            // ..and if content is not empty, update it
            if (!empty($content)) {
                $updateReview = $db->prepare('UPDATE mkpoint_reviews SET content = ?, time = ? WHERE mkpoint_id = ? AND user_id = ?');
                $updateReview->execute(array($content, $time, $mkpoint->id, $connected_user->id));
            // ..and if content is empty, delete it
            } else {
                $deleteReview = $db->prepare('DELETE FROM mkpoint_reviews WHERE mkpoint_id = ? AND user_id = ?');
                $deleteReview->execute(array($mkpoint->id, $connected_user->id));
            }
        } else {
            // Insert into mkpoint_reviews table
            $insertReview = $db->prepare('INSERT INTO mkpoint_reviews(mkpoint_id, user_id, user_login, content, time) VALUES (?, ?, ?, ?, ?)');
            $insertReview->execute(array($mkpoint->id, $connected_user->id, $connected_user->login, $content, $time));
        }
        echo json_encode(['mkpoint_id' => $mkpoint->id, "user" => ["id" => $connected_user->id, "login" => $connected_user->login], "content" => $content, "time" => $time, "propic" => $propic]);
    }

    if (isset($_GET['display-rides'])) {
        $getRides = $db->prepare('SELECT id, name, date, level_beginner, level_intermediate, level_athlete, nb_riders_max, description, distance, author_id, author_login, privacy, status, substatus, participants_number, route_id FROM rides');
        $getRides->execute();
        $rides = $getRides->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($rides); $i++) {
            $getCourse = $db->prepare('SELECT lng, lat FROM coords WHERE segment_id = ?');
            $getCourse->execute([$rides[$i]['route_id']]);
            $course = $getCourse->fetchAll(PDO::FETCH_NUM);
            $rides[$i]['route'] = $course;
        }
        for ($i = 0; $i < count($rides); $i++) {
            $getCheckpoints = $db->prepare('SELECT checkpoint_id, name, distance, lng, lat FROM ride_checkpoints WHERE ride_id = ? ORDER BY checkpoint_id');
            $getCheckpoints->execute([$rides[$i]['id']]);
            $checkpoints = $getCheckpoints->fetchAll(PDO::FETCH_ASSOC);
            $rides[$i]['checkpoints'] = $checkpoints;
        }
        echo json_encode($rides);
    }

    if (isset($_GET['ride-featured-image'])) {
        $getRideFeaturedImage = $db->prepare('SELECT * FROM ride_checkpoints WHERE ride_id = ? AND featured = 1');
        $getRideFeaturedImage->execute([$_GET['ride-featured-image']]);
        $featured_image = $getRideFeaturedImage->fetch(PDO::FETCH_ASSOC);
        echo json_encode($featured_image);
    }

    if (isset($_GET['display-segments'])) {
        $getSegments = $db->prepare('SELECT id FROM segments');
        $getSegments->execute();
        $segments = $getSegments->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($segments); $i++) {
            $segments[$i] = new Segment($segments[$i]['id'], false);
        }
        echo json_encode($segments);
    }

    if (isset($_GET['segment-mkpoints'])) {
        $segment = new Segment($_GET['segment-mkpoints']);
        $close_mkpoints = $segment->route->getCloseMkpoints(500);
        foreach ($close_mkpoints as $mkpoint) $mkpoint->photos = $mkpoint->getImages();
        echo json_encode($close_mkpoints);
    }

    if (isset($_GET['get-user-cleared-mkpoints'])) {
        $entries = $connected_user->getClearedMkpoints();
        $cleared_mkpoints = [];
        foreach ($entries as $entry) {
            $mkpoint = new Mkpoint($entry['mkpoint_id']);
            $mkpoint->activity_id = intval($entry['activity_id']);
            array_push($cleared_mkpoints, $mkpoint);
        }
        echo json_encode($cleared_mkpoints);
    }
    
    if (isset($_GET['get-user-favorite-mkpoints'])) {
        echo json_encode($connected_user->getFavorites('scenery'));
    }

}