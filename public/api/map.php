<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['get-session'])) {
        if (isSessionActive()) echo json_encode($_SESSION);
    }

    // In case a 'saveScenery' index have been detected
    if (isset($_POST['saveScenery']) AND !empty($_POST['saveScenery'])) {

        try {

            if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
                throw new Exception('アップロード中に問題が発生しました。');
            } else {

                // If error is file_exceed_limit
                if ($_FILES['file']['error'] == 2) throw new Exception('アップロードされたファイルがサイズ制限を超えています（10Mb）。サイズを縮小して再度お試しください。');

                // Store image in jpg format
                $temp_image = new TempImage($_FILES['file']['name']);
                $temp_image->convert($_FILES['file']['tmp_name']);
                if (!$temp_image->temp_path) throw new Exception('アップロードしたファイル形式は対応しておりません。対応可能なファイル形式：' .implode(', ', $temp_image->accepted_formats));
                
                if (!isset(exif_read_data($temp_image->temp_path, 0, true)['EXIF']['DateTimeOriginal'])) { // If image header doesn't contain DateTimeOriginal
                    throw new Exception('この画像ファイルに撮影日時のデータが付随されていません。カメラデバイスから撮影された写真の生データしかご利用頂けません。');
                }
                
                // Preparing variables
                $scenery_data['id']               = getNextAutoIncrement('sceneries');
                $scenery_data['user_id']          = $_SESSION['id'];
                $scenery_data['user_login']       = $_SESSION['login'];
                $scenery_data['category']         = 'marker';
                $scenery_data['name']             = htmlspecialchars($_POST['name']);
                $scenery_data['city']             = $_POST['city'];
                $scenery_data['prefecture']       = $_POST['prefecture'];
                $scenery_data['elevation']        = $_POST['elevation'];
                $scenery_data['date']             = new Datetime(exif_read_data($temp_image->temp_path, 0, true)['EXIF']['DateTimeOriginal']);
                $scenery_data['month']            = date("n", strtotime(exif_read_data($temp_image->temp_path, 0, true)['EXIF']['DateTimeOriginal']));
                $scenery_data['description']      = htmlspecialchars($_POST['description']);
                $scenery_data['tags']             = explode(",", $_POST['tags']);
                $scenery_data['file_size']        = $_FILES['file']['size'];
                $scenery_data['file_name']        = $_FILES['file']['name'];
                $scenery_data['file_type']        = $_FILES['file']['type'];
                $scenery_data['lng']              = $_POST['lng'];
                $scenery_data['lat']              = $_POST['lat'];
                $scenery_data['publication_date'] = (new DateTime(date('Y-m-d H:i:s'), new DateTimezone('Asia/Tokyo')));
                $scenery_data['error']            = $_FILES['file']['error'];
                $scenery_data['popularity']       = 30;

                // Preparing photo variables
                $scenery_data['photos'][0]['blob']     = $temp_image->treatFile($temp_image->temp_path);
                $scenery_data['photos'][0]['filename'] = setFilename('img');
                $scenery_data['photos'][0]['size']     = $scenery_data['file_size'];
                $scenery_data['photos'][0]['name']     = $scenery_data['file_name'];
                $scenery_data['photos'][0]['type']     = $scenery_data['file_type'];
            }

            // Create scenery
            $scenery = new Scenery();
            $scenery->create($scenery_data);
            
            // If everything went fine, response the scenery data
            $scenery_response = [
                'grades_number' => 0,
                'id' => $scenery_data['id'],
                'lat' => $scenery_data['lat'],
                'lng' => $scenery_data['lng'],
                'name' => $scenery_data['name'],
                'popularity' => $scenery_data['popularity'],
                'rating' => null,
                'user_id' => $scenery_data['user_id'],
                'thumbnail' => $scenery->getThumbnail()
            ];
            echo json_encode($scenery_response);
        
        // If any exception have been catched, response the error message set in the exception
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            die();
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
                $temp_image->convert($_FILES['file']['tmp_name']);
                if (!$temp_image->temp_path) throw new Exception('アップロードしたファイル形式は対応しておりません。対応可能なファイル形式：' .implode(', ', $temp_image->accepted_formats));
                
                if (!isset(exif_read_data($temp_image->temp_path, 0, true)['EXIF']['DateTimeOriginal'])) { // If image header doesn't contain DateTimeOriginal
                    throw new Exception('この画像ファイルに撮影日時のデータが付随されていません。カメラデバイスから撮影された写真の生データしかご利用頂けません。');
                }
                
                // Preparing variables
                $user = new User($_SESSION['id']);
                $sceneryimg['scenery_id']  = $_POST['scenery_id'];
                $sceneryimg['user_id']     = $user->id;
                $sceneryimg['user_login']  = $user->login;
                $sceneryimg['date']        = date('Y-m-d H:i:s', strtotime(exif_read_data($temp_image->temp_path, 0, true)['EXIF']['DateTimeOriginal']));
                $sceneryimg['file_size']   = $_FILES['file']['size'];
                $sceneryimg['file_name']   = $_FILES['file']['name'];
                $sceneryimg['file_type']   = $_FILES['file']['type'];
                $sceneryimg['error']       = $_FILES['file']['error'];

                // Get blob ready to upload
                $blob = $temp_image->treatFile($temp_image->temp_path);

                // Connect to blob storage
                $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
                require $folder . '/actions/blobStorage.php';
                // Send file to blob storage
                $containername = 'scenery-photos';
                $filename = setFilename('img');
                $blobClient->createBlockBlob($containername, $filename, $blob);
                // Set file metadata
                $scenery_instance = new Scenery($sceneryimg['scenery_id']);
                $lngLat = $scenery_instance->lngLat;
                $metadata = [
                    'file_name' => $sceneryimg['file_name'],
                    'file_type' => $sceneryimg['file_type'],
                    'file_size' => $sceneryimg['file_size'],
                    'scenery_id' => $sceneryimg['scenery_id'],
                    'author_id' => $sceneryimg['user_id'],
                    'date' => $sceneryimg['date'],
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

        // Insert image in the database scenery_photos table and send response
        $insertScenery = $db->prepare('INSERT INTO scenery_photos(scenery_id, user_id, date, likes, filename) VALUES (?, ?, ?, ?, ?)');
        $insertScenery->execute(array($sceneryimg['scenery_id'], $sceneryimg['user_id'], $sceneryimg['date'], 0, $filename));    
        echo json_encode(['success' => $sceneryimg['file_name']. 'は無事に追加されました！']);
    }

    if (isset($_GET['scenery-photos'])) {
        $scenery = new Scenery($_GET['scenery-photos']);
        echo json_encode($scenery->getImages());
    }

    if (isset($_GET['sceneries-closest-photo'])) { // Get photo whose period is the soonest for each scenery
        if (strlen($_GET['sceneries-closest-photo']) == 0) $sceneries = [];
        else {
            $sceneries_ids = explode(',', $_GET['sceneries-closest-photo']);
            $sceneries = [];
            foreach ($sceneries_ids as $scenery_id) {
                $getSceneryPhoto = $db->prepare('SELECT id FROM scenery_photos WHERE scenery_id = ? AND EXTRACT(MONTH FROM date) > ? ORDER BY date ASC');
                $getSceneryPhoto->execute([$scenery_id, date('m')]);
                if ($getSceneryPhoto->rowCount() == 0) {
                    $getSceneryPhoto = $db->prepare('SELECT id FROM scenery_photos WHERE scenery_id = ? ORDER BY date DESC');
                    $getSceneryPhoto->execute([$scenery_id]);
                }
                $sceneryphoto = new SceneryImage($getSceneryPhoto->fetch(PDO::FETCH_COLUMN));
                array_push($sceneries,['id' => $scenery_id, 'data' => $sceneryphoto]);
            }
        }
        echo json_encode($sceneries);
    }

    if (isset($_GET['getpropic'])) {
        if (is_numeric($_GET['getpropic'])) $user = new User($_GET['getpropic']);
        else $user = getConnectedUser();
        $profile_picture_src = $user->getPropicUrl();
        echo json_encode([$profile_picture_src]);
    }

    if (isset($_GET['display-sceneries'])) {
        if (isset($_GET['details']) && $_GET['details'] == true) $getSceneries = $db->prepare('SELECT s.id, s.user_id, s.name, s.description, s.city, s.prefecture, s.elevation, s.rating, s.grades_number, s.popularity, ST_X(s.point::geometry) as lng, ST_Y(s.point::geometry) as lat, p.filename FROM sceneries as s JOIN scenery_photos AS p ON s.id = p.scenery_id ORDER BY popularity, rating, grades_number DESC, elevation ASC');
        else $getSceneries = $db->prepare('SELECT s.id, s.user_id, s.name, s.rating, s.grades_number, s.popularity, ST_X(s.point::geometry) as lng, ST_Y(s.point::geometry) as lat, p.filename FROM sceneries as s JOIN scenery_photos as p ON s.id = p.scenery_id ORDER BY popularity, rating, grades_number DESC, elevation ASC');
        $getSceneries->execute();
        $result = $getSceneries->fetchAll(PDO::FETCH_ASSOC);
        $sceneries = array_map(function ($scenery) {
            require substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT']))) . '/actions/blobStorage.php';
            $scenery['thumbnail'] = $blobClient->getBlobUrl('scenery-photos', $scenery['filename']);
            return $scenery;
        }, $result);
        echo json_encode($sceneries);
    }

    if (isset($_GET['get-sceneries'])) {
        $sceneries_ids = explode(',', $_GET['get-sceneries']);
        $sceneries = [];
        foreach ($sceneries_ids as $scenery_id) {
            $scenery = new Scenery($scenery_id);
            if (isset($_SESSION['id'])) $scenery->isFavorite = $scenery->isFavorite();
            if (isset($_SESSION['id'])) $scenery->isCleared = $scenery->isCleared();
            $scenery->tags = $scenery->getTags();
            array_push($sceneries, $scenery);
        }
        echo json_encode($sceneries);
    }

    if (isset($_GET['scenery'])) {
        $scenery_id = $_GET['scenery'];
        $getScenery = $db->prepare('SELECT id FROM sceneries WHERE id = ?');
        $getScenery->execute(array($scenery_id));
        if ($getScenery->rowCount() > 0) {
            $scenery = new Scenery($scenery_id);
            if (isset($_SESSION['id'])) $scenery->isFavorite = $scenery->isFavorite();
            if (isset($_SESSION['id'])) $scenery->isCleared = $scenery->isCleared();
            $scenery->tags = $scenery->getTags();
            echo json_encode(['data' => $scenery, 'photos' => $scenery->getImages()]);
        } else echo json_encode(['error' => '該当する絶景スポットは存在していません。']);
    }

    if (isset($_GET['scenery-details'])) {
        $scenery_id = $_GET['scenery-details'];
        $getScenery = $db->prepare('SELECT id FROM sceneries WHERE id = ?');
        $getScenery->execute(array($scenery_id));
        if ($getScenery->rowCount() > 0) {
            $scenery = new Scenery($scenery_id);
            if (isset($_SESSION['id'])) $scenery->isFavorite = $scenery->isFavorite();
            if (isset($_SESSION['id'])) $scenery->isCleared = $scenery->isCleared();
            $scenery->tags = $scenery->getTags();
            $scenery->photos = $scenery->getImages();
            echo json_encode($scenery);
        } else echo json_encode(['error' => '該当する絶景スポットは存在していません。']);
    }

    if (isset($_GET['display-sceneries-list'])) {
        $querystring = "SELECT * FROM sceneries WHERE id IN (" .$_GET['display-sceneries-list']. ")";
        $getSceneries = $db->prepare($querystring);
        $getSceneries->execute();
        $sceneriesList = $getSceneries->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($sceneriesList);
    }

    if (isset($_GET['sceneries-photos'])) {
        $sceneries_ids = explode(',', $_GET['get-sceneries']);
        $photos = [];
        foreach ($sceneries_ids as $scenery_id) {
            $scenery = new Scenery($scenery_id);
            $images = $scenery->getImages(3);
            foreach ($images as $image) array_push($photos, $image);
        }
        echo json_encode($photos);
    }

    if (isset($_GET['scenery-dragged'])) {
        if (isset($_GET['lng']) && isset($_GET['lat'])) {
            $id  = $_GET['scenery-dragged'];
            $lng = $_GET['lng'];
            $lat = $_GET['lat'];
            $scenery = new Scenery($id);
            $scenery->move(new LngLat($lng, $lat));
            echo json_encode([$lng, $lat]);
        }
    }

    if (isset($_GET['islike-img'])) {
        if (!isset($_SESSION['id'])) $islike = false;
        else {
            $img_id = $_GET['islike-img'];
            $checkIfUserHasAlreadyGivenALike = $db->prepare('SELECT * FROM scenery_photos_likes WHERE user_id = ? AND img_id = ?');
            $checkIfUserHasAlreadyGivenALike->execute(array($_SESSION['id'], $img_id));
            if ($checkIfUserHasAlreadyGivenALike->rowcount() > 0) $islike = true;
            else $islike = false;
        }
        echo json_encode($islike);
    }

    if (isset($_GET['togglelike-img'])) {
        // Get all scenery infos
        $img = new SceneryImage($_GET['togglelike-img']);

        // Get scenery id
        $getSceneryId = $db->prepare('SELECT scenery_id FROM scenery_photos WHERE id = ?');
        $getSceneryId->execute([$_GET['togglelike-img']]);
        $scenery_id = $getSceneryId->fetch(PDO::FETCH_NUM)[0];
        $scenery = new Scenery($scenery_id);

        // Check if user has already given a like
        $checkIfUserHasAlreadyGivenALike = $db->prepare('SELECT * FROM scenery_photos_likes WHERE user_id = ? AND img_id = ?');
        $checkIfUserHasAlreadyGivenALike->execute(array(getConnectedUser()->id, $img->id));

        // If user has already liked
        if ($checkIfUserHasAlreadyGivenALike->rowcount() > 0) {
            // Decrease likes in scenery_photos table
            $removeLikeFromImg = $db->prepare('UPDATE scenery_photos SET likes = likes - 1 WHERE id = ?');
            $removeLikeFromImg->execute(array($img->id));
            // Decrease likes and popularity in sceneries table
            $removeLikeFromScenery = $db->prepare('UPDATE sceneries SET likes = likes - 1, popularity = popularity - 5 WHERE id = ?');
            $removeLikeFromScenery->execute(array($scenery->id));
            // Remove corresponding entry in scenery_photos_likes table
            $removeEntryFromIslikeSceneryTable = $db->prepare('DELETE FROM scenery_photos_likes WHERE user_id = ? AND img_id = ?');
            $removeEntryFromIslikeSceneryTable->execute(array(getConnectedUser()->id, $img->id));
            $is_given_point = false;

        // If user has not liked yet
        } else {
            // Increase likes in scenery_photos table
            $addLikeFromImg = $db->prepare('UPDATE scenery_photos SET likes = likes + 1 WHERE id = ?');
            $addLikeFromImg->execute(array($img->id));
            // Increase likes and popularity in sceneries table
            $addLikeFromScenery = $db->prepare('UPDATE sceneries SET likes = likes + 1, popularity = popularity + 5 WHERE id = ?');
            $addLikeFromScenery->execute(array($scenery->id));
            // Add corresponding entry in scenery_photos_likes table
            $setEntryInIslikeSceneryTable = $db->prepare('INSERT INTO scenery_photos_likes(user_id, img_id) VALUES (?, ?)');
            $setEntryInIslikeSceneryTable->execute(array(getConnectedUser()->id, $img->id));
            $is_given_point = true;
        }
        // Update scenery infos
        $updated_img     = new SceneryImage($_GET['togglelike-img']);
        $updated_scenery = new Scenery($scenery_id);

        echo json_encode(['islike' => $is_given_point, 'imgLikes' => $updated_img->likes, 'sceneryLikes' => $updated_scenery->likes]);
    }

    if (isset($_GET['edit-scenery'])) {
        $scenery_id          = $_GET['edit-scenery'];
        $scenery_name        = $_GET['name'];
        $scenery_description = $_GET['description'];
        $scenery_tags        = explode(",", $_GET['tags']);
        // Update scenery data
        $removeScenery = $db->prepare('UPDATE sceneries SET name = ?, description = ? WHERE id = ?');
        $removeScenery->execute(array($scenery_name, $scenery_description, $scenery_id));
        // Update tags data
        $deleteCurrentTags = $db->prepare('DELETE FROM tags WHERE object_type = ? AND object_id = ?');
        $deleteCurrentTags->execute(array('scenery', $scenery_id));
        foreach ($scenery_tags as $tag) {
            $insertNewTags = $db->prepare('INSERT INTO tags (object_type, object_id, tag) VALUES (?, ?, ?)');
            $insertNewTags->execute(array('scenery', $scenery_id, $tag));
        }
        echo json_encode(['id' => $scenery_id, 'name' =>  $scenery_name, 'description' => $scenery_description, 'tags' => $scenery_tags]);
    }

    if (isset($_GET['delete-scenery'])) {
        $scenery = new Scenery($_GET['delete-scenery']);
        $scenery->delete();
        echo json_encode([$scenery->id]);
    }

    // Delete one photo from a scenery
    if (isset($_GET['delete-scenery-photo'])) {
        $photo_id = $_GET['delete-scenery-photo'];
        $photo = new SceneryImage($photo_id);
        $photo->delete();
        echo json_encode(['success' => '写真' .$photo->id. 'が削除されました。']);
    }

    if (isset($_GET['get-rating'])) {
        if ($_GET['type'] == 'scenery') {
            $object = new Scenery($_GET['id']);
            $table = "sceneries";
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
            $vote = $object->getUserVote(getConnectedUser());
            $rating_infos['vote'] = $vote;
        }
        echo json_encode($rating_infos);
    }

    if (isset($_GET['check-user-vote'])) {
        $scenery = new Scenery($_GET['check-user-vote']);
        $user    = new User($_GET['user_id']);
        $vote    = $scenery->getUserVote($user);
        echo json_encode($vote);
    }

    if (isset($_GET['set-rating'])) {

        if ($_GET['type'] == 'scenery') {
            $object       = new Scenery($_GET['id']);
            $table        = "sceneries";
            $grades_table = "scenery_grades";
        } else if ($_GET['type'] == 'segment') {
            $object       = new Segment($_GET['id']);
            $table        = "segments";
            $grades_table = "segment_grade";
        }
        $id_entry = $_GET['type'] . "_id";
        $grade    = $_GET['grade'];

        // Add or update grade to table
        $checkIfUserAlreadyRated = $db->prepare("SELECT grade FROM {$grades_table} WHERE {$id_entry} = ? AND user_id = ?");
        $checkIfUserAlreadyRated->execute(array($object->id, getConnectedUser()->id));
        $grade_infos = $checkIfUserAlreadyRated->fetch(PDO::FETCH_NUM);
        if ($checkIfUserAlreadyRated->rowCount() > 0){ // If user already rated this object, update the corresponding grade
            $current_grade = $grade_infos[0]; // Get user's current grade 
            $updateGrade = $db->prepare("UPDATE {$grades_table} SET grade = ? WHERE {$id_entry} = ? AND user_id = ?");
            $updateGrade->execute(array($grade, $object->id, getConnectedUser()->id));
            $operation_type = 'update';
        } else { // Else, insert a new grade
            $insertGrade = $db->prepare("INSERT INTO {$grades_table} ({$id_entry}, user_id, grade) VALUES (?, ?, ?)");
            $insertGrade->execute(array($object->id, getConnectedUser()->id, $grade));
            $operation_type = 'insertion';
        }

        // Get current rating infos
        $checkRating = $db->prepare("SELECT rating, grades_number FROM {$table} WHERE id = ?");
        $checkRating->execute(array($object->id));
        $rating_infos = $checkRating->fetch(PDO::FETCH_ASSOC);

        if ($operation_type == 'update') {
            // Substract previous grade from current rating
            if ($rating_infos['grades_number'] == 1) $rating_without_grade = 0;
            else $rating_without_grade = ($rating_infos['grades_number'] * $rating_infos['rating'] - $current_grade) / ($rating_infos['grades_number'] - 1);
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
        if ($_GET['type'] == 'scenery') {
            $object       = new Scenery($_GET['id']);
            $table        = "sceneries";
            $grades_table = "scenery_grades";
        } else if ($_GET['type'] == 'segment') {
            $object       = new Segment($_GET['id']);
            $table        = "segments";
            $grades_table = "segment_grade";
        }
        $id_entry = $_GET['type'] . "_id";

        // Get current grade
        $getCurrentGrade = $db->prepare("SELECT grade FROM {$grades_table} WHERE {$id_entry} = ? AND user_id = ?");
        $getCurrentGrade->execute(array($object->id, getConnectedUser()->id));
        $current_grade = $getCurrentGrade->fetch(PDO::FETCH_NUM);
        if ($getCurrentGrade->rowCount() == 0) throw new Exception('CURRENT GRADE NOT FOUND');

        // Remove grade entry from grade table
        $removeGrade = $db->prepare("DELETE FROM {$grades_table} WHERE {$id_entry} = ? AND user_id = ?");
        $removeGrade->execute(array($object->id, getConnectedUser()->id));
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

    if (isset($_GET['get-reviews-scenery'])) {
        $scenery = new Scenery($_GET['get-reviews-scenery']);
        $reviews = $scenery->getReviews();
        // Add profile picture src to the response
        for ($i = 0; $i < count($reviews); $i++) {
            $propic = $reviews[$i]->user->getPropicUrl();
            $reviews[$i]->propic = $propic;
        }
        echo json_encode($reviews);
    }

    if (isset($_GET['add-review-scenery'])) {
        // Prepare data
        $content = nl2br(htmlspecialchars($_GET['content']));
        $propic  = getConnectedUser()->getPropicUrl();
        $time    = (new DateTime(date('Y-m-d H:i:s'), new DateTimezone('Asia/Tokyo')));
        // Post review
        $scenery = new Scenery($_GET['add-review-scenery']);
        $scenery->postReview($content);
        // Return necessary data
        echo json_encode(['scenery_id' => $scenery->id, "user" => ["id" => getConnectedUser()->id, "login" => getConnectedUser()->login], "content" => $content, "time" => $time->format('Y-m-d H:i:s'), "propic" => $propic]);
    }

    if (isset($_GET['display-rides'])) {
        define('RIDES_DATE_RANGE', 3); // Define interval in which rides must be displayed in months
        $getRides = $db->prepare("SELECT id FROM rides
        WHERE
            (privacy = 'public')
        AND
            date BETWEEN :today AND :datemax");
        $today = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
        $getRides->execute(array(":today" => $today->format('Y-m-d'), ":datemax" => $today->modify('+' . RIDES_DATE_RANGE . ' month')->format('Y-m-d')));
        $rides = [];
        while ($ride_data = $getRides->fetch(PDO::FETCH_COLUMN)) {
            $ride = new Ride($ride_data, false);
            $ride->route = $ride->getRoute();
            if ($ride->route) { // Filter rides with no route data
                $ride->route->coordinates = $ride->route->getLinestring();
                $ride->author_login = $ride->getAuthor()->login;
                $ride->checkpoints = $ride->getCheckpoints();
                $ride->participants_number = count($ride->getParticipants());
                $ride->status = $ride->getStatus()['status'];
                array_push($rides, $ride);
            }
        }
        echo json_encode($rides);
    }

    if (isset($_GET['ride-featured-image'])) {
        $getRideFeaturedImage = $db->prepare('SELECT id FROM ride_checkpoints WHERE ride_id = ? AND featured = 1');
        $getRideFeaturedImage->execute([$_GET['ride-featured-image']]);
        $featured_image_id = $getRideFeaturedImage->fetch(PDO::FETCH_COLUMN);
        $featured_image = new CheckpointImage($featured_image_id);
        echo json_encode($featured_image);
    }

    if (isset($_GET['display-segments'])) {
        $getSegments = $db->prepare('SELECT id, route_id, rank, name, advised, popularity FROM segments ORDER BY popularity DESC');
        $getSegments->execute();
        $segments = $getSegments->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($segments); $i++) {
            // Add coordinates
            $getCoords = $db->prepare('SELECT ST_AsEWKT(linestring) FROM linestrings WHERE segment_id = ?');
            $getCoords->execute(array($segments[$i]['route_id']));
            $linestring_wkt = $getCoords->fetch(PDO::FETCH_COLUMN);
            $coordinates = new CFLinestring();
            $coordinates->fromWKT($linestring_wkt);
            $segments[$i]['coordinates'] = $coordinates->getArray();
            // Add tunnels
            $getLinestring = $db->prepare('SELECT ST_AsEWKT(linestring) FROM tunnels WHERE segment_id = ?');
            $getLinestring->execute(array($segments[$i]['route_id']));
            $tunnels = [];
            while ($linestring_wkt = $getLinestring->fetch(PDO::FETCH_COLUMN)) {
                $tunnel = new Tunnel();
                $tunnel->fromWKT($linestring_wkt);
                array_push($tunnels, $tunnel->getArray());
            }            
            $segments[$i]['tunnels'] = $tunnels;
            // Add tags
            $getTags = $db->prepare('SELECT tag FROM tags WHERE object_type = ? AND object_id = ?');
            $getTags->execute(['segment', $segments[$i]['id']]);
            $tags = $getTags->fetchAll(PDO::FETCH_COLUMN);
            $segments[$i]['tags'] = $tags;
            // Add seasons
            $getSeasons = $db->prepare('SELECT number, period_start_month, period_start_detail, period_end_month, period_end_detail, description FROM segment_seasons WHERE segment_id = ?');
            $getSeasons->execute([$segments[$i]['id']]);
            $seasons = $getSeasons->fetchAll(PDO::FETCH_ASSOC);
            $segments[$i]['seasons'] = $seasons;
        }
        echo json_encode($segments);
    }
    
    if (isset($_GET['segment-details'])) {
        $segment_id = $_GET['segment-details'];
        echo json_encode(new Segment($segment_id, false));
    }

    if (isset($_GET['segment-sceneries'])) {
        $segment = new Segment($_GET['segment-sceneries']);
        $close_sceneries = $segment->route->getLinestring()->getCloseSceneries(200);
        foreach ($close_sceneries as $scenery) $scenery->photos = $scenery->getImages();
        echo json_encode($close_sceneries);
    }
    
    if (isset($_GET['segment-public-photos'])) {
        $segment = new Segment($_GET['segment-public-photos']);
        $public_photos = $segment->route->getPublicPhotos(300);
        echo json_encode($public_photos);
    }
    
    if (isset($_GET['get-user-favorite-sceneries'])) {
        echo json_encode(getConnectedUser()->getFavorites('scenery'));
    }

    if (isset($_GET['get-icon'])) {
        $filename = $_GET['get-icon'];
        $path = $_SERVER['DOCUMENT_ROOT']. "/map/media/" .$filename;
        echo json_encode($path);
    }

    if (isset($_GET['activity-photos'])) {

        $limit = 10;

        $ne = new LngLat(explode(',', $_GET['ne'])[0], explode(',', $_GET['ne'])[1]);
        $sw = new LngLat(explode(',', $_GET['sw'])[0], explode(',', $_GET['sw'])[1]);

        $getFittingActivityPhotos = $db->prepare("SELECT p.id, a.id as activity_id, a.title, a.privacy as activity_privacy, u.login as user_login FROM activity_photos as p JOIN activities as a ON p.activity_id = a.id JOIN users as u ON u.id = p.user_id WHERE p.point IS NOT NULL AND p.privacy = 'public' AND p.point::geometry @ ST_MakeEnvelope({$ne->lng}, {$ne->lat},{$sw->lng}, {$sw->lat}, 4326) LIMIT {$limit}");
        $getFittingActivityPhotos->execute();
        $photo_ids = $getFittingActivityPhotos->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array_map(function ($data) {
            $activity_photo = new ActivityPhoto($data['id']);
            $activity_photo->activity_title = $data['title'];
            $activity_photo->activity_privacy = $data['activity_privacy'];
            $activity_photo->user_login = $data['user_login'];
            return $activity_photo;
        }, $photo_ids));

    }

}