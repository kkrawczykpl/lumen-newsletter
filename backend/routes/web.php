<?php

/** @var Router $router */

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
    
    // API Authentication
    $router->post('authenticate', ['uses' => 'UserController@authenticate', 'as' => 'auth']);

    /**
     * 
     * Newsletter Routes
     * 
     */
    
    // This routes are only for authenticated user.
    $router->group(['middleware' => 'auth'], function() use ($router) {
        $router->get('newsletter/signed', ['uses' => 'NewsletterController@showAllNewsletters', 'as' => 'index']);
        $router->get('newsletter/signed/{id}', ['uses' => 'NewsletterController@showOneNewsletter', 'as' => 'newsletter.one']);
    });
    
    // Signing in and out of the newsletter
    $router->post('newsletter/sign-up', ['uses' => 'NewsletterController@store', 'as' => 'sign-up']);
    $router->patch('newsletter/update-user', ['uses' => 'NewsletterController@update', 'as' => 'update']);
    $router->delete('newsletter/unsubscribre', ['uses' => 'NewsletterController@destroy', 'as' => 'destroy']);

});
