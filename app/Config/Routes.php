<?php

use App\Controllers\CoasterController;
use App\Controllers\WagonController;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('api', function($routes) {
    // Register a new roller coaster
    $routes->post('coasters', [CoasterController::class, 'add']);

    // Edit an existing roller coaster
    $routes->put('coasters/(:num)', [[CoasterController::class, 'upgrade'], '$1']);

    // Register a new wagon
    $routes->post('coasters/(:num)/wagons', [[WagonController::class, 'add'], '$1']);

    // Remove a wagon
    $routes->delete('coasters/(:num)/wagons/(:num)', [[WagonController::class, 'remove'], '$1/$2']);
});


