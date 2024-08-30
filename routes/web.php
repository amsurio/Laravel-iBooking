<?php

use Illuminate\Support\Facades\Auth;
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

Auth::routes();
// Route::post('/register', 'Auth\RegisterController@register')->middleware('check.temporary.email');

Route::get('/chat', 'ChatController@index')->middleware('auth');
Route::get('/messages/{userId}', 'ChatController@fetchMessages')->middleware('auth');
Route::post('/send-message', 'ChatController@sendMessage')->middleware('auth');
