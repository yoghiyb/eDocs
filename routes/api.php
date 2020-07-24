<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

$protect = [
    'middleware' => ['auth:api']
];

Route::group($protect, function () {

    Route::resource('user', 'Api\UserController')->except(['show']);
    Route::patch('user/cp/{id}', 'Api\UserController@passwordReset');

    //get all tag
    Route::get('tag/all', 'Api\TagController@getAllTag');
    Route::post('document/upload', 'Api\DocumentController@store');
    Route::get('document', 'Api\DocumentController@index');
    Route::get('tests3/{file}', 'Api\DocumentController@show');

    Route::group(['middleware' => 'admin'], function () {
        Route::get('tag', function (Request $request) {
            return response()->json($request->user(), 200);
        });

        Route::get('users/all', 'Api\UserController@allUsers');

        Route::resource('tag', 'Api\TagController')->except(['create', 'show']);
    });

    Route::group(['middleware' => 'adminOrManager'], function () {
        Route::get('approve', function (Request $request) {
            return response()->json($request->user(), 200);
        });
    });
});
