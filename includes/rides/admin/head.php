<?php

include '../actions/users/initSession.php';

// Get ride from slug
$slug = basename($_SERVER['REQUEST_URI']);
$url_fragments = explode('/', $_SERVER['REQUEST_URI']);
foreach ($url_fragments as $fragment) {
    if (is_numeric($fragment)) $slug = $fragment;
}
if (isset($slug)) $ride = new Ride($slug);
else header('location: /' . getConnectedUser()->login . '/rides');

// Only allow access to ride admin and ride guides
if (!getConnectedUser()->hasAdministratorRights() AND getConnectedUser()->id != $ride->author_id AND !in_array(getConnectedUser()->id, array_map(function ($guide) { return $guide->id; }, $ride->getGuides()))) header('location: ' .$router->generate('ride-organizations')) ?>

<link rel="stylesheet" href="/assets/css/ride.css" />