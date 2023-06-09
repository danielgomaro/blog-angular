<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParametrosController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//Cargando clases
use App\Http\Middleware\ApiAuthMiddleware;

//RUTAS DE PRUEBA
/*Route::get('/', function () {
    return view('welcome');
});

Route::get('/animales',  "PruebasController@index");
Route::get('/test-orm',  "PruebasController@testOrm");*/

//RUTAS DEL API

    /*Metodos HTTP comunes
        GET: Conseguir datos o recursos
        POST: Guardar datos o recursos o hacer logica desde un formulario
        PUT:Actualizar recursos o datos
        DELETE: Eliminar datos o recursos
    */

    //Rutas de prueba
    Route::get('/usuario/pruebas',  "UserController@pruebas");
    Route::get('/categoria/pruebas',  "CategoryController@pruebas");
    Route::get('/entrada/pruebas',  "PostController@pruebas");

    //Rutas de controlador de usuarios
    Route::post('/api/register',  "UserController@register");
    Route::post('/api/login',  "UserController@login");
    Route::put('/api/user/update',  "UserController@update");
    Route::post('/api/user/upload', "UserController@upload")->middleware(ApiAuthMiddleware::class);
    Route::get('/api/user/avatar/{filename}', "UserController@getImage");
    Route::get('/api/user/detail/{id}', "UserController@detail");

    //Rutas del controlador de categorias
    Route::resource('/api/category', 'CategoryController');

    //Rutas del controlador de Post
    Route::resource('/api/post', 'PostController');
    Route::post('/api/post/upload', "PostController@upload");
    Route::get('/api/post/image/{filename}', "PostController@getImage");
    Route::get('/api/post/category/{id}', "PostController@getPostsByCategory");
    Route::get('/api/post/user/{id}', "PostController@getPostsByUser");

