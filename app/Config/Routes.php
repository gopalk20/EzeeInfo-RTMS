<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home routes
$routes->get('/', 'Home::index');
$routes->get('/users', 'Home::users');
$routes->get('/about', 'Home::about');

// Catch-all route for 404
$routes->setAutoRoute(false);
