<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ImageController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/crop-image', [ImageController::class, 'showCropForm'])->name('crop.form');
Route::post('/upload-image', [ImageController::class, 'upload'])->name('upload.image');
