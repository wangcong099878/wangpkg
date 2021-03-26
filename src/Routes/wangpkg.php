<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'wangpkg', 'namespace' => 'Wang\Pkg\Http\Controllers'], function () {


    Route::get('version', ShowController::class . '@db');
    Route::get('showdb', function () {
        if (env('APP_ENV') == 'local') {
            Wang\Pkg\Lib\ShowDB::show();
        }
    });

    Route::any('upload', 'EditorController@wangUpload');
    Route::any('ueditor', 'EditorController@editorAction');
    Route::post('getQiniuToken', 'UpController@getToken');
    Route::get('qiniuHtml', 'UpController@qiniuHtml');
    Route::post('ckUpload', 'EditorController@ckUpload');

});


