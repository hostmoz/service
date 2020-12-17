<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'SpondonIt\Service\Controllers', 'middleware' => 'web'], function () {
    Route::group(['prefix' => 'install'], function(){
        Route::get('/', 'InstallController@index')->name('service.install');
        Route::get('pre-requisite', 'InstallController@preRequisite')->name('service.preRequisite');
        Route::get('license', 'InstallController@license')->name('service.license');
        Route::post('license', 'InstallController@post_license');
        Route::get('database', 'InstallController@database')->name('service.database');
        Route::post('database', 'InstallController@post_database');
        Route::get('user', 'InstallController@user')->name('service.user');
        Route::post('user', 'InstallController@post_user');
        Route::get('done', 'InstallController@done')->name('service.done');
    });

    Route::get('/update', 'UpdateController@index')->name('service.update');
    Route::post('/update', 'UpdateController@update');
    Route::post('/download', 'UpdateController@download')->name('service.delete');

    /* Route::get('/install/pre-requisite', 'InstallController@preRequisite');

    Route::post('/install/validate/{option}', 'InstallController@store');
    Route::post('/install', 'InstallController@store');
    Route::post('/license', 'LicenseController@verify');

    Route::get('/about', 'HomeController@about');
    Route::get('/support', 'SupportController@index');
    Route::post('/support', 'SupportController@submit');

    Route::post('/help/content', 'HomeController@helpDoc'); */
});


