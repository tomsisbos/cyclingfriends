<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // In case a Json request have been detected
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $key     = $data->key;
    $value   = $data->value;
    $id      = $data->id;
    $bike    = new Bike();

    // If value is 'new', create a new entry in bikes table and return the corresponding bike id
    if ($id == 'new') {
        $bike->create($user->id);
        $id = $bike->id;
        
    // Else, update existing bike
    } else $bike->load($id);
    
    $bike->updateValue($key, $value);
    echo json_encode([$id, $key, $value]);


} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    
    $bike = new Bike($data->id);
    $bike->delete();
    
    echo json_encode(true);

}