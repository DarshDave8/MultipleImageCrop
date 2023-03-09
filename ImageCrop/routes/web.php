<?php

use App\Http\Controllers\MultiImageCropController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UploadImagesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/posts', [PostController::class, 'index'])->name('image.index');
Route::get('/posts/create',  [PostController::class, 'create'])->name('image.create');
Route::post('/posts', [PostController::class, 'store'])->name('image.store');
Route::get('/posts/{id}', [PostController::class, 'show'])->name('image.show');
Route::get('/posts/{id}/edit',[PostController::class, 'edit'])->name('image.edit');
Route::put('/posts/{id}', [PostController::class, 'update'])->name('image.update');
Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('image.destroy');

Route::get('upload-multiple-image-preview', [UploadImagesController::class, 'index']);
Route::post('upload-multiple-image-preview', [UploadImagesController::class, 'store']);
Route::get('edit-multiple-image-preview', [UploadImagesController::class, 'edit']);
// Route::post('crop-image', [UploadImagesController::class, 'cropAndSave']);



Route::get('multi-image-crop', [MultiImageCropController::class, 'add']);
Route::post('multi-image-crop', [MultiImageCropController::class, 'store']);
