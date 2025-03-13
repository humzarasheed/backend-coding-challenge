<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    dump(Auth::user()->role);
    return redirect('dashboard');
});
