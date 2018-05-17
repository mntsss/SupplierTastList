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
//Auth::routes();
// Autentikacija
Broadcast::routes();
$this->get('/', 'Auth\LoginController@showLoginForm')->name('login');
$this->post('/', 'Auth\LoginController@login');
$this->post('logout', 'Auth\LoginController@logout')->name('logout');

// Password Reset Routes...
/*
$this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
$this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
$this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
$this->post('password/reset', 'Auth\ResetPasswordController@reset');*/
/*Route::get('/broadcasting/auth', function(){
  return dd(Auth::check()) ;
});
Route::post('/broadcasting/auth', function(){
  return dd(Auth::check());
});*/
Route::get('/active', 'OrderController@activeOrders')->name('active');
Route::get('/delivered/{query?}/{from?}/{til?}', 'OrderController@deliveredOrders')->name('delivered');
Route::get('/deleted/{query?}/{from?}/{til?}', 'OrderController@deletedOrders')->name('deleted');
Route::get('/returned/{query?}/{from?}/{til?}', 'OrderController@returnedOrders')->name('returned');
Route::get('/history/{query?}/{user?}/{from?}/{til?}', 'OrderController@history')->name('history');

Route::get('/neworder', 'OrderController@newOrder')->name('order.add');
Route::post('/neworder', 'OrderController@submitNewOrder')->name('order.add.submit');

Route::get('/newsearch', 'OrderController@newSearch')->name('search.add');
Route::post('/newsearch', 'OrderController@newSearch')->name('search.add.another');
Route::post('/newsearch/submit', 'OrderController@newSearchSubmit')->name('search.add.submit');

Route::post('/vehicles/models', 'OrderController@fillModels')->name('vehicle.models');

Route::get('/order/view/{id}', 'OrderController@viewOrder')->name('order.view');

Route::get('/order/edit/{id}', 'OrderController@orderEdit')->name('order.edit');

Route::post('/order/editsubmit', 'OrderController@orderEditSubmit')->name('order.edit.submit');

Route::post('/search/editsubmit', 'OrderController@searchEditSubmit')->name('search.edit.submit');


Route::get('/order/delete/{id}', 'OrderController@delete')->name('order.delete');
Route::get('/order/delivered/{id}', 'OrderController@delivered')->name('order.delivered');

Route::get('/order/return/{id}', 'OrderController@return')->name('order.return');
Route::get('/order/returned/{id}', 'OrderController@returned')->name('order.returned');

Route::get('/order/refresh/{id}', 'OrderController@refreshOrder')->name('order.refresh');

Route::get('/order/found/{id}', 'OrderController@found')->name('order.found');
Route::post('/order/foundsubmit', 'OrderController@foundSubmit')->name('order.found.submit');

Route::post('/order/updatephoto', 'OrderController@updatePhoto')->name('order.updatephoto.submit');

Route::get('/order/setcolor/green/{id}', 'OrderController@setSupplierOrderColorGreen')->name('order.color.green');
Route::get('/order/setcolor/yellow/{id}', 'OrderController@setSupplierOrderColorYellow')->name('order.color.yellow');
Route::get('/order/setcolor/none/{id}', 'OrderController@setSupplierOrderColorNone')->name('order.color.none');

Route::get('/settings/password', 'UserController@changePassword')->name('user.password');
Route::post('/settings/password', 'UserController@changePasswordSubmit')->name('user.password.submit');

Route::get('/user/register', 'UserController@register')->name('user.register');
Route::post('/user/register', 'UserController@registerSubmit')->name('user.register.submit');

Route::post('/checkupdates', 'OrderController@checkForUpdates')->name('check.updates');
Route::post('/sendnotification', 'OrderController@sendFCM')->name('send.notification');

Route::post('/updatesub', 'UserController@updateNotificationSubscription')->name('user.notification.update');

Route::get('/notificationinfo', 'OrderController@getNotificationInfo')->name('order.notification.info');

Route::post('/chat/new', 'ChatController@newMessage')->name('chat.new');
Route::get('/chat/get/{orderID}', 'ChatController@getMessages')->name('chat.get');
Route::get('/chat/{orderID}', 'ChatController@index')->name('chat');
