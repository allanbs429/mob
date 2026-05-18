<?php

declare(strict_types=1);

$app->get('/',      app\controller\Home::class  . ':home')->add(app\middleware\Middleware::web());
$app->get('/home',  app\controller\Home::class  . ':home')->add(app\middleware\Middleware::web());
$app->get('/login', app\controller\Login::class . ':login')->add(app\middleware\Middleware::web());

$app->group('/authentication', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/logout', app\controller\Login::class . ':logout');
    $group->post('/google', app\controller\Login::class . ':google');
    $group->post('/authenticate', app\controller\Login::class . ':authenticate');
    $group->post('/preregister', app\controller\Login::class . ':preRegister');
});

$app->group('/cliente', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista',         app\controller\Customer::class . ':list');
    $group->get('/detalhes/{id}', app\controller\Customer::class . ':details');
    $group->get('/detalhes',      app\controller\Customer::class . ':details');
    $group->post('/insert',       app\controller\Customer::class . ':insert');
    $group->post('/update',       app\controller\Customer::class . ':update');
    $group->post('/delete',       app\controller\Customer::class . ':delete');
    $group->post('/listingdata',  app\controller\Customer::class . ':listingdata');
})->add(app\middleware\Middleware::web());

$app->group('/empresa', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista',         app\controller\Enterprise::class . ':list');
    $group->get('/detalhes/{id}', app\controller\Enterprise::class . ':details');
    $group->get('/detalhes',      app\controller\Enterprise::class . ':details');
    $group->post('/insert',       app\controller\Enterprise::class . ':insert');
    $group->post('/update',       app\controller\Enterprise::class . ':update');
    $group->post('/delete',       app\controller\Enterprise::class . ':delete');
    $group->post('/listingdata',  app\controller\Enterprise::class . ':listingdata');
})->add(app\middleware\Middleware::web());

$app->group('/fornecedor', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista',         app\controller\Supplier::class . ':list');
    $group->get('/detalhes/{id}', app\controller\Supplier::class . ':details');
    $group->get('/detalhes',      app\controller\Supplier::class . ':details');
    $group->post('/insert',       app\controller\Supplier::class . ':insert');
    $group->post('/update',       app\controller\Supplier::class . ':update');
    $group->post('/delete',       app\controller\Supplier::class . ':delete');
    $group->post('/listingdata',  app\controller\Supplier::class . ':listingdata');
})->add(app\middleware\Middleware::web());

$app->group('/usuario', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista',         app\controller\Users::class . ':list');
    $group->get('/detalhes/{id}', app\controller\Users::class . ':details');
    $group->get('/detalhes',      app\controller\Users::class . ':details');
    $group->post('/insert',       app\controller\Users::class . ':insert');
    $group->post('/update',       app\controller\Users::class . ':update');
    $group->post('/delete',       app\controller\Users::class . ':delete');
    $group->post('/listingdata',  app\controller\Users::class . ':listingdata');
})->add(app\middleware\Middleware::web());