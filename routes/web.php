<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PersonCardController;

Route::get('/person-card/{person}/download', [PersonCardController::class, 'download'])
    ->name('person.card.download')
    ->middleware(['auth']);
// Route::get('/', function () {
//     return view('welcome');
// });
