<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'MainController@index')
    ->middleware('auth')->name('main');
Route::post('/get_road_list', 'MainController@getRoadList')
    ->middleware('auth')->name('getRoadList');


Route::get('/prada', function () {
    return 'by MisterPrada ' . mt_rand(75, 150);
})->name('prada');


Auth::routes(['register' => false]);
Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout');
