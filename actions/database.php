<?php

if (!isset($db_name) OR $db_name == null) $db_name = getenv('DB_NAME');

try {

	$dsn = 'pgsql:host='.getenv('DB_POSTGRESQL_HOST').';port=5432;dbname='.$db_name;
	$db = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'));

	// Activation des erreurs PDO
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (exception $e) {

	die('An error has occured : ' . $e->getMessage());

} ?>

