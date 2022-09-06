<?php

use App\Http\Controllers\BeatController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\PlaylistsController;
use App\Http\Controllers\ProducerController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenreController;
use App\Http\Resources\UserResource;
use App\Http\Controllers\PartController;
use App\Http\Controllers\ServiceController;

/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider within a group which | is assigned the "api" middleware group. Enjoy building your API! | */

/**
 * @authenticated
 * @apiResource App\Http\Resources\UserResource
 * @apiResourceModel App\Models\User
 */

Route::group(['prefix' => 'user'], function () {
    Route::get('/', [UserController::class, 'me'])
        ->middleware(['auth:sanctum'])
        ->name('user.current');
    Route::put('/', [UserController::class, 'update'])
        ->middleware(['auth:sanctum'])
        ->name('user.update');
});

Route::group(['prefix' => 'genres'], function () {
    Route::get('/', [GenreController::class, 'index'])
        ->name('genre.index');
});

Route::group(['prefix' => 'parts'], function () {
    Route::get('/', [PartController::class, 'index'])
        ->name('part.index');
});

Route::group(['prefix' => 'services'], function () {
    Route::get('/', [ServiceController::class, 'index'])
        ->name('service.index');
});

Route::group(['prefix' => 'beats'], function () {
    Route::get('/', [BeatController::class, 'index'])
        ->name('beat.index');
    Route::post('/', [BeatController::class, 'store'])
        ->middleware('auth:sanctum')
        ->middleware('verified')
        ->name('beat.store');
    Route::get('/latest', [BeatController::class, 'latest'])
        ->name('beat.latest');
    Route::get('/trending', [BeatController::class, 'trending'])
        ->name('beat.trending');
    Route::get('/{beat}', [BeatController::class, 'show'])
        ->name('beat.show');
    Route::get('/{beat}/download', [BeatController::class, 'download'])
        ->middleware('auth:sanctum')
        ->middleware('verified')
        ->name('beat.download');
});

Route::group(['prefix' => 'carts'], function () {
    Route::get('/', [CartController::class, 'get'])
        ->name('cart.get');
    Route::post('/', [CartController::class, 'store'])
        ->name('cart.store');
    Route::post('/remove', [CartController::class, 'remove'])
        ->name('carts.remove');
    Route::delete('/', [CartController::class, 'destroy'])
        ->name('cart.destroy');
});

Route::group(['prefix' => 'purchases', 'middleware' => ['auth:sanctum', 'verified']], function () {
    Route::get('/', [PurchasesController::class, 'index'])
        ->name('purchases.index');
    Route::post('/', [PurchasesController::class, 'start'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('purchases.start');
    Route::post('/{purchase}/complete', [PurchasesController::class, 'complete'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('purchases.complete');
    Route::delete('/{purchase}/cancel', [PurchasesController::class, 'cancel'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('purchases.cancel');
});

Route::group(['prefix' => 'paypal'], function () {
    Route::post('/webhook', [PaypalController::class, 'handleWebhook'])
        ->name('paypal.handle-webhook');
});

Route::group(['prefix' => 'webhook'], function () {
    Route::post('/handle', [PaypalController::class, 'handleWebhook'])
        ->name('webhook.paypal');
});

Route::group(['prefix' => 'playlists'], function () {
    Route::get('/', [PlaylistsController::class, 'index'])
        ->middleware(['auth:sanctum'])
        ->name('playlists.index');
    Route::get('/{playlist}', [PlaylistsController::class, 'show'])
        ->middleware(['auth:sanctum'])
        ->name('playlists.show');
    Route::post('/', [PlaylistsController::class, 'store'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('playlists.store');
    Route::post('/{playlist}/add', [PlaylistsController::class, 'add'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('playlists.add');
    Route::put('/{playlist}', [PlaylistsController::class, 'update'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('playlists.update');
    Route::delete('/{playlist}/remove', [PlaylistsController::class, 'remove'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('playlists.remove');
    Route::delete('/{playlist}', [PlaylistsController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('playlists.destroy');
});

Route::group(['prefix' => 'producers'], function () {
    Route::get('/trending', [ProducerController::class, 'trending'])
        ->name('producer.trending');
});

Route::group(['prefix' => 'search'], function () {
    Route::get('/producers', [SearchController::class, 'producers']);
    Route::get('/beats', [SearchController::class, 'beats']);
});
