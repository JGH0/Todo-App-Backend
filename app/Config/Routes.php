<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/themes', 'ThemeStore::index');
$routes->options('/themes', static function () {
    header('Access-Control-Allow-Origin: http://localhost:5173');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept, Fetch');
    header('Access-Control-Allow-Credentials: true');
    return response()->setStatusCode(204);
});
$routes->post('/themes/upload', 'ThemeStore::upload');
$routes->options('/themes/upload', static function () {
    header('Access-Control-Allow-Origin: http://localhost:5173');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept, Fetch');
    header('Access-Control-Allow-Credentials: true');
    return response()->setStatusCode(204);
});
$routes->get('/themes/preview/(:segment)', 'ThemeStore::preview/$1');
$routes->post('/themes/install/(:segment)', 'ThemeStore::install/$1');
$routes->post('/themes/activate/(:segment)', 'ThemeStore::activate/$1');
$routes->delete('/themes/uninstall/(:segment)', 'ThemeStore::uninstall/$1');
$routes->get('/themes/my-themes', 'ThemeStore::myThemes');
$routes->get('/themes/(:segment)', 'ThemeStore::serveCss/$1');
