 <?php
 
 try {
	/// Distant connection $db = new PDO('mysql:host=bt71486-001.eu.clouddb.ovh.net:35438; dbname=cyclingfriends_db; charset=utf8;', 'sisbos', 'bOk6oyKW'); 
	$db = new PDO('mysql:host=localhost; dbname=cyclingfriends; charset=utf8;', 'root', ''); 
	// Activation des erreurs PDO
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	/// Dev setting $db->setAttribute();
 }
 catch (exception $e) {
	die('An error has occured : ' . $e->getMessage());
 }

 ?>
 