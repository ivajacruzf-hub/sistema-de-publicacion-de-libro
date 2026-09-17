<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../src/router.php';
require_once __DIR__ . '/../src/controllers/UserController.php';
require_once __DIR__ . '/../src/controllers/ProductoController.php';

use App\Router;
$route=new Router();
// Rutas de usuarios
$route->add('GET','/','UserController@getAll');
$route->add('GET','/users','UserController@getAll');
$route->add('POST','/users','UserController@add');
$route->add('PUT','/users/{id}','UserController@update');
$route->add('DELETE','/users/{id}','UserController@delete');

// Rutas de productos
$route->add('GET','/productos','ProductoController@getAll');
$route->add('PUT','/productos/{id}','ProductoController@update');
$route->add('POST','/productos','ProductoController@add');
$route->add('DELETE','/productos/{id}','ProductoController@delete');





$route->run();


