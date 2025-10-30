<?php

use App\Models\DynamicPage;
use Illuminate\Support\Facades\Route;

Route::get('/',function (){
    return view('welcome');
})->middleware('authCheck')->name('home');


Route::get('privacy-policy', function () {
    $data = DynamicPage::where('page_slug', 'privacy-policy')->first();

    if (!$data) {
        $content = 'Privacy Policy data not found.';
    } else {
        $content = $data->page_content;
    }

    return view('privacy-policy', compact('content'));
})->name('privacy-policy');


Route::get('terms-and-conditions', function () {
    $data = DynamicPage::where('page_slug', 'terms-and-conditions')->first();

    if (!$data) {
        $content = 'Terms and Conditions data not found.';
    } else {
        $content = $data->page_content;
    }

    return view('terms-and-conditions', compact('content'));
})->name('terms-and-conditions');


require __DIR__.'/auth.php';


