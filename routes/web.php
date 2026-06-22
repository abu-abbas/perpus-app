<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Web — SPA Catch-all
|--------------------------------------------------------------------------
| Semua request yang tidak cocok dengan route API akan diarahkan ke
| template Blade app.blade.php yang memuat Vue SPA.
*/

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
