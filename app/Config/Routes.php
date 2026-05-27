<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', static function () {
    return redirect()->to('/themes');
});
$routes->get('/themes', 'ThemeStore::index');
$routes->post('/themes/upload', 'ThemeStore::upload');
$routes->get('/themes/preview/(:segment)', 'ThemeStore::preview/$1');

// ============================================================================
// API Routes - Version 1.0
// ============================================================================

// Catch-all CORS preflight handler for all API routes
$routes->options('api/v1/(:any)', function () {
    $response = service('response');
    return $response->setStatusCode(200)
        ->setHeader('Access-Control-Allow-Origin', '*')
        ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key');
});

// Public endpoints (no authentication required)
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1', 'filter' => 'cors'], function ($routes) {
    // Authentication
    $routes->options('auth/register', 'AuthController::options');
    $routes->post('auth/register', 'AuthController::register');
    $routes->options('auth/login', 'AuthController::options');
    $routes->post('auth/login', 'AuthController::login');
    $routes->options('auth/api-key', 'AuthController::options');
    $routes->post('auth/api-key', 'AuthController::createApiKey');
    
    // Marketplace - Public access
    $routes->get('marketplace/themes', 'MarketplaceController::index');
    $routes->get('marketplace/themes/(:num)', 'MarketplaceController::show/$1');
    
    // JWT Authentication
    $routes->post('auth/jwt/register', 'AuthController::jwtRegister');
    $routes->post('auth/jwt/login', 'AuthController::jwtLogin');
    $routes->post('auth/jwt/refresh', 'AuthController::jwtRefresh');
});

// Protected endpoints (API key authentication required)
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1', 'filter' => ['cors', 'apiauth']], function ($routes) {
    // User endpoints
    $routes->get('user/profile', 'UserController::profile');
    $routes->put('user/profile', 'UserController::updateProfile');
    $routes->get('user/api-keys', 'UserController::listApiKeys');
    $routes->post('user/api-keys', 'UserController::createApiKey');
    $routes->delete('user/api-keys/(:segment)', 'UserController::revokeApiKey/$1');
    
    // Categories
    $routes->get('categories', 'CategoryController::index');
    $routes->post('categories', 'CategoryController::create');
    $routes->get('categories/(:segment)', 'CategoryController::show/$1');
    $routes->put('categories/(:segment)', 'CategoryController::update/$1');
    $routes->delete('categories/(:segment)', 'CategoryController::delete/$1');
    
    // Projects
    $routes->get('projects', 'ProjectController::index');
    $routes->post('projects', 'ProjectController::create');
    $routes->get('projects/(:segment)', 'ProjectController::show/$1');
    $routes->put('projects/(:segment)', 'ProjectController::update/$1');
    $routes->delete('projects/(:segment)', 'ProjectController::delete/$1');
    
    // Todos
    $routes->get('todos', 'TodoController::index');
    $routes->post('todos', 'TodoController::create');
    $routes->get('todos/(:segment)', 'TodoController::show/$1');
    $routes->put('todos/(:segment)', 'TodoController::update/$1');
    $routes->delete('todos/(:segment)', 'TodoController::delete/$1');
    $routes->post('todos/(:segment)/categories', 'TodoController::addCategory/$1');
    $routes->delete('todos/(:segment)/categories/(:segment)', 'TodoController::removeCategory/$1/$2');
    
    // Recurring Tasks
    $routes->get('recurring-tasks', 'RecurringTaskController::index');
    $routes->post('recurring-tasks', 'RecurringTaskController::create');
    $routes->get('recurring-tasks/(:segment)', 'RecurringTaskController::show/$1');
    $routes->put('recurring-tasks/(:segment)', 'RecurringTaskController::update/$1');
    $routes->delete('recurring-tasks/(:segment)', 'RecurringTaskController::delete/$1');
    $routes->post('recurring-tasks/(:segment)/categories', 'RecurringTaskController::addCategory/$1');
    $routes->delete('recurring-tasks/(:segment)/categories/(:segment)', 'RecurringTaskController::removeCategory/$1/$2');
    
    // Activity Logs
    $routes->get('activity-logs', 'ActivityLogController::index');
    $routes->get('activity-logs/(:segment)', 'ActivityLogController::show/$1');
    
    // User Themes
    $routes->get('user/themes', 'UserThemeController::index');
    $routes->post('user/themes', 'UserThemeController::create');
    $routes->put('user/themes/(:segment)', 'UserThemeController::update/$1');
    $routes->delete('user/themes/(:segment)', 'UserThemeController::delete/$1');
});
$routes->get('/themes', 'ThemeStore::index');
$routes->options('/themes', static function () {
    $origin = service('request')->getHeaderLine('Origin') ?: '*';
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
    header('Vary: Origin');
    return response()->setStatusCode(204);
});
$routes->post('/themes/upload', 'ThemeStore::upload');
$routes->options('/themes/upload', static function () {
    $origin = service('request')->getHeaderLine('Origin') ?: '*';
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
    header('Vary: Origin');
    return response()->setStatusCode(204);
});
$routes->get('/themes/preview/(:segment)', 'ThemeStore::preview/$1');
