<?php

Route::group(['prefix' => config('adminamazing.path').'/adminmenu', 'middleware' => ['web', 'CheckAccess']], function() {
	Route::get('/', 'selfreliance\adminmenu\AdminMenuController@index')->name('AdminMenuHome');
	Route::post('/create/{type}', 'selfreliance\adminmenu\AdminMenuController@create_item')->name('AdminMenuCreate');
	Route::put('/update/{type}', 'selfreliance\adminmenu\AdminMenuController@update')->name('AdminMenuUpdate');
	Route::delete('/{id?}', 'selfreliance\adminmenu\AdminMenuController@destroy')->name('AdminMenuDelete');
});