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
require_once __DIR__ . '/../src/controllers/autor.php';
require_once __DIR__ . '/../src/controllers/lector.php';
require_once __DIR__ . '/../src/controllers/libro.php';
require_once __DIR__ . '/../src/controllers/lectura_libro.php';
require_once __DIR__ . '/../src/controllers/autor_libro.php';

use App\Router;
$route=new Router();
// Rutas de usuarios
$route->add('POST','/login','UserController@login');
$route->add('GET','/','UserController@getAll');
$route->add('GET','/users','UserController@getAll');
$route->add('POST','/users','UserController@add');
$route->add('PUT','/users/{id}','UserController@update');
$route->add('DELETE','/users/{id}','UserController@delete');

// Autores
foreach (['/autores', '/autor'] as $path) {
    $route->add('GET', $path, 'autorController@getAll');
    $route->add('POST', $path, 'autorController@add');
    $route->add('PUT', $path . '/{id}', 'autorController@update');
    $route->add('DELETE', $path . '/{id}', 'autorController@delete');
}

// Lectores
foreach (['/lectores', '/lector'] as $path) {
    $route->add('GET', $path, 'lectorController@getAll');
    $route->add('POST', $path, 'lectorController@add');
    $route->add('PUT', $path . '/{id}', 'lectorController@update');
    $route->add('DELETE', $path . '/{id}', 'lectorController@delete');
}

// Libros
foreach (['/libros', '/libro'] as $path) {
    $route->add('GET', $path, 'libroController@getAll');
    $route->add('POST', $path, 'libroController@add');
    $route->add('PUT', $path . '/{id}', 'libroController@update');
    $route->add('DELETE', $path . '/{id}', 'libroController@delete');
}

// Rutas de lectura de libros
$route->add('GET','/lecturas-libro','lectura_librocontroller@getAll');
$route->add('GET','/lectura-libro','lectura_librocontroller@getAll');

// Rutas de relación autor-libro
$route->add('GET','/autor-libro','autor_librocontroller@getAll');
$route->add('GET','/autor_lector','autor_librocontroller@getAll');

$route->run();