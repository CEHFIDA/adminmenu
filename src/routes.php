<?php

Route::group(['prefix' => config('adminamazing.path').'/adminmenu', 'middleware' => ['web', 'CheckAccess']], function() {
	Route::get('/', 'selfreliance\adminmenu\AdminMenuController@index')->name('AdminMenuHome');
	Route::post('/add_packages', 'selfreliance\adminmenu\AdminMenuController@addPackages')->name('AdminMenuAddPackages');
	Route::post('/create_stub', 'selfreliance\adminmenu\AdminMenuController@createStub')->name('AdminMenuCreateStub');
	Route::put('/update_category', 'selfreliance\adminmenu\AdminMenuController@updateCategory')->name('AdminMenuUpdateCategory');
	Route::put('/update_tree', 'selfreliance\adminmenu\AdminMenuController@updateTree')->name('AdminMenuUpdateTree');
	Route::delete('/{id?}', 'selfreliance\adminmenu\AdminMenuController@destroy')->name('AdminMenuDelete');
});