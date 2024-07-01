<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\ItkPortalSubmit\Http\Controllers'], function()
{
    Route::get('/', 'ItkPortalSubmitController@index');
});
