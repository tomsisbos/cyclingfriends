<?php

$folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require $folder . '/actions/database.php';
	
// Get data from database
$query = "
	SELECT
		u.id,
		ROUND((ST_DistanceSphere(
			u.point::geometry,
			(SELECT point FROM users WHERE id = :connected_user_id)::geometry
		) / 1000)::numeric, 1) as distance,
		ST_X(u.point::geometry) as lng,
		ST_Y(u.point::geometry) as lat,
		p.filename as propic,
		u.default_profilepicture_id
	FROM users as u
	JOIN profile_pictures as p ON u.id = p.user_id
	WHERE
		u.city IS NOT NULL AND
		u.prefecture IS NOT NULL AND
		u.id NOT IN (SELECT id FROM settings WHERE hide_on_neighbours = 1) AND
		NOT u.id = :connected_user_id
	ORDER BY distance ASC
";
if (isset($limit)) $query .= "LIMIT {$limit}";
$getRiders = $db->prepare($query);
$getRiders->execute(array(':connected_user_id' => getConnectedUser()->id));
$riders_data = $getRiders->fetchAll(PDO::FETCH_ASSOC);
$riders = [];
	 
?>