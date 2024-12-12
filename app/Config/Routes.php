<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('api/v1', ['namespace' => 'App\Controllers', 'filter' => 'cors'], function (RouteCollection $routes) {


    $routes->options('(:any)', static function () {
        $response = response();
        $response->setStatusCode(204);
        $response->setHeader('Allow:', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');

        return $response;
    });

    // Auth
    $routes->post('login', 'AuthController::login');
    $routes->post('logout', 'AuthController::logout');
    $routes->get('me', 'UserController::me', ['filter' => 'auth']);

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

    // $routes->resource('tours');
    $routes->get('tours', 'TourController::index');
    $routes->get('tours/(:num)', 'TourController::show/$1');
    $routes->post('tours', 'TourController::create');
    $routes->put('tours/(:num)', 'TourController::update/$1');

    // Para crear horarios y reservar, puedes usar rutas adicionales o incluirlas en el recurso
    $routes->post('schedules/(:segment)', 'ScheduleController::createSchedule/$1');
    $routes->get('schedules/(:segment)', 'ScheduleController::getSchedulesByMonth/$1');
    $routes->get('schedules/(:segment)/month/(:num)/year/(:num)', 'ScheduleController::getSchedulesByMonth/$1/$2/$3');
    $routes->get('schedules/(:segment)/date/(:any)', 'ScheduleController::getSchedulesByDate/$1/$2');
    // $routes->get('schedules/(:segment)/(:num)', 'ScheduleController::showByTourId/$1/$2');

    $routes->get('bookings', 'BookingController::index');
    $routes->get('bookings/(:num)', 'BookingController::show/$1');
    $routes->post('bookings', 'BookingController::createBookingTour');
    $routes->put('bookings/(:num)/status', 'BookingController::updateBookingStatus/$1');


    $routes->get('customers', 'CustomerController::index');
    $routes->get('customers/(:num)', 'CustomerController::show/$1');

    // Analytics
    $routes->get('dashboard/stats', 'AnalyticsController::index');
    $routes->get('dashboard/recent-bookings', 'AnalyticsController::recentsBookings');
    $routes->get('dashboard/clear-cache', 'AnalyticsController::clearCache');

    // settings
    $routes->get('settings', 'SettingsController::index', ['filter' => 'auth']);
    $routes->put('settings/(:num)', 'SettingsController::update/$1', ['filter' => 'auth']);

});
