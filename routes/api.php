<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\Api\Login as LoginController;
use App\Http\Controllers\Api\Register as RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [RegisterController::class, 'Register']);
Route::post('/login', [LoginController::class, 'Login']);
Route::middleware(['auth:sanctum'])->group(function () {
    //vendor
    Route::get('/vendor/all', [VendorController::class, 'index'])->middleware('isAdmin');
    Route::post('/vendor/create', [VendorController::class, 'create'])->middleware('isAdmin');
    Route::post('/vendor/update/{id}', [VendorController::class, 'update']);
    Route::delete('/vendor/delete/{id}', [VendorController::class, 'destroy'])->middleware('isAdmin');
    //store
    Route::get('/store/all', [StoreController::class, 'index'])->middleware('isAdmin');
    Route::post('/store/create', [StoreController::class, 'store']);
    Route::post('/store/update/{id}', [StoreController::class, 'update']);
    Route::delete('/store/delete/{id}', [StoreController::class, 'destroy']);
    //event
    Route::get('/event/all', [EventController::class, 'index'])->middleware('isAdmin');
    Route::post('/event/create', [EventController::class, 'store'])->middleware('isAdmin');
    Route::get('/event/show/{id}', [EventController::class, 'show']);
    Route::post('/event/update/{id}', [EventController::class, 'update'])->middleware('isAdmin');
    Route::delete('/event/delete/{id}', [EventController::class, 'destroy'])->middleware('isAdmin');
    //product
    Route::get('/product/all', [ProductController::class, 'index']);
    Route::post('/product/create', [ProductController::class, 'store']);
    Route::get('/product/show/{id}', [ProductController::class, 'show']);
    Route::post('/product/update/{id}', [ProductController::class, 'update']);
    Route::delete('/product/delete/{id}', [ProductController::class, 'destroy']);
});