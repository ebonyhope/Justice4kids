<?php

use Illuminate\Http\Request;

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

//create report
Route::post('reports/create', 'ApiController@createReport');

//advocates, translators, reporters 
Route::post('login', 'ApiController@login');
Route::post('register', 'ApiController@register')->middleware('cors');

 
Route::group(['middleware' => 'auth.jwt'], function () {
    //all users
    Route::get('user', 'UserController@getAuthUser');
    Route::post('user', 'UserController@updateUser');
    Route::get('logout', 'UserController@logout');

    //role = reporter, advocate
    Route::get('report/{id}', 'ReportController@getReport');
    Route::post('report/{id}', 'ReportController@updateReport');

    //role = advocate
    Route::get('reports', 'ReportController@getAllReports');
    //Route::get('report/{country}/{state}', 'ReportController@getReportsByLocation');    
    Route::post('reports/accept', 'ReportController@acceptReport');
    Route::post('reports/drop', 'ReportController@dropReport');
    Route::post('reports/takeOver', 'ReportController@reportRequestTakeOver');
    Route::post('reports/handOver', 'ReportController@reportHandOver');
    Route::get('reports/active', 'ReportController@activeReports');

    //role = advocate
    Route::post('activities/create', 'ActivityController@create');
    Route::get('activities/{id}', 'ActivityController@get');
    
});
