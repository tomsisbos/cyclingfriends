<?php
require '../vendor/autoload.php';
require '../class/Autoloader.php';
Autoloader::register();

$uri = $_SERVER['REQUEST_URI'];
$router = new AltoRouter();

/* List of targets to match */

$router->map('GET', '/', 'dashboard');

// Map
$router->map('GET', '/world', 'map');

// Segments
$router->map('GET', '/segment/[i:segment_id]', 'segments/single', 'segment-single');

// Scenery spots
$router->map('GET', '/scenery/[i:mkpoint_id]', 'sceneries/single', 'scenery-single');

// Activities
$router->map('GET', '/activity/[i:activity_id]', 'activities/single', 'activity-single');
$router->map('GET', '/activity/new', 'activities/new', 'activity-new');
$router->map('GET', '/activity/[i:activity_id]/edit', 'activities/edit', 'activity-edit');
$router->map('GET', '/activities', 'activities/publicboard', 'activity-publicboard');
$router->map('GET', '/[*:user_login]/activities', 'activities/userboard', 'activity-userboard');

// Routes
$router->map('GET', '/route/[i:route_id]', 'routes/single', 'route-single');
$router->map('GET', '/route/new', 'routes/new', 'route-new');
$router->map('GET', '/route/[i:route_id]/edit', 'routes/edit', 'route-edit');
$router->map('GET', '/[*:user_login]/routes', 'routes/userboard', 'route-userboard');

// Rides
$router->map('GET', '/ride/[i:ride_id]', 'rides/single', 'ride-single');
$router->map('POST', '/ride/[i:ride_id]', 'rides/single');
$router->map('GET', '/ride/[i:ride_id]/join', 'rides/single');
$router->map('GET', '/ride/[i:ride_id]/quit', 'rides/single');
$router->map('GET', '/ride/new', 'rides/new', 'ride-new');
$router->map('GET', '/ride/new/[i:stage]', 'rides/new');
$router->map('POST', '/ride/new/[i:stage]', 'rides/new');
$router->map('GET', '/ride/[i:ride_id]/edit', 'rides/edit', 'ride-edit');
$router->map('GET', '/ride/[i:ride_id]/edit/[i:stage]', 'rides/edit');
$router->map('POST', '/ride/[i:ride_id]/edit/[i:stage]', 'rides/edit');
$router->map('GET', '/rides', 'rides/publicboard', 'rides-publicboard');
$router->map('POST', '/rides', 'rides/publicboard');
$router->map('GET', '/[*:user_login]/rides', 'rides/userboard', 'rides-userboard');
$router->map('GET', '/ride/[i:ride_id]/route', 'routes/single', 'ride-route');

// Community
$router->map('GET', '/community', 'community/community', 'community');
$router->map('GET', '/friends', 'community/friends', 'friends');
$router->map('POST', '/friends', 'community/friends');
$router->map('GET', '/neighbours', 'community/neighbours', 'neighbours');

// Riders
$router->map('GET', '/rider/[i:user_id]', 'profile/single', 'profile-single');
$router->map('GET', '/profile/edit', 'profile/edit', 'profile-edit');
$router->map('POST', '/profile/edit', 'profile/edit');

// User
$router->map('GET', '/signin', 'user/signin', 'user-signin');
$router->map('POST', '/signin', 'user/signin');
$router->map('GET', '/signout', 'user/signout', 'user-signout');
$router->map('POST', '/signout', 'user/signout');
$router->map('GET', '/signup', 'user/signup', 'user-signup');
$router->map('POST', '/signup', 'user/signup');
$router->map('GET', '/settings', 'user/settings', 'user-settings');
$router->map('GET', '/favorites/sceneries', 'user/favorites/sceneries', 'user-favorites-sceneries');
$router->map('GET', '/favorites/segments', 'user/favorites/segments', 'user-favorites-segment');

// Treatment of results
$match = $router->match();
if (is_array($match)) {
    // If target is a function, call it with relevant params
    if (is_callable($match['target'])) {
        var_dump($match); die;
        call_user_func_array($match['target'], $match['params']);
    // If target is a string,
    } else {
        $params = $match['params'];
        $target = $match['target'];
        include '../includes/head.php';
        require '../templates/' . $target . '.php';
    }
} else {
    require '../templates/404.php';
}