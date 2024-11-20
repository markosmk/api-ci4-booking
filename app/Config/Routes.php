<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('api/v1', ['namespace' => 'App\Controllers'], function ($routes) {
    // Auth
    $routes->post('login', 'AuthController::login');
    $routes->get('me', 'AuthController::me', ['filter' => 'auth']);

    // Users (only superadmin)
    $routes->get('users', 'UserController::index', ['filter' => ['auth','superadmin']]);
    $routes->post('users', 'UserController::create', ['filter' => ['auth','superadmin']]);
    $routes->put('users/(:num)', 'UserController::update/$1', ['filter' => ['auth','superadmin']]);
    $routes->delete('users/(:num)', 'UserController::delete/$1', ['filter' => ['auth','superadmin']]);

    // user: only update his pass and username
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
