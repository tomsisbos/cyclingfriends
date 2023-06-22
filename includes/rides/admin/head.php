<?php

include '../actions/users/initSessionAction.php';

// Get ride from slug
$slug = basename($_SERVER['REQUEST_URI']);
$url_fragments = explode('/', $_SERVER['REQUEST_URI']);
foreach ($url_fragments as $fragment) {
    if (is_numeric($fragment)) $slug = $fragment;
}
if (isset($slug)) $ride = new Ride($slug);
else header('location: /' . $connected_user->login . '/rides');

// Only allow access to ride admin and ride guides
if ($connected_user->id != $ride->author_id AND !in_array($connected_user->id, array_map(function ($guide) { return $guide->id; }, $ride->getGuides()))) header('location: ' .$router->generate('ride-organizations')) ?>

<link rel="stylesheet" href="/assets/css/ride.css" />