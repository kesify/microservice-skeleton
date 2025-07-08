<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'your_prefix','middleware'=>['auth:api']],function() {
    Route::post('/install',[\App\Http\Controllers\Controller::class,'install']);
});
