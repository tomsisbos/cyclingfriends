<?php

$ssl_options = array(
	PDO::MYSQL_ATTR_SSL_CA => '/bin/DigiCertGlobalRootG2.crt.pem',
	PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
);

try {
	///$db = new PDO('mysql:host=cyclingfriends-db.mysql.database.azure.com:3306; dbname=cyclingfriends_db; charset=utf8;', 'sisbos', 'bOk6oyKW', $ssl_options); 
	$db = new PDO('mysql:host=localhost; dbname=cyclingfriends; charset=utf8;', 'root', ''); 
	// Activation des erreurs PDO
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	/// Dev setting $db->setAttribute();
}
catch (exception $e) {
	die('An error has occured : ' . $e->getMessage());
} ?>
