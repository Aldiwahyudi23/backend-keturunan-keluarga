<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\BookDataController;
use App\Http\Controllers\Api\BookPdfController;
use App\Http\Controllers\Api\FamilyRelationshipController;
use App\Http\Controllers\Api\FamilyTreeController;
use Illuminate\Support\Facades\Route;

// Hanya untuk ngetes data yang di hasilkan dan sekarang sudah tidak digunakan lagi
Route::get('buku/{book}/data', [
    BookDataController::class,
    'show',
]);

// ===========================================================

// ln -s /home/u516139464/domains/keturunan-api.keluargamahaya.com/public_html/storage/app/public /home/u516139464/domains/keturunan-api.keluargamahaya.com/public_html/public/storage
Route::prefix('auth')
    ->group(function () {

        Route::post(
            'login',
            [AuthController::class, 'login']
        );

        Route::middleware('auth:sanctum')
            ->group(function () {

                Route::get(
                    'me',
                    [AuthController::class, 'me']
                );

                Route::post(
                    'logout',
                    [AuthController::class, 'logout']
                );

                Route::post(
                    'logout-all',
                    [AuthController::class, 'logoutAll']
                );
            });
    });

Route::prefix('people')->group(function () {

    Route::post('/', [FamilyTreeController::class, 'store'])->middleware('auth:sanctum');                 // POST   /api/people
    Route::get('/{personId}/spouse-options', [FamilyTreeController::class, 'getSpouseOptionsForChildForm']) // GET /api/people/{personId}/spouse-options
        ->whereNumber('personId')->middleware('auth:sanctum');
    Route::get('{personId}/spouse-options-with-children', [FamilyTreeController::class, 'getSpouseOptionsWithChildren'])->middleware('auth:sanctum');

    // Update & Delete Person
    Route::put('{personId}', [FamilyTreeController::class, 'updatePerson'])->middleware('auth:sanctum');
    Route::delete('{personId}', [FamilyTreeController::class, 'deletePerson'])->middleware('auth:sanctum');

    // Get spouse options for delete confirmation
    Route::get('{personId}/spouse-options-for-delete', [FamilyTreeController::class, 'getSpouseOptionsForDelete'])->middleware('auth:sanctum');

    // Get spouse options for marriage update
    Route::get('{personId}/spouse-options-for-marriage', [FamilyTreeController::class, 'getSpouseOptionsForMarriage'])->middleware('auth:sanctum');

    // Get children with relations for delete child form
    Route::get('{personId}/children-with-relations', [FamilyTreeController::class, 'getChildrenWithRelations'])->middleware('auth:sanctum');

    // Delete child relation (single)
    Route::delete('{parentId}/children/{childId}', [FamilyTreeController::class, 'deleteChildRelation'])->middleware('auth:sanctum');

    // Delete all child relations
    Route::delete('{parentId}/children/all', [FamilyTreeController::class, 'deleteAllChildRelations'])->middleware('auth:sanctum');

    // Marriage routes
    Route::prefix('marriages')->group(function () {
        Route::put('{marriageId}', [FamilyTreeController::class, 'updateMarriage'])->middleware('auth:sanctum');
        Route::delete('{marriageId}', [FamilyTreeController::class, 'deleteMarriage'])->middleware('auth:sanctum');
    });

    // ============ Public ==================
    Route::get('/{identifier}/tree', [FamilyTreeController::class, 'getFamilyTree'])->middleware('optional.auth'); // GET /api/people/{identifier}/tree
    Route::get('/search', [FamilyTreeController::class, 'search']);           // GET    /api/people/search?keyword=...

    Route::post('/check-relationship', [FamilyRelationshipController::class, 'check']);
});

Route::prefix('books')->middleware('auth:sanctum')->group(function () {

    Route::get('/book/{bookId}/preview', [BookPdfController::class, 'preview']);

    Route::get('/book/{uuid}/download', [BookPdfController::class, 'download']);
});

Route::get('/test', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API jalan',
    ]);
});

// 4|X7UtX7mRImr0FDmFekFRKxjMZQvXaeihxcroOM1a20ef4c00
