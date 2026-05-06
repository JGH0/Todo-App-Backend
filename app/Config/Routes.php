<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
<<<<<<< Updated upstream
=======
$routes->get('/themes', 'ThemeStore::index');
$routes->post('/themes/upload', 'ThemeStore::upload');
$routes->get('/themes/preview/(:segment)', 'ThemeStore::preview/$1');
>>>>>>> Stashed changes
