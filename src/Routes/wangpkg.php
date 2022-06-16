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

Route::group(['prefix' => 'api/wangpkg', 'namespace' => 'Wang\Pkg\Http\Controllers'], function () {
    Route::any('version', 'PublicController@version');
    Route::any('sendSms', 'PublicController@sendSms');
    Route::any('getConfig', 'PublicController@getConfig');
});


Route::group(['prefix' => 'wangpkg', 'namespace' => 'Wang\Pkg\Http\Controllers'], function () {

    Route::get('version', ShowController::class . '@db');
    Route::get('showdb', function () {
        echo 123456;

        if (env('APP_ENV') == 'local') {
            Wang\Pkg\Lib\ShowDB::show();
        }
    });

    Route::post('getQiniuToken', 'UpController@getToken');
    Route::get('qiniuHtml', 'UpController@qiniuHtml');

    Route::any('upload', 'EditorController@wangUpload');
    Route::any('ueditor', 'EditorController@editorAction');
    Route::post('ckUpload', 'EditorController@ckUpload');

    /*使用本地存储图片*/
    Route::any('fileUpload', 'LEditorController@fileUpload');
    Route::any('lUpload', 'LEditorController@wangUpload');
    Route::any('lUeditor', 'LEditorController@editorAction');
    Route::post('lCkUpload', 'LEditorController@ckUpload');

});


