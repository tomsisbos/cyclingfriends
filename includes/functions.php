<?php

use League\ColorExtractor\Palette;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;

// Get root folder
function root () {
	return substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));	
}

/**
 * Check whether an user is currently connected or not
 * @return boolean
 */
function isSessionActive () {
	if (isset($_SESSION['auth']) && $_SESSION['auth'] != 0) return true;
	else return false;
}

/**
 * Get connected user instance if connected, else throw exception
 * @return User
 */
function getConnectedUser () {
	if (isset($_SESSION['id']) && $_SESSION['id'] > 0) return new User($_SESSION['id']);
	else return false;
	///else throw new Exception('セッションが切れてしまいました。お手数ですが、再度ログインしてください。');
}

// Check for an AJAX request
function isAjax () {
	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Function for replacing <br /> tags with new lines
function br2nl($input){
	    return preg_replace('/<br\s?\/?>/ius', "\n", str_replace("\n","",str_replace("\r","", htmlspecialchars_decode($input))));
}

// Reset keys of an array to 0,1,2,3...
function reset_keys($array){
	$i = 0;
	$new_array = array();
	foreach ($array as $key => $value){
		$new_array[$i++] = $value;
	}
	return $new_array;
}


// Function for getting current page URL
function getCurrentPageUrl () {
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
	{
		$url = "https";
	}
	else
	{
		$url = "http"; 
	}  
	$url .= "://"; 
	$url .= $_SERVER['HTTP_HOST']; 
	$url .= $_SERVER['REQUEST_URI']; 
	return $url; 
}

// Function for checking whether a value is found in a multidimensional array or not
function in_array_r ($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }
    return false;
}

// Function for checking whether a key is found in a multidimensional array or not
function in_array_key_r($needle, $haystack) {

    // is in base array?
    if (array_key_exists($needle, $haystack)) {
        return true;
    }

    // check arrays contained in this array
    foreach ($haystack as $element) {
        if (is_array($element)) {
            if (in_array_key_r($needle, $element)) {
                return true;
            }
        }

    }

    return false;
}

// Update session settings to the latest data
function updateSessionSettings() {
	$_SESSION['settings'] = getConnectedUserSettings();
}


// Check if ride name is already set in the database
function checkIfRideIsAlreadySet($ride_name) {
	require '../actions/database.php';
	// Get all ride_names from the database
	$getRideInfos = $db->prepare('SELECT name FROM rides');
	$getRideInfos->execute();
	// Compare every ride name to the ride name parameter and returns true if finds the same, else return false after the loop
	while ($currentRideTable = $getRideInfos->fetch()) {
		if ($currentRideTable['name'] == $ride_name) return true;
	}
	return false;
}

// Build a level list made of array values separated by commas
function levelFromArray($array){
	$i = 0; $string = '';
	foreach ($array as $key => $value) {
		if ($value) {
			if ($value == 'Anyone') return 'レベル問わず';
			// Insert commas between level
			if ($i > 0) $string .= ', ';
			$string .= getLevelFromKey($value);
			$i++;
		}
	}
	return $string;
}

// Change the level key number to the proper level name
function getLevelFromKey ($key) {
	if ($key == 0) return '誰でも可';
	if ($key == 1) return '初心者';
	if ($key == 2) return '中級者';
	if ($key == 3) return '上級者';
}

// Change the level column name to the proper level name
function getLevelFromColumnName ($level) {
	if ($level == 'level_beginner') return '初心者';
	if ($level == 'level_intermediate') return '中級者';
	if ($level == 'level_athlete') return '上級者';
}

// Build a bikes list made of array values separated by commas
function bikesFromArray ($array) {
	$i = 0; $string = '';
	foreach ($array as $key => $value) {
		if ($value) {
			if ($value == 'All bikes') return '車種問わず';
			// Insert commas between level
			if ($i > 0) {
				$string .= ', ';
			}
			$string .= getBikeFromKey($value);
			$i++;
		}
	}
	return $string;
}

function getTerrainFromValue ($value) {
	switch ($value) {
		case 1: return '<img class="terrain-icon" src="\media\flat.svg" />';
		case 'Flat': return '<img class="terrain-icon" src="\media\flat.svg" />';
		case 2: return '<img class="terrain-icon" src="\media\smallhills.svg" />';
		case 'Small hills': return '<img class="terrain-icon" src="\media\smallhills.svg" />';
		case 3: return '<img class="terrain-icon" src="\media\hills.svg" />';
		case 'Hills': return '<img class="terrain-icon" src="\media\hills.svg" />';
		case 4: return '<img class="terrain-icon" src="\media\mountain.svg" />';
		case 'Mountain': return '<img class="terrain-icon" src="\media\mountain.svg" />';
		default : return ''; 
	}
}

// Change the bike key name to the proper bike type name
function getBikeFromKey ($key) {
	switch ($key) {
		case 0: return '車種問わず';
		case 1: return 'ママチャリ＆その他の自転車';
		case 2: return 'ロードバイク';
		case 3: return 'マウンテンバイク';
		case 4: return 'グラベル＆シクロクロスバイク';
	}
}

// Change the bike column name to the proper bike name
function getBikesFromColumnName ($bike) {
	switch ($bike) {
		case 'citybike': return 'ママチャリ＆その他の自転車';
		case 'roadbike': return 'ロードバイク';
		case 'mountainbike': return 'マウンテンバイク';
		case 'gravelcxbike': return 'グラベル＆シクロクロスバイク';
	}
}

// Delete a bike from the users table
function deleteBike ($bike_number, $user_id) {
	require '../actions/database.php';
	if (checkIfBikeIsSet($bike_number, $user_id)) {
		$deleteBike = $db->prepare('DELETE FROM bikes WHERE user_id = ? AND bike_number = ?');
		$deleteBike->execute(array($user_id, $bike_number));
		$success = 'バイクが削除しました。';
		return array(true, $success);
	}
}

// Add a bike into the users table
function addBike ($bike_number, $user_id) {
	require '../actions/database.php';
	if (!checkIfBikeIsSet($bike_number, $user_id)) {
		$addBike = $db->prepare('INSERT INTO bikes (user_id, bike_number) VALUES (?, ?)');
		$addBike->execute(array($user_id, $bike_number));
		$success = 'バイクが追加されました。';
		return array(true, $success);
	}
}

// Get the gender of an user and return it as an icon
function getGenderAsIcon($user_id){
	require '../actions/database.php';
	$getGender = $db->prepare('SELECT gender FROM users WHERE id = ?');
	$getGender->execute(array($user_id));
	$gender = $getGender->fetch();
	if($gender = 'Man'){
		return '<span class="iconify" style="color: #00adff;" data-icon="el:male" data-width="20" data-height="20"></span>';
	}else if($gender = 'Woman'){
		return '<span class="iconify" style="color: #ff6666;" data-icon="el:female" data-width="20" data-height="20"></span>';
	}
	
}

// Function for calculating the number of remaining days to a certain date
function nbDaysLeftToDate($date) {
	$currentdate = time();
	$daysleft = (strtotime($date) / 86400) - ($currentdate / 86400);
	return ceil($daysleft);
}

// Get the period (early/mid/late + month) from a date
function getPeriod($date) {
	// Get part of the month from the day
	$day = date("d", strtotime($date));
	if ($day < 10) $third = "上旬";
	else if (($day >= 10) AND ($day <= 20)) $third = "中旬";
	else if ($day > 20) $third = "下旬";

	// Get month in letters
	switch (date("n", strtotime($date))) {
		case 1: $month = "1月"; break;
		case 2: $month = "2月"; break;
		case 3: $month = "3月"; break;
		case 4: $month = "4月"; break;
		case 5: $month = "5月"; break;
		case 6: $month = "6月"; break;
		case 7: $month = "7月"; break;
		case 8: $month = "8月"; break;
		case 9: $month = "9月"; break;
		case 10: $month = "10月"; break;
		case 11: $month = "11月"; break;
		case 12: $month = "12月"; 
	}

	return $month . $third;
}

// Scale image file and save it as a blob
function scaleImageFileToBlob($file, $max_width, $max_height) {

    $source_pic = $file;

    list($width, $height, $image_type) = getimagesize($file);

    switch ($image_type)
    {
        case 1: $src = imagecreatefromgif($file); break;
        case 2: $src = imagecreatefromjpeg($file);  break;
        case 3: $src = imagecreatefrompng($file); break;
        default: return '';  break;
    }

    $x_ratio = $max_width / $width;
    $y_ratio = $max_height / $height;

    if( ($width <= $max_width) && ($height <= $max_height) ){
        $tn_width = $width;
        $tn_height = $height;
        }elseif (($x_ratio * $height) < $max_height){
            $tn_height = ceil($x_ratio * $height);
            $tn_width = $max_width;
        }else{
            $tn_width = ceil($y_ratio * $width);
            $tn_height = $max_height;
    }

    $tmp = imagecreatetruecolor($tn_width,$tn_height);

    /* Check if this image is PNG or GIF, then set if Transparent*/
    if(($image_type == 1) OR ($image_type==3))
    {
        imagealphablending($tmp, false);
        imagesavealpha($tmp,true);
        $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
        imagefilledrectangle($tmp, 0, 0, $tn_width, $tn_height, $transparent);
    }
    imagecopyresampled($tmp,$src,0,0,0,0,$tn_width, $tn_height,$width,$height);

    /*
     * imageXXX() only has two options, save as a file, or send to the browser.
     * It does not provide you the oppurtunity to manipulate the final GIF/JPG/PNG file stream
     * So I start the output buffering, use imageXXX() to output the data stream to the browser,
     * get the contents of the stream, and use clean to silently discard the buffered contents.
     */
    ob_start();

    switch ($image_type)
    {
        case 1: imagegif($tmp); break;
        case 2: imagejpeg($tmp, NULL, 100);  break; // best quality
        case 3: imagepng($tmp, NULL, 0); break; // no compression
        default: echo ''; break;
    }

    $final_image = ob_get_contents();

    ob_end_clean();

    return $final_image;
}

// Adds treating of orientation to GD original 'imagecreatefromjpeg' function 
function imagecreateexif ($filename) {
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	if ($ext == 'jpg' || $ext == 'jpeg') {
		$img = imagecreatefromjpeg($filename);
		$exif = exif_read_data($filename);
	} else if ($ext == 'png') $img = imagecreatefrompng($filename);
	else throw new Error('*.' .$ext. 'ファイル形式に対応していません。');
	if (isset($img) && isset($exif) && isset($exif['Orientation']))
	{
		$ort = $exif['Orientation'];

		if ($ort == 6 || $ort == 5)
			$img = imagerotate($img, 270, null);
		if ($ort == 3 || $ort == 4)
			$img = imagerotate($img, 180, null);
		if ($ort == 8 || $ort == 7)
			$img = imagerotate($img, 90, null);

		if ($ort == 5 || $ort == 4 || $ort == 7)
			imageflip($img, IMG_FLIP_HORIZONTAL);
	}
	return $img;
}

function setPopularity ($rating, $grades_number) {
    // Set ratingScore
    if ($rating == null) { // If no rating data, set default to 3
        $rating = 3;
    } else {
        $rating = intval($rating);
    }
    $ratingScore = $rating * 10;

    // Set numberScore
    if ($grades_number == 0) { // If no grade, 
        $numberScore = 1;
    } else {
        $grades_number = intval($grades_number);
        $numberScore = log($grades_number, 6) + 2;
    }

    // Set popularity
    $popularity = $ratingScore * $numberScore;

    return $popularity;
}

// Check if data is a base 64 encoded string or not
function is_base64_encoded ($data) {
    if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data)) {
       return true;
    } else {
       return false;
    }
};

// Check if $file extension if included in $extensions list
function checkFileExtension ($extensions, $file) {
	$file_parts = pathinfo($file);
	// If file extension is included in $extensions list, return it
	foreach ($extensions as $ext) {
		if ($file_parts['extension'] == $ext) {
			return $ext;
		}
	}
	// Else, return false
	return false;
}

function base64_to_jpeg ($base64_string, $output_file) {
    // Open the output file for writing
    $ifp = fopen($output_file, 'wb'); 

    // Split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    if (strpos($base64_string, ',')) $data = explode(',', $base64_string)[1];
	else $data = $base64_string;

    // We could add validation here with ensuring count( $data ) > 1
    fwrite($ifp, base64_decode($data));

    // Clean up the file resource
    fclose($ifp); 

    return $output_file; 
}

function getStars ($number) {
	$string = '';
	for ($i = 0; $i < $number; $i++) {
		$string .= '<div class="d-inline selected-star">★</div>';
	}
	return $string;
}

function getMainColor ($image) {
	// Get content from image regardless from whether an url or a base 64 string
	if (filter_var($image, FILTER_VALIDATE_URL)) $content = imagecreatefromjpeg($image);
	else if (substr($image, 0, 6) == '/media') return '#eeeeee';
	else $content = imagecreatefromstring(base64_decode($image));
	// Get main color from content
	if (imagesx($content) > 200) {
		$image = imagescale($content, 50);
		$palette = Palette::fromGD($image);
	} else $palette = Palette::fromContents(base64_decode($image));
	$extractor = new ColorExtractor($palette);
	$colors = $extractor->extract(1);
	return Color::fromIntToHex($colors[0]);
}

function luminanceLight($hexcolor, $percent) {
	if ( strlen( $hexcolor ) < 6 ) {
		$hexcolor = $hexcolor[0] . $hexcolor[0] . $hexcolor[1] . $hexcolor[1] . $hexcolor[2] . $hexcolor[2];
	}
	$hexcolor = array_map('hexdec', str_split( str_pad( str_replace('#', '', $hexcolor), 6, '0' ), 2 ) );

	foreach ($hexcolor as $i => $color) {
		$from = $percent < 0 ? 0 : $color;
		$to = $percent < 0 ? $color : 255;
		$pvalue = ceil( ($to - $from) * $percent );
		$hexcolor[$i] = str_pad( dechex($color + $pvalue), 2, '0', STR_PAD_LEFT);
	}

	return '#' . implode($hexcolor);
}

function setFilename ($prefix, $ext = 'jpg') {
	$filename = $prefix . '_' . rand(0, 999999999999) . '.' .$ext;
	return $filename;
}

function getNextAutoIncrement ($table_name) {
	require root(). '/actions/database.php';
    $getTableStatus = $db->prepare("SHOW TABLE STATUS LIKE '{$table_name}'");
    $getTableStatus->execute();
    return $getTableStatus->fetchAll(PDO::FETCH_ASSOC)[0]['Auto_increment'];
}

function exists ($table, $id) {
	require root(). '/actions/database.php';
    $checkIfExists = $db->prepare("SELECT id FROM {$table} WHERE id = ?");
    $checkIfExists->execute(array($id));
	if ($checkIfExists->rowCount() > 0) return true;
	else return false;
}

function getWeekDay ($date) {
	switch ($date->format('w')) {
		case 0: return '日';
		case 1: return '月';
		case 2: return '火';
		case 3: return '水';
		case 4: return '木';
		case 5: return '金';
		case 6: return '土';
	}
}

/**
 * Get a correctly populated DateInterval from a timestamp
 * @param int $timespan Timestamp (timespan)
 * @return DateInterval
 */
function timestampToDateInterval ($timespan) {
	$d1 = new DateTime();
	$d2 = new DateTime();
	$d2->add(new DateInterval('PT' .$timespan. 'S'));
	return $d2->diff($d1);
}

/**
 * Calculate average of all non empty values of an array
 * @param array $array
 * @return float
 */
function avg ($array) {
	$array = array_filter($array);
	if (count($array)) return array_sum($array) / count($array);
} ?>