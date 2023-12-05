<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $user_id = intval($_GET['id']);

    // Get user data
    $getUserData = $db->prepare("SELECT
        u.id,
        u.login,
        u.default_profilepicture_id,
        u.inscription_date,
        u.first_name,
        u.last_name,
        u.gender,
        u.birthdate,
        u.city,
        u.prefecture,
        u.description,
        ST_X(u.point::geometry)::double precision as lng,
        ST_Y(u.point::geometry)::double precision as lat,
        pp.filename as propic,
        CASE WHEN s.hide_realname = 1 THEN true
            WHEN s.hide_realname = 0 THEN false
            ELSE null
        END as hide_realname,
        CASE WHEN s.hide_age = 1 THEN true
            WHEN s.hide_age = 0 THEN false
            ELSE null
        END as hide_age,
        CASE WHEN f.following_id IS NOT NULL THEN true
            ELSE false
        END as follows
    FROM users AS u
    LEFT JOIN profile_pictures AS pp ON u.id = pp.user_id
    LEFT JOIN settings AS s ON u.id = s.id
    LEFT JOIN followers AS f ON u.id = f.followed_id AND ? = f.following_id
    WHERE u.id = ?
    ");
    $getUserData->execute([$user->id, $user_id]);
    $data = $getUserData->fetch(PDO::FETCH_ASSOC);

    $user = new User($data['id']);

    $data['bikes'] = array_map(function ($bike_id) {
        return new Bike($bike_id);
    }, $user->getBikes());

    // Get following
    $getFollowing = $db->prepare("SELECT DISTINCT
        f.followed_id as user_id,
        u.login,
        u.default_profilepicture_id as default_propic_id,
        pp.filename as propic
    FROM followers AS f
    INNER JOIN users AS u ON f.followed_id = u.id
    INNER JOIN profile_pictures AS pp ON f.followed_id = pp.user_id
    WHERE f.following_id = ?
    ");
    $getFollowing->execute([$user_id]);
    $data['following'] = $getFollowing->fetchAll(PDO::FETCH_ASSOC);

    // Get followers
    $getFollowers = $db->prepare("SELECT DISTINCT
        f.following_id as user_id,
        u.login,
        u.default_profilepicture_id as default_propic_id,
        pp.filename as propic
    FROM followers AS f
    INNER JOIN users AS u ON f.following_id = u.id
    INNER JOIN profile_pictures AS pp ON f.following_id = pp.user_id
    WHERE f.followed_id = ?
    ");
    $getFollowers->execute([$user_id]);
    $data['followers'] = $getFollowers->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);

}