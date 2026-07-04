<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PersonCardController;

use App\Http\Controllers\Api\BookPdfController;

Route::get('/person-card/{person}/download', [PersonCardController::class, 'download'])
    ->name('person.card.download')
    ->middleware(['auth']);

Route::middleware(['web', 'auth'])->prefix('books')->name('books.')->group(function () {
    Route::get('{book}/preview', [BookPdfController::class, 'preview'])
        ->name('preview');

    Route::get('{book}/download', [BookPdfController::class, 'download'])
        ->name('download');
});
// Route::get('/', function () {
//     return view('welcome');
// });
