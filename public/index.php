<?php
require_once '../vendor/autoload.php';
require_once '../class/Autoloader.php';
Autoloader::register();

$uri = $_SERVER['REQUEST_URI'];
$router = new AltoRouter();

/* List of targets to match */

$router->map('GET', '/dashboard', 'dashboard');

// Beta
$router->map('GET', '/', 'beta/home');
$router->map('POST', '/', 'beta/home');
$router->map('GET', '/privatebeta/registration/[i:token]', 'beta/registration');
$router->map('POST', '/privatebeta/registration/[i:token]', 'beta/registration');
$router->map('GET', '/privatebeta/signup/[i:token]', 'beta/signup');
$router->map('POST', '/privatebeta/signup/[i:token]', 'beta/signup');
$router->map('GET', '/dev/board', 'dev/board');
$router->map('POST', '/dev/board', 'dev/board');
$router->map('GET', '/dev/note/[i:note_id]', 'dev/note');
$router->map('POST', '/dev/note/[i:note_id]', 'dev/note');

// User
$router->map('GET', '/signin', 'user/signin', 'user-signin');
$router->map('POST', '/signin', 'user/signin');
$router->map('GET', '/signout', 'user/signout', 'user-signout');
$router->map('POST', '/signout', 'user/signout');
$router->map('GET', '/signup', 'user/signup', 'user-signup');
$router->map('POST', '/signup', 'user/signup');
$router->map('GET', '/unsubscribe', 'user/unsubscribe', 'user-unsubscribe');
$router->map('POST', '/unsubscribe', 'user/unsubscribe');
$router->map('GET', '/rider/[i:user_id]', 'profile/single', 'profile-single');
$router->map('GET', '/profile/edit', 'profile/edit', 'profile-edit');
$router->map('POST', '/profile/edit', 'profile/edit');
$router->map('GET', '/settings', 'user/settings', 'user-settings');
$router->map('GET', '/favorites/sceneries', 'user/favorites/sceneries', 'user-favorites-sceneries');
$router->map('GET', '/favorites/segments', 'user/favorites/segments', 'user-favorites-segment');

// Manual
$router->map('GET', '/manual', 'manual/home', 'manual');
$router->map('GET', '/manual/[a:chapter]', 'manual/single', 'manual-single');

// World
$router->map('GET', '/world', 'world/map');
$router->map('GET', '/segment/[i:segment_id]', 'segments/single', 'segment-single');
$router->map('GET', '/scenery/[i:mkpoint_id]', 'sceneries/single', 'scenery-single');
$router->map('GET', '/tag/[a:tag]', 'world/tag', 'tag');

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
$router->map('GET', '/routes', function () { // Redirect to "/[login]/routes" when typing "/routes"
    require_once '../actions/users/initSessionAction.php';
    header('location: /' .$connected_user->login. '/routes');
} );

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
$router->map('GET', '/ride/[i:ride_id]/admin', 'rides/admin/entries', 'ride-admin');
$router->map('GET', '/ride/[i:ride_id]/admin/entries', 'rides/admin/entries', 'ride-admin-entries');
$router->map('GET', '/ride/[i:ride_id]/admin/forms', 'rides/admin/forms', 'ride-admin-forms');
$router->map('POST', '/ride/[i:ride_id]/admin/forms', 'rides/admin/forms');
$router->map('GET', '/rides', 'rides/publicboard', 'rides-publicboard');
$router->map('POST', '/rides', 'rides/publicboard');
$router->map('GET', '/[*:user_login]/rides', 'rides/userboard', 'rides-userboard');
$router->map('GET', '/ride/[i:ride_id]/route', 'routes/single', 'ride-route');

// Community
$router->map('GET', '/community', 'community/community', 'community');
$router->map('GET', '/friends', 'community/friends', 'friends');
$router->map('POST', '/friends', 'community/friends');
$router->map('GET', '/scouts', 'community/scouts', 'scouts');
$router->map('POST', '/scouts', 'community/scouts');
$router->map('GET', '/neighbours', 'community/neighbours', 'neighbours');
$router->map('GET', '/news', 'community/news', 'community/news');
$router->map('POST', '/news', 'community/news');

// Treatment of results
$match = $router->match();
if (is_array($match)) {
    // If target is a function, call it with relevant params
    if (is_callable($match['target'])) {
        call_user_func_array($match['target'], $match['params']);
    // If target is a string,
    } else {
        $params = $match['params'];
        $target = $match['target'];
        require '../templates/' . $target . '.php';
    }
} else require '../templates/404.php';