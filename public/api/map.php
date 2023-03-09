<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['get-session'])) {
        if (isset($_SESSION['auth'])) echo json_encode($_SESSION);
    }

    // In case a 'saveMkpoint' index have been detected
    if (isset($_POST['saveMkpoint']) AND !empty($_POST['saveMkpoint'])) {

        try {

            if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
                throw new Exception('アップロード中に問題が発生しました。');
            } else {

                // If error is file_exceed_limit
                if ($_FILES['file']['error'] == 2) throw new Exception('アップロードされたファイルがサイズ制限を超えています（10Mb）。サイズを縮小して再度お試しください。');

                // Store image in jpg format
                $temp_image = new TempImage($_FILES['file']['name']);
                $temp_image->convert($_FILES['file']['tmp_name'], $_FILES['file']['name']);
                if (!$temp_image->temp_path) throw new Exception('アップロードしたファイル形式は対応しておりません。対応可能なファイル形式：' .implode(', ', $temp_image->accepted_formats));
                
                if (!isset(exif_read_data($temp_image->temp_path, 0, true)['EXIF']['DateTimeOriginal'])) { // If image header doesn't contain DateTimeOriginal
                    throw new Exception('この画像ファイルに撮影日時のデータが付随されていません。カメラデバイスから撮影された写真の生データしかご利用頂けません。');
                }
                
                // Preparing variables
                $mkpoint['user_id']          = $_SESSION['id'];
                $mkpoint['user_login']       = $_SESSION['login'];
                $mkpoint['category']         = 'marker';
                $mkpoint['name']             = htmlspecialchars($_POST['name']);
                $mkpoint['city']             = $_POST['city'];
                $mkpoint['prefecture']       = $_POST['prefecture'];
                $mkpoint['elevation']        = $_POST['elevation'];
                $mkpoint['date']             = exif_read_data($temp_image->temp_path, 0, true)['EXIF']['DateTimeOriginal'];
                $mkpoint['month']            = date("n", strtotime(exif_read_data($temp_image->temp_path, 0, true)['EXIF']['DateTimeOriginal']));
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

                // Get blob ready to upload
                $blob = $temp_image->treatFile($temp_image->temp_path);
                $filename = setFilename('img');
                    
                // Build thumbnail
                $mkpoint['thumbnail'] = $temp_image->getThumbnail();
            }
        
        // If any exception have been catched, response the error message set in the exception
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            die();
        }
        
        // Check if there is an existing index with the same lng and lat (or less than a 0,001 difference) in the database
        $checkLngLat = $db->prepare('SELECT id, lng, lat FROM map_mkpoint WHERE ROUND(lng, 3) = ? AND ROUND(lat, 3) = ?');
        $checkLngLat->execute(array(round($mkpoint['lng'], 3), round($mkpoint['lat'], 3)));
        // If there is one, update it
        if ($checkLngLat->rowCount() > 0) {
            $isMkpoint = $checkLngLat->fetch();
            $updateMapMkpoint = $db->prepare('UPDATE map_mkpoint SET user_id = ?, user_login = ?, category = ?, name = ?, city = ?, prefecture = ?, elevation = ?, date = ?, month = ?, description = ?, thumbnail = ?, popularity = ? WHERE ROUND(lng, 3) = ROUND(?, 3) AND ROUND(lat, 3) = ROUND(?, 3)');
            $updateMapMkpoint->execute(array($mkpoint['user_id'], $mkpoint['user_login'], $mkpoint['category'], $mkpoint['name'], $mkpoint['city'], $mkpoint['prefecture'], $mkpoint['elevation'], $mkpoint['date'], $mkpoint['month'], $mkpoint['description'], $mkpoint['thumbnail'], $mkpoint['popularity'], $mkpoint['lng'], $mkpoint['lat']));
            $updateImgMkpoint = $db->prepare('UPDATE img_mkpoint SET user_id = ?, date = ?, filename = ? WHERE mkpoint_id = ?');
            $updateImgMkpoint->execute(array($mkpoint['user_id'], $mkpoint['date'], $filename, $isMkpoint['id']));
            $getMkpointId = $db->prepare('SELECT id FROM map_mkpoint WHERE ROUND(lng, 3) = ? AND ROUND(lat, 3) = ?');
            $getMkpointId->execute(array(round($mkpoint['lng'], 3), round($mkpoint['lat'], 3)));
            $mkpoint['id'] = $getMkpointId->fetch(PDO::FETCH_COLUMN);
        // Else, create it
        } else {
		    $insertMapMkpoint = $db->prepare('INSERT INTO map_mkpoint (user_id, user_login, category, name, city, prefecture, elevation, date, month, description, thumbnail, popularity, lng, lat, publication_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		    $insertMapMkpoint->execute(array($mkpoint['user_id'], $mkpoint['user_login'], $mkpoint['category'], $mkpoint['name'], $mkpoint['city'], $mkpoint['prefecture'], $mkpoint['elevation'], $mkpoint['date'], $mkpoint['month'], $mkpoint['description'], $mkpoint['thumbnail'], $mkpoint['popularity'], $mkpoint['lng'], $mkpoint['lat'], $mkpoint['publication_date']));
            $getMkpointId = $db->prepare('SELECT id FROM map_mkpoint WHERE ROUND(lng, 3) = ? AND ROUND(lat, 3) = ?');
            $getMkpointId->execute(array(round($mkpoint['lng'], 3), round($mkpoint['lat'], 3)));
            $mkpoint['id'] = $getMkpointId->fetch(PDO::FETCH_COLUMN);
            $insertImgMkpoint = $db->prepare('INSERT INTO img_mkpoint (mkpoint_id, user_id, date, likes, filename) VALUES (?, ?, ?, ?, ?)');
            $insertImgMkpoint->execute(array($mkpoint['id'], $mkpoint['user_id'], $mkpoint['date'], 0, $filename));
        }

        // If everything went fine, response the mkpoint data
        $mkpoint_response = [
            'grades_number' => 0,
            'id' => $mkpoint['id'],
            'lat' => $mkpoint['lat'],
            'lng' => $mkpoint['lng'],
            'name' => $mkpoint['name'],
            'popularity' => $mkpoint['popularity'],
            'rating' => null,
            'thumbnail' => $mkpoint['thumbnail'],
            'user_id' => $mkpoint['user_id']
        ];
        echo json_encode($mkpoint_response);
        
        // Connect to blob storage
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/actions/blobStorageAction.php';
        // Send file to blob storage
        $containername = 'scenery-photos';
        $blobClient->createBlockBlob($containername, $filename, $blob);
        // Set file metadata
        $metadata = [
            'file_name' => $mkpoint['file_name'],
            'file_type' => $mkpoint['file_type'],
            'file_size' => $mkpoint['file_size'],
            'scenery_id' => $mkpoint['id'],
            'author_id' => $mkpoint['user_id'],
            'date' => $mkpoint['publication_date'],
            'lat' => $mkpoint['lat'],
            'lng' => $mkpoint['lng']
        ];
        $blobClient->setBlobMetadata($containername, $filename, $metadata);

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
                throw new Exception('アップロード中に問題が発生しました。');
            } else {

                // If error is file_exceed_limit
                if ($_FILES['file']['error'] == 2) throw new Exception('アップロードされたファイルがサイズ制限を超えています（10Mb）。サイズを縮小して再度お試しください。');

                // Store image in jpg format
                $temp_image = new TempImage($_FILES['file']['name']);
                $temp_image->convert($_FILES['file']['tmp_name'], $_FILES['file']['name']);
                if (!$temp_image->temp_path) throw new Exception('アップロードしたファイル形式は対応しておりません。対応可能なファイル形式：' .implode(', ', $temp_image->accepted_formats));
                
                if (!isset(exif_read_data($temp_image->temp_path, 0, true)['EXIF']['DateTimeOriginal'])) { // If image header doesn't contain DateTimeOriginal
                    throw new Exception('この画像ファイルに撮影日時のデータが付随されていません。カメラデバイスから撮影された写真の生データしかご利用頂けません。');
                }
                
                // Preparing variables
                $user = new User($_SESSION['id']);
                $mkpointimg['mkpoint_id']  = $_POST['mkpoint_id'];
                $mkpointimg['user_id']     = $user->id;
                $mkpointimg['user_login']  = $user->login;
                $mkpointimg['date']        = date('Y-m-d H:i:s', strtotime(exif_read_data($temp_image->temp_path, 0, true)['EXIF']['DateTimeOriginal']));
                $mkpointimg['file_size']   = $_FILES['file']['size'];
                $mkpointimg['file_name']   = $_FILES['file']['name'];
                $mkpointimg['file_type']   = $_FILES['file']['type'];
                $mkpointimg['error']       = $_FILES['file']['error'];

                // Get blob ready to upload
                $blob = $temp_image->treatFile($temp_image->temp_path);

                // Connect to blob storage
                $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
                require $folder . '/actions/blobStorageAction.php';
                // Send file to blob storage
                $containername = 'scenery-photos';
                $filename = setFilename('img');
                $blobClient->createBlockBlob($containername, $filename, $blob);
                // Set file metadata
                $mkpoint_instance = new Mkpoint($mkpointimg['mkpoint_id']);
                $lngLat = $mkpoint_instance->lngLat;
                $metadata = [
                    'file_name' => $mkpointimg['file_name'],
                    'file_type' => $mkpointimg['file_type'],
                    'file_size' => $mkpointimg['file_size'],
                    'scenery_id' => $mkpointimg['mkpoint_id'],
                    'author_id' => $mkpointimg['user_id'],
                    'date' => $mkpointimg['date'],
                    'lng' => $lngLat->lng,
                    'lat' => $lngLat->lat
                ];
                $blobClient->setBlobMetadata($containername, $filename, $metadata);
            }
        
        // If any exception have been catched, response the error message set in the exception
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            die();
        }

        // Check if the same image have already been uploaded
        $checkIfSimilarImageExists = $db->prepare('SELECT id FROM img_mkpoint WHERE mkpoint_id = ? AND user_id = ? AND date = ?');
        $checkIfSimilarImageExists->execute(array($mkpointimg['mkpoint_id'], $mkpointimg['user_id'], $mkpointimg['date']));        
        // If not, insert image in the database img_mkpoint table and send response
        if ($checkIfSimilarImageExists->rowCount() == 0) {
            $insertMkpoint = $db->prepare('INSERT INTO img_mkpoint(mkpoint_id, user_id, date, likes, filename) VALUES (?, ?, ?, ?, ?)');
            $insertMkpoint->execute(array($mkpointimg['mkpoint_id'], $mkpointimg['user_id'], $mkpointimg['date'], 0, $filename));    
            echo json_encode(['success' => $mkpointimg['file_name']. 'は無事に追加されました！']);
        } else echo json_encode(['error' => $mkpointimg['file_name']. 'は既にアップロードされています。']);
    }

    if (isset($_GET['mkpoint-photos'])) {
        $mkpoint = new Mkpoint($_GET['mkpoint-photos']);
        echo json_encode($mkpoint->getImages());
    }

    if (isset($_GET['mkpoints-closest-photo'])) { // Get photo whose period is the soonest for each mkpoint
        if (strlen($_GET['mkpoints-closest-photo']) == 0) $mkpoints = [];
        else {
            $mkpoints_ids = explode(',', $_GET['mkpoints-closest-photo']);
            $mkpoints = [];
            foreach ($mkpoints_ids as $mkpoint_id) {
                $getMkpointPhoto = $db->prepare('SELECT id FROM img_mkpoint WHERE mkpoint_id = ? AND MONTH(date) > ? ORDER BY date ASC');
                $getMkpointPhoto->execute([$mkpoint_id, date('m')]);
                if ($getMkpointPhoto->rowCount() == 0) {
                    $getMkpointPhoto = $db->prepare('SELECT id FROM img_mkpoint WHERE mkpoint_id = ? ORDER BY date DESC');
                    $getMkpointPhoto->execute([$mkpoint_id]);
                }
                $mkpointphoto = new MkpointImage($getMkpointPhoto->fetch(PDO::FETCH_ASSOC)['id']);
                array_push($mkpoints,['id' => $mkpoint_id, 'data' => $mkpointphoto]);
            }
        }
        echo json_encode($mkpoints);
    }

    if (isset($_GET['getpropic'])) {
        if (is_numeric($_GET['getpropic'])) $user = new User($_GET['getpropic']);
        else $user = $connected_user;
        $profile_picture_src = $user->getPropicUrl();
        echo json_encode([$profile_picture_src]);
    }

    if (isset($_GET['display-mkpoints'])) {
        if (isset($_GET['details']) && $_GET['details'] == true) $getMkpoints = $db->prepare('SELECT id, user_id, name, description, city, prefecture, thumbnail, elevation, lng, lat, rating, grades_number, popularity FROM map_mkpoint ORDER BY popularity, rating, grades_number DESC, elevation ASC');
        else $getMkpoints = $db->prepare('SELECT id, user_id, name, thumbnail, lng, lat, rating, grades_number, popularity FROM map_mkpoint ORDER BY popularity, rating, grades_number DESC, elevation ASC');
        $getMkpoints->execute();
        $result = $getMkpoints->fetchAll(PDO::FETCH_ASSOC);
        $mkpoints = $result;
        echo json_encode($mkpoints);
    }

    if (isset($_GET['get-mkpoints'])) {
        $mkpoints_ids = explode(',', $_GET['get-mkpoints']);
        $mkpoints = [];
        foreach ($mkpoints_ids as $mkpoint_id) {
            $mkpoint = new Mkpoint($mkpoint_id);
            if (isset($_SESSION['id'])) $mkpoint->isFavorite = $mkpoint->isFavorite();
            if (isset($_SESSION['id'])) $mkpoint->isCleared = $mkpoint->isCleared();
            $mkpoint->tags = $mkpoint->getTags();
            array_push($mkpoints, $mkpoint);
        }
        echo json_encode($mkpoints);
    }

    if (isset($_GET['mkpoint'])) {
        $mkpoint_id = $_GET['mkpoint'];
        $getMkpoint = $db->prepare('SELECT id FROM map_mkpoint WHERE id = ?');
        $getMkpoint->execute(array($mkpoint_id));
        if ($getMkpoint->rowCount() > 0) {
            $mkpoint = new Mkpoint($mkpoint_id);
            if (isset($_SESSION['id'])) $mkpoint->isFavorite = $mkpoint->isFavorite();
            if (isset($_SESSION['id'])) $mkpoint->isCleared = $mkpoint->isCleared();
            $mkpoint->tags = $mkpoint->getTags();
            echo json_encode(['data' => $mkpoint, 'photos' => $mkpoint->getImages()]);
        } else echo json_encode(['error' => '該当する絶景スポットは存在していません。']);
    }

    if (isset($_GET['mkpoint-details'])) {
        $mkpoint_id = $_GET['mkpoint-details'];
        $getMkpoint = $db->prepare('SELECT id FROM map_mkpoint WHERE id = ?');
        $getMkpoint->execute(array($mkpoint_id));
        if ($getMkpoint->rowCount() > 0) {
            $mkpoint = new Mkpoint($mkpoint_id);
            if (isset($_SESSION['id'])) $mkpoint->isFavorite = $mkpoint->isFavorite();
            if (isset($_SESSION['id'])) $mkpoint->isCleared = $mkpoint->isCleared();
            $mkpoint->tags = $mkpoint->getTags();
            $mkpoint->photos = $mkpoint->getImages();
            echo json_encode($mkpoint);
        } else echo json_encode(['error' => '該当する絶景スポットは存在していません。']);
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
        $mkpoint_tags        = explode(",", $_GET['tags']);
        // Update mkpoint data
        $removeMkpoint = $db->prepare('UPDATE map_mkpoint SET name = ?, description = ? WHERE id = ?');
        $removeMkpoint->execute(array($mkpoint_name, $mkpoint_description, $mkpoint_id));
        // Update tags data
        $deleteCurrentTags = $db->prepare('DELETE FROM tags WHERE object_type = ? AND object_id = ?');
        $deleteCurrentTags->execute(array('scenery', $mkpoint_id));
        foreach ($mkpoint_tags as $tag) {
            $insertNewTags = $db->prepare('INSERT INTO tags (object_type, object_id, tag) VALUES (?, ?, ?)');
            $insertNewTags->execute(array('scenery', $mkpoint_id, $tag));
        }
        echo json_encode(['id' => $mkpoint_id, 'name' =>  $mkpoint_name, 'description' => $mkpoint_description, 'tags' => $mkpoint_tags]);
    }

    if (isset($_GET['delete-mkpoint'])) {
        $mkpoint = new Mkpoint($_GET['delete-mkpoint']);
        $mkpoint->delete();
        echo json_encode([$mkpoint->id]);
    }

    // Delete one photo from a mkpoint
    if (isset($_GET['delete-mkpoint-photo'])) {
        $photo_id = $_GET['delete-mkpoint-photo'];
        $photo = new MkpointImage($photo_id);
        $photo->delete();
        echo json_encode(['success' => '写真' .$photo->id. 'が削除されました。']);
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
        if (isset($_SESSION['id'])) {
            $vote = $object->getUserVote($connected_user);
            $rating_infos['vote'] = $vote;
        }
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
            $propic = $reviews[$i]->user->getPropicUrl();
            $reviews[$i]->propic = $propic;
        }
        echo json_encode($reviews);
    }

    if (isset($_GET['add-review-mkpoint'])) {
        $mkpoint = new Mkpoint($_GET['add-review-mkpoint']);
        $content = $_GET['content'];
        $time    = date('Y-m-d H:i:s');
        $propic  = $connected_user->getPropicUrl();
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
        define('RIDES_DATE_RANGE', 3); // Define interval in which rides must be displayed in months
        $getRides = $db->prepare("SELECT id FROM rides
        WHERE
            (privacy = 'Public' OR (privacy = 'Friends only' AND author_id IN ('".implode("','",$connected_user->getFriends())."')))
        AND
            date BETWEEN :today AND :datemax
        AND
            entry_start < NOW() AND entry_end > NOW()");
        $today = new DateTime();
        $getRides->execute(array(":today" => $today->format('Y-m-d H:i:s'), ":datemax" => $today->modify('+' . RIDES_DATE_RANGE . ' month')->format('Y-m-d H:i:s')));
        $rides = [];
        while ($ride_data = $getRides->fetch(PDO::FETCH_COLUMN)) {
            $ride = new Ride($ride_data);
            $ride->route = $ride->getRoute();
            $ride->author_login = $ride->getAuthor()->login;
            array_push($rides, $ride);
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
        $getSegments = $db->prepare('SELECT id, route_id, rank, name, advised, popularity FROM segments ORDER BY popularity DESC');
        $getSegments->execute();
        $segments = $getSegments->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($segments); $i++) {
            // Add coordinates
            $getCoords = $db->prepare('SELECT lng, lat FROM coords WHERE segment_id = ? ORDER BY number ASC');
            $getCoords->execute([$segments[$i]['route_id']]);
            $segments[$i]['coordinates'] = $getCoords->fetchAll(PDO::FETCH_NUM);
            // Add tunnels
            $tunnels = [];
            $getTunnelsNumber = $db->prepare('SELECT DISTINCT tunnel_id FROM tunnels WHERE segment_id = ?');
            $getTunnelsNumber->execute([$segments[$i]['route_id']]);
            $tunnels_number = $getTunnelsNumber->rowCount();
            for ($j = 0 ; $j < $tunnels_number; $j++) {
                $getTunnelCoords = $db->prepare('SELECT lng, lat FROM tunnels WHERE tunnel_id = ? AND segment_id = ?');
                $getTunnelCoords->execute([$j, $segments[$i]['route_id']]);
                $tunnels[$j] = $getTunnelCoords->fetchAll(PDO::FETCH_NUM);
            }
            $segments[$i]['tunnels'] = $tunnels;
            // Add tags
            $getTags = $db->prepare('SELECT tag FROM tags WHERE object_type = ? AND object_id = ?');
            $getTags->execute(['segment', $segments[$i]['id']]);
            $tags = $getTags->fetchAll(PDO::FETCH_COLUMN);
            $segments[$i]['tags'] = $tags;
        }
        echo json_encode($segments);
    }
    
    if (isset($_GET['segment-details'])) {
        $segment_id = $_GET['segment-details'];
        echo json_encode(new Segment($segment_id, false));
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

    if (isset($_GET['get-icon'])) {
        $filename = $_GET['get-icon'];
        $path = $_SERVER['DOCUMENT_ROOT']. "/map/media/" .$filename;
        echo json_encode($path);
    }

}