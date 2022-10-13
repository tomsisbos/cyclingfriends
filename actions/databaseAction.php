 <?php
 
 try{
	$db = new PDO('mysql:host=localhost; dbname=cyclingfriends; charset=utf8;', 'root', ''); 
	// Activation des erreurs PDO
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 }
 catch(exception $e){
	die('An error has occured : ' . $e->getMessage());
 }

 ?>
 