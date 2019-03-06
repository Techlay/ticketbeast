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

Route::get('/concerts/{id}', 'ConcertController@show')->name('concerts.show');
Route::post('/concerts/{id}/orders', 'ConcertOrderController@store');
Route::get('/orders/{confirmationNumber}', 'OrderController@show');

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('auth.show-login');
Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::post('/logout', 'Auth\LoginController@logout')->name('auth.logout');

Route::post('/register', 'Auth\RegisterController@register')->name('auth.register');

Route::get('/invitations/{code}', 'InvitationController@show')->name('invitations.show');

Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], function () {
    Route::get('concerts', 'ConcertController@index')->name('backstage.concerts.index');
    Route::get('concerts/new', 'ConcertController@create')->name('backstage.concerts.new');
    Route::post('concerts', 'ConcertController@store');
    Route::get('/concerts/{id}/edit', 'ConcertController@edit')->name('backstage.concerts.edit');
    Route::patch('/concerts/{id}', 'ConcertController@update')->name('backstage.concerts.update');

    Route::get('/published-concerts/{id}/orders', 'PublishedConcertOrderController@index')->name('backstage.published-concert-orders.index');
    Route::post('/published-concerts', 'PublishedConcertController@store')->name('backstage.published-concerts.store');

    Route::get('/concerts/{id}/messages/new', 'ConcertMessageController@create')->name('backstage.concert-messages.new');
    Route::post('/concerts/{id}/messages', 'ConcertMessageController@store')->name('backstage.concert-messages.store');

    Route::get('/stripe-connect/connect', 'StripeConnectController@connect')->name('backstage.stripe-connect.connect');
    Route::get('/stripe-connect/authorize', 'StripeConnectController@authorizeRedirect')->name('backstage.stripe-connect.authorize');
    Route::get('/stripe-connect/redirect', 'StripeConnectController@redirect')->name('backstage.stripe-connect.redirect');
});

