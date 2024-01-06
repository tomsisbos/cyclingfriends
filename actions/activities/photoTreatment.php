
<?php

// Get blob ready to upload
$temp_image = new TempImage($photo['name']);
$activity_photo_data['blob'] = $temp_image->treatBase64($photo['blob']);

// Build photo data
$activity_photo_data['user_id'] = $author_id;
$activity_photo_data['activity_id'] = $activity_id;
$activity_photo_data['size'] = $photo['size'];
$activity_photo_data['name'] = $photo['name'];
$activity_photo_data['type'] = $photo['type'];
$activity_photo_data['lng'] = $photo['lng'];
$activity_photo_data['lat'] = $photo['lat'];
$activity_photo_data['datetime'] = intval($photo['datetime']);
$activity_photo_data['elevation'] = intval($photo['elevation']);
if ($photo['featured'] == true) $activity_photo_data['featured'] = 1;
else $activity_photo_data['featured'] = 0;
$activity_photo_data['privacy'] = $photo['privacy'];

$activity_photo = new ActivityPhoto();
$activity_photo->create($activity_photo_data);