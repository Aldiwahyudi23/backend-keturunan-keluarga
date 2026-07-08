<?php

use App\Http\Controllers\Api\BookPdfController;
use App\Http\Controllers\PersonCardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('person')->group(function () {

    Route::get(
        '/{person}/card',
        [PersonCardController::class, 'show']
    )->name('person.card');

    Route::get(
        '/{person}/card/download',
        [PersonCardController::class, 'download']
    )->name('person.card.download');

});

Route::middleware(['web', 'auth'])->prefix('books')->name('books.')->group(function () {
    Route::get('{book}/preview', [BookPdfController::class, 'preview'])
        ->name('preview');

    Route::get('{book}/download', [BookPdfController::class, 'download'])
        ->name('download');
});
// Route::get('/', function () {
//     return view('welcome');
// });

use SimpleSoftwareIO\QrCode\Facades\QrCode;

Route::get('/test-qr', function () {

    return QrCode::size(300)
        ->generate('https://google.com');

});
