<?php

// Get id from URL
$last_parameter = basename($_SERVER['REQUEST_URI']);
if (is_numeric($last_parameter) || $last_parameter === 'route') {

    if (is_numeric($last_parameter)) {
        if (exists('routes', $last_parameter)) $route = new Route($last_parameter);
        else header('location: /routes'); // If route doesn't exist, redirect to user routes page
    } else {
        $url_fragments = explode('/', $_SERVER['REQUEST_URI']);
        $slug = array_slice($url_fragments, -2)[0];
        if (exists('rides', $slug)) {
            $ride = new Ride($slug);
            $route = $ride->getRoute();
        } else header('location: /routes'); // If route doesn't exist, redirect to user routes page
    }
}