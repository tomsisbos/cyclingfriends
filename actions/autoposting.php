<?php

// Database connection
$ssl_options = array(
	PDO::MYSQL_ATTR_SSL_CA => '/bin/DigiCertGlobalRootG2.crt.pem',
	PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
);

if (!isset($db_name) OR $db_name == null) $db_name = getenv('DB_NAME');

try {
	$db = new PDO('mysql:host='.getenv('DB_HOST').'; dbname='.$db_name.'; charset=utf8;', getenv('DB_USER'), getenv('DB_PASSWORD'), $ssl_options); 
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (exception $e) {
	die('An error has occured : ' . $e->getMessage());
}


// Function
$getSceneryToPost = $db->prepare("SELECT id, text, media1, media2, media3, media4 FROM autoposting WHERE entry_type = 'scenery' LIMIT 1 ORDER BY id DESC");
$getSceneryToPost->execute();
$data = $getSceneryToPost->fetch(PDO::FETCH_ASSOC);
var_dump($data);
