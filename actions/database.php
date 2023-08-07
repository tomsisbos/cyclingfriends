<?php

$ssl_options = array(
	PDO::MYSQL_ATTR_SSL_CA => '/bin/DigiCertGlobalRootG2.crt.pem',
	PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
);

if (!isset($db_name) OR $db_name == null) $db_name = getenv('DB_NAME');

try {
	$db = new PDO('mysql:host='.getenv('DB_HOST').'; dbname='.$db_name.'; charset=utf8;', getenv('DB_USER'), getenv('DB_PASSWORD'), $ssl_options); 
	///$db = new PDO('mysql:host=localhost; dbname=cyclingfriends; charset=utf8;', 'root', ''); 
	// Activation des erreurs PDO
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	/// Dev setting $db->setAttribute();
}
catch (exception $e) {
	die('An error has occured : ' . $e->getMessage());
} ?>