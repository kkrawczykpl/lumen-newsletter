<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function() use ($router) {
    
    // Signing in and out of the newsletter
    $router->post('newsletter/sign-up', ['uses' => 'NewsletterController@store']);
    $router->delete('newsletter/unsubscribre/{code}', ['uses' => 'NewsletterController@destroy']);

});
