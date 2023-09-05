<?php
require_once '../vendor/autoload.php';
require_once '../class/CFAutoloader.php';
CFAutoloader::register();
include_once("../vendor/phayes/geophp/geoPHP.inc");

$uri = $_SERVER['REQUEST_URI'];

$router = new AltoRouter();

/* List of targets to match */

$router->map('GET', '/dashboard', 'dashboard');

// Beta
$router->map('GET|POST', '/', 'home/home');
$router->map('GET|POST', '/privatebeta/registration/[i:token]', 'beta/registration');
$router->map('GET|POST', '/privatebeta/signup/[i:token]', 'beta/signup');
$router->map('GET|POST', '/dev/board', 'dev/board');
$router->map('GET|POST', '/dev/note/[i:note_id]', 'dev/note');

// User
$router->map('GET|POST', '/signin', 'user/signin', 'user-signin');
$router->map('GET|POST', '[*:url]/signin', 'user/signin', 'user-signin-redirect');
$router->map('GET|POST', '/signout', 'user/signout', 'user-signout');
$router->map('GET|POST', '[*:url]/signout', 'user/signout', 'user-signout-redirect');
$router->map('GET|POST', '/signup', 'user/signup', 'user-signup');
$router->map('GET|POST', '/reset-password-application', 'user/reset-password-application', 'user-reset-password-application');
$router->map('GET|POST', '/account/reset-password/[i:token]', 'user/reset-password', 'user-reset-password');
$router->map('GET|POST', '/unsubscribe', 'user/unsubscribe', 'user-unsubscribe');
$router->map('GET', '/rider/[i:user_id]', 'profile/single', 'profile-single');
$router->map('GET|POST', '/profile/edit', 'profile/edit', 'profile-edit');
$router->map('GET', '/settings', 'user/settings', 'user-settings');
$router->map('GET', '/favorites/sceneries', 'user/favorites/sceneries', 'user-favorites-sceneries');
$router->map('GET', '/favorites/segments', 'user/favorites/segments', 'user-favorites-segment');
$router->map('GET|POST', '/account/verification/guidance', 'user/verification-guidance', 'user-verification-guidance');
$router->map('GET', '/account/verification/[i:user_slug]-[*:email]', function ($user_slug, $email) {
    require_once '../actions/users/verification.php';
});
$router->map('GET', '[*:url]/account/verification/[i:user_slug]-[*:email]', function ($url, $user_slug, $email) {
    require_once '../actions/users/verification.php';
});

// Manual
$router->map('GET', '/manual', 'manual/home', 'manual');
$router->map('GET', '/manual/[a:chapter]', 'manual/single', 'manual-single');

// World
$router->map('GET', '/world', 'world/map');
$router->map('GET', '/segment/[i:segment_id]', 'segments/single', 'segment-single');
$router->map('GET|POST', '/scenery/[i:scenery_id]', 'sceneries/single', 'scenery-single');
$router->map('GET', '/tag/[a:tagcategory]-[a:tagname]', 'world/tag', 'tag');

// Activities
$router->map('GET|POST', '/activity/[i:activity_id]', 'activities/single', 'activity-single');
$router->map('GET|POST', '/activity/new', 'activities/new', 'activity-new');
$router->map('GET', '/activity/[i:activity_id]/edit', 'activities/edit', 'activity-edit');
$router->map('GET', '/activities', 'activities/publicboard', 'activity-publicboard');
$router->map('GET', '/[*:user_login]/activities', 'activities/userboard', 'activity-userboard');
$router->map('GET', '/[*:user_login]/journal', 'activities/journal', 'activity-journal');
$router->map('GET', '/journal/[i:user_id]', 'activities/journal');

// Routes
$router->map('GET', '/route/[i:route_id]', 'routes/single', 'route-single');
$router->map('GET', '/route/new', 'routes/new', 'route-new');
$router->map('GET', '/route/[i:route_id]/edit', 'routes/edit', 'route-edit');
$router->map('GET', '/[*:user_login]/routes', 'routes/userboard', 'route-userboard');
$router->map('GET', '/routes', function () { // Redirect to "/[login]/routes" when typing "/routes"
    require_once '../actions/users/initSession.php';
    require_once '../includes/functions.php';
    header('location: /' .getConnectedUser()->login. '/routes');
});

// Rides
$router->map('GET|POST', '/ride/[i:ride_id]', function ($ride_id) {
    global $router;
    $ride = new Ride($ride_id);
    // If ride date is past, or if report activity id has been set, show report page
    if (isset($ride->getReport()->activity_id)) {
        include '../templates/rides/report.php';
    // Else, show ride page
    } else include '../templates/rides/single.php';
}, 'ride-single');
$router->map('GET', '/ride/new', 'rides/new', 'ride-new');
$router->map('GET|POST', '/ride/new/[i:stage]', 'rides/new');
$router->map('GET', '/ride/[i:ride_id]/edit', 'rides/edit', 'ride-edit');
$router->map('GET|POST', '/ride/[i:ride_id]/edit/[i:stage]', 'rides/edit');
$router->map('GET', '/ride/[i:ride_id]/admin', 'rides/admin/entries', 'ride-admin');
$router->map('GET', '/ride/[i:ride_id]/admin/entries', 'rides/admin/entries', 'ride-admin-entries');
$router->map('GET|POST', '/ride/[i:ride_id]/admin/forms', 'rides/admin/forms', 'ride-admin-forms');
$router->map('GET|POST', '/ride/[i:ride_id]/admin/guides', 'rides/admin/guides', 'ride-admin-guides');
$router->map('GET|POST', '/ride/guide-requests', 'rides/guide-requests', 'ride-guide-requests');
$router->map('GET|POST', '/ride/[i:ride_id]/admin/report', 'rides/admin/report', 'ride-admin-report');
$router->map('GET|POST', '/rides', 'rides/publicboard', 'rides-publicboard');
$router->map('GET', '/ride/organizations', 'rides/organizations', 'ride-organizations');
$router->map('GET', '/ride/participations', 'rides/participations', 'ride-participations');
$router->map('GET', '/ride/[i:ride_id]/route', 'routes/single', 'ride-route');
$router->map('GET|POST', '/ride/[i:ride_id]/signup', 'rides/signup', 'rides-signup');
$router->map('GET', '/rides/calendar', 'rides/calendar', 'rides-calendar');
$router->map('GET|POST', '/ride/[i:ride_id]/entry', 'rides/entry/entry', 'ride-entry');
$router->map('GET|POST', '/ride/[i:ride_id]/payment', 'rides/entry/payment', 'ride-payment');
$router->map('GET', '/ride/[i:ride_id]/checkout', 'rides/entry/checkout', 'ride-checkout');
$router->map('GET|POST', '/ride/[i:ride_id]/guide-entry/[i:guide_id]', 'rides/entry/guide-entry', 'ride-guide-entry');
$router->map('GET', '/ride/contract', 'rides/contract', 'ride-contract');

// Community
$router->map('GET', '/community', 'community/community', 'community');
$router->map('GET|POST', '/friends', 'community/friends', 'friends');
$router->map('GET|POST', '/scouts', 'community/scouts', 'scouts');
$router->map('GET', '/neighbours', 'community/neighbours', 'neighbours');
$router->map('GET|POST', '/news', 'community/news', 'community/news');

// Company
$router->map('GET', '/company', 'company/company', 'company');
$router->map('GET', '/company/business', 'company/business', 'company-business');
$router->map('GET|POST', '/company/contact', 'company/contact', 'company-contact');
$router->map('GET', '/company/commerce-disclosure', 'company/commerce-disclosure', 'company-commerce-disclosure');

// Admin
$router->map('GET|POST', '/admin/autoposting/sceneries', '/admin/autoposting/sceneries', 'admin-autoposting-sceneries');
$router->map('GET', '/admin/garmin', '/admin/garmin', 'admin-garmin');


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