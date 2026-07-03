<?php

use App\Http\Controllers\Api\FamilyRelationshipController;
use App\Http\Controllers\Api\FamilyTreeController;
use App\Http\Controllers\API\VehicleController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\GenealogyController;

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookPdfController;

Route::get('/book/{uuid}', [BookController::class, 'show']);

Route::get('/book/{uuid}/pdf', [BookPdfController::class, 'show']);

Route::get('/book/{uuid}/download', [BookPdfController::class, 'download']);

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

    // Genealogy routes
    //  * Contoh Penggunaan:
    //  * GET /api/genealogy/550e8400-e29b-41d4-a716-446655440000
    //  * GET /api/genealogy/550e8400-e29b-41d4-a716-446655440000?max_generations=3
    //  * GET /api/genealogy/550e8400-e29b-41d4-a716-446655440000?format=text
    //  */
    Route::get('/genealogy/{uuid}', [GenealogyController::class, 'show'])->name('genealogy.show');

    // ============ Public ==================
    Route::get('/{identifier}/tree', [FamilyTreeController::class, 'getFamilyTree'])->middleware('optional.auth'); // GET /api/people/{identifier}/tree
    Route::get('/search', [FamilyTreeController::class, 'search']);           // GET    /api/people/search?keyword=...

    Route::post('/check-relationship',[FamilyRelationshipController::class, 'check']);
});

// API Documentation route
Route::get('/docs', function () {
    return response()->json([
        'message' => 'Vehicle Management API v1',
        'endpoints' => [
            'GET /api/v1/vehicles' => 'List all vehicles with filters',
            'GET /api/v1/vehicles/{id}' => 'Get specific vehicle details',
            'GET /api/v1/brands' => 'List all vehicle brands',
            'GET /api/v1/brands/{id}/models' => 'Get models by brand',
            'GET /api/v1/vehicles/search?query=...' => 'Search vehicles',
        ],
        'filters' => [
            'brand' => 'Filter by brand ID',
            'model' => 'Filter by model ID',
            'year_from' => 'Filter by minimum year',
            'year_to' => 'Filter by maximum year',
            'fuel_type' => 'Filter by fuel type',
            'min_cc' => 'Filter by minimum engine CC',
            'max_cc' => 'Filter by maximum engine CC',
            'sort' => 'Sort by field (prefix with - for descending)',
            'per_page' => 'Items per page (max 100)',
        ]
    ]);
});

Route::get('/test', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API jalan'
    ]);
});

// 4|X7UtX7mRImr0FDmFekFRKxjMZQvXaeihxcroOM1a20ef4c00