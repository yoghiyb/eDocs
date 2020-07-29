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

    Route::resource('user', 'Api\UserController')->except(['show', 'destroy', 'edit']);
    Route::patch('user/cp/{id}', 'Api\UserController@passwordReset');
    Route::get('user/photo/{id}', 'Api\UserController@userPhoto');
    Route::get('users/all', 'Api\UserController@allUsers');
    Route::match(['get', 'head'], 'user/{id}/edit', 'Api\UserController@edit');

    //get all tag
    Route::get('tag/all', 'Api\TagController@getAllTag');

    // document
    Route::post('document/upload', 'Api\DocumentController@store');
    Route::get('document', 'Api\DocumentController@index');
    Route::get('mydocument/{id}', 'Api\DocumentController@getMyDocument');
    Route::get('document/{id}', 'Api\DocumentController@show');
    Route::put('document/{id}', 'Api\DocumentController@update');
    Route::get('documents', 'Api\DocumentController@documents');
    Route::get('document/download/{file}', 'Api\DocumentController@download');

    // Comment
    // Route::post('comment', 'Api\CommentController@store');
    Route::get('comment/{comment_owner}', 'Api\CommentController@getComment');
    Route::resource('comment', 'Api\CommentController')->only(['update', 'destroy', 'store']);
    Route::post('comment/reply', 'Api\CommentController@reply');

    // khusus admin
    Route::group(['middleware' => 'admin'], function () {

        Route::get('tag', function (Request $request) {
            return response()->json($request->user(), 200);
        });

        Route::resource('tag', 'Api\TagController')->except(['create', 'show']);
        Route::get('tag/{id}', 'Api\TagController@show');
    });


    //khusu admin dan manager
    Route::group(['middleware' => 'adminOrManager'], function () {
        //user
        Route::delete('/user/{id}', 'Api\UserController@destroy');
        // Route::match(['PUT', 'PATCH'], 'user/{id}', 'Api\UserController@update');

        Route::get('approve', function (Request $request) {
            return response()->json($request->user(), 200);
        });

        //hapus dokumen
        Route::delete('document/{id}', 'Api\DocumentController@destroy');

        //approve document
        Route::patch('document/approve/{id}', 'Api\DocumentController@approve');
        // get pending document
        Route::get('pending', 'Api\DocumentController@getPendingDocument');
        // get total pending document
        Route::get('pending/total', 'Api\DocumentController@getTotalPendingDocuments');
    });
});
