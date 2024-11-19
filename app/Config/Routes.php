<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\Controllers'], function ($routes) {
    // Auth
    $routes->post('login', 'AuthController::login');
    $routes->get('me', 'AuthController::me', ['filter' => 'auth']);

    // Users (only superadmin)
    $routes->post('users', 'UserController::create', ['filter' => 'auth']);
    $routes->get('users', 'UserController::index', ['filter' => 'auth']);
    $routes->put('users/(:num)', 'UserController::update/$1', ['filter' => 'auth']);
    $routes->delete('users/(:num)', 'UserController::delete/$1', ['filter' => 'auth']);

    // user admin: only update his pass and username
    $routes->put('users/self', 'UserController::updateSelf', ['filter' => 'auth']);

    // Reservations
    $routes->get('reservations', 'ReservationController::index', ['filter' => 'auth']);
    $routes->post('reservations', 'ReservationController::create');
    $routes->put('reservations/(:num)', 'ReservationController::update/$1', ['filter' => 'auth']);
    $routes->delete('reservations/(:num)', 'ReservationController::delete/$1', ['filter' => 'auth']);

    // Reservations Types
    $routes->get('reservation-types', 'ReservationTypeController::index');
    $routes->post('reservation-types', 'ReservationTypeController::create', ['filter' => 'auth']);
    $routes->put('reservation-types/(:num)', 'ReservationTypeController::update/$1', ['filter' => 'auth']);
    $routes->delete('reservation-types/(:num)', 'ReservationTypeController::delete/$1', ['filter' => 'auth']);
});