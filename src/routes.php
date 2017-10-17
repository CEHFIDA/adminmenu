<?php

Route::group(['prefix' => config('adminamazing.path').'/adminmenu', 'middleware' => ['web', 'CheckAccess']], function() {
	Route::get('/', 'selfreliance\adminmenu\AdminMenuController@index')->name('AdminMenuHome');
	Route::post('/add/{name}', 'selfreliance\adminmenu\AdminMenuController@action')->name('AdminMenuAdd');
	Route::delete('/delete', 'selfreliance\adminmenu\AdminMenuController@action')->name('AdminMenuDelete');
	Route::put('/update/{name}', 'selfreliance\adminmenu\AdminMenuController@action')->name('AdminMenuUpdate');
});