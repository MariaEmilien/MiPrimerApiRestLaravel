<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware;

//Rutas de Prueba
Route::get('/', function () {
    return view('welcome');
});

Route::get('/pruebas/{nombre}', function ($nombre = null){
    $texto = '<h2> Texto desde una ruta</h2>';
    $texto.= 'Nombre:'.$nombre;
    
    return view('pruebas', array(
        'texto' => $texto
    ));
    
});

Route::get('/animales', 'PruebasController@index');
Route::get('/test-orm', 'PruebasController@testOrm');

//Rutas de prueba del API
/*Route::get('/usuario/pruebas', 'UserController@pruebas');
Route::get('/categoria/pruebas', 'CategoryController@pruebas');
Route::get('/entrada/pruebas', 'PostController@pruebas'); */

//Rutas de controlador de usuario
Route::post('/api/register','UserController@register');
Route::post('/api/login','UserController@login');
Route::put('/api/user/update','UserController@update');
Route::post('api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}','UserController@getImage');
Route::get('/api/user/detail/{id}','UserController@detail');

//Rutas de controlador de categorías
Route::resource('/api/category', 'CategoryController');

//Rutas de Controlador de entradas
Route::resource('/api/post','PostController');

//Ruta para subir imagen
Route::post('/api/post/upload', 'PostController@upload');
Route::get('/api/post/image/{filename}', 'PostController@getImage');
Route::get('/api/post/category/{id}', 'PostController@getPostsByCategory');
Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');