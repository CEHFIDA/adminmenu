<?php

Route::group(['prefix' => config('adminamazing.path').'/adminmenu', 'middleware' => ['web', 'CheckAccess']], function() {
	Route::get('/', 'selfreliance\adminmenu\AdminMenuController@index')->name('AdminMenuHome');
	Route::post('/add', 'selfreliance\adminmenu\AdminMenuController@add')->name('AdminMenuAdd');
	Route::get('/delete/{id}', 'selfreliance\adminmenu\AdminMenuController@delete')->name('AdminMenuDelete');
	Route::put('/update', 'selfreliance\adminmenu\AdminMenuController@update_tree')->name('AdminMenuUpdate');
});