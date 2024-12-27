<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/upload', [UploadController::class, 'viewupload'])->name('upload.excel');

Route::post('/post-upload', [UploadController::class, 'validateExcel'])->name('post.upload');