<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// ============================================================================
// API Routes - Version 1.0
// ============================================================================

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
