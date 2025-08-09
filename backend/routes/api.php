<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));
Route::get('/version', fn () => response()->json(['app' => config('app.name'), 'laravel' => app()->version()])); 