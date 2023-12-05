<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = getEnv('JWT_SECRET_KEY');

if (empty($token)) {
    // Token is missing or invalid, return an error response
    http_response_code(401);
    echo json_encode(['error' => 'Missing token']);
    exit();
}

// Verify and decode the JWT token
try {
    $user_data = JWT::decode($token, new Key($key, 'HS256'));
    $user = new User($user_data->id);
    // JWT token is valid, you can access $decoded for user information
    
} catch (Exception $e) {
    // Token is invalid, return an error response
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit();
}