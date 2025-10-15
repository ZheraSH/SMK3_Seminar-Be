<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginApiController;

Route::post('login', [LoginApiController::class, 'login']);