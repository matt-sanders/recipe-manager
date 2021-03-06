<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController'
]);

Route::get('/', 'HomeController@index');

Route::group(['prefix' => 'api', 'middleware' => 'cors'], function(){

    Route::post('/authenticate', 'AuthenticateController@authenticate');

    Route::get('/ingredients', 'IngredientController@retrieveAll');
    Route::get('/recipes', 'RecipeController@retrieveAll');
    Route::get('/recipe/{id}', 'RecipeController@retrieveOne');
    Route::get('/last-updated', 'RecipeController@retrieveLastUpdated');
    Route::get('/units', 'UnitController@retrieveAll');
    
    Route::group(['middleware' => ['jwt.auth']], function(){
        Route::post('/ingredient', 'IngredientController@store');
        Route::post('/recipe', 'RecipeController@store');
        Route::put('/recipe/{id}', 'RecipeController@update');
        Route::delete('/recipe/{id}', 'RecipeController@remove');
    });
});

Route::auth();

