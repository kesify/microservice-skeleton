<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'your_prefix','middleware'=>['auth:api']],function() {});
