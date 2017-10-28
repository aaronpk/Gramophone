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

Route::get('/', function () {
    return view('welcome');
})->name('index');

Route::get('/dashboard', 'HomeController@index')->name('dashboard');

Route::get('/login', 'LoginController@login')->name('login');
Route::get('/logout', 'LoginController@logout')->name('logout');
Route::post('/login', 'LoginController@start');
Route::get('/login/callback', 'LoginController@callback')->name('login_callback');


Route::get('/podcast/new', 'PodcastController@create')->name('new_podcast');

Route::get('/podcast/auth', 'PodcastAuthController@start')->name('podcast_auth');
Route::get('/podcast/callback', 'PodcastAuthController@callback')->name('podcast_auth_callback');
Route::get('/podcast/auth/error', 'PodcastAuthController@error')->name('podcast_auth_error');

Route::get('/podcast/{podcast}', 'PodcastController@podcast')->name('podcast');
Route::get('/podcast/{podcast}/edit', 'PodcastController@edit')->name('edit_podcast');
Route::post('/podcast/{podcast}/edit', 'PodcastController@update')->name('update_podcast');

Route::post('/podcast/episode/upload', 'PodcastController@upload')->name('podcast_upload');
Route::post('/podcast/episode/save_id3', 'PodcastController@save_id3')->name('save_id3');
Route::post('/podcast/episode/upload_media', 'PodcastController@upload_media')->name('upload_media');
Route::post('/podcast/episode/create_episode', 'PodcastController@create_episode')->name('create_episode');
