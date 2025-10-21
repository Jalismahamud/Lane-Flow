<?php

use Illuminate\Support\Facades\Route;

Route::get('/',function (){
    return view('welcome');
})->middleware('authCheck')->name('home');


require __DIR__.'/auth.php';


