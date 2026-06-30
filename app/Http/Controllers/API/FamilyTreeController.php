<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Services\FamilyTreeSearchService;
use App\Services\FamilyTreeService;
use App\Services\FamilyTreeStoreService;
use App\Services\FamilyTreeUpdateService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class FamilyTreeController extends Controller
{
    public function __construct(
        protected FamilyTreeService $familyTreeService,
        protected FamilyTreeStoreService $familyTreeStoreService,
        protected FamilyTreeSearchService $familyTreeSearchService,
        protected FamilyTreeUpdateService $familyTreeUpdateService
    ) {
    }

    /**
     * POST /api/people
     * 
     * Support 2 mode:
     * 1. Buat person baru: kirim full_name, gender, birth_year, dll
     * 2. Pakai person existing: kirim selected_person_id
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                // === Mode 1: Buat person baru ===
                'full_name'         => 'nullable|string|max:255',
                'nickname'          => 'nullable|string|max:100',
                'gender'            => 'nullable|in:male,female',
                'birth_year'        => 'nullable|integer|min:1900|max:' . date('Y'),
                'birth_month'       => 'nullable|integer|min:1|max:12',
                'death_date'        => 'nullable|date',
                'birth_place'       => 'nullable|string|max:255',
                'photo_path'        => 'nullable|string|max:2048',
                'bio'               => 'nullable|string',
                
                // === Mode 2: Pakai person existing ===
                'selected_person_id' => 'nullable|integer|exists:people,id',
                
                // === Source & Relasi ===
                'source'            => 'required|in:self,spouse,child,parent',
                'related_person_id' => 'required_if:source,spouse,child,parent|integer|exists:people,id',
                'spouse_id'         => 'nullable|integer|exists:people,id|different:related_person_id',
                'additional_parent_id' => 'nullable|integer|exists:people,id|different:related_person_id',
                'child_ids'         => 'nullable|array',
                'child_ids.*'       => 'integer|exists:people,id',
                'relation_type'     => 'nullable|in:biological,adopted,step',
                
                // === Marriage data ===
                'marriage_date'     => 'nullable|date',
                'divorce_date'      => 'nullable|date|after:marriage_date',
                'notes'             => 'nullable|string',
            ]);

            // ============================================================
            // HANDLE selected_person_id: ambil data dari database
            // ============================================================
            if (!empty($validated['selected_person_id'])) {
                $existingPerson = Person::find($validated['selected_person_id']);
                
                if (!$existingPerson) {
                    throw ValidationException::withMessages([
                        'selected_person_id' => ['Data person tidak ditemukan.'],
                    ]);
                }

                // Ambil data dari database, override form
                $validated['full_name'] = $existingPerson->full_name;
                $validated['gender'] = $existingPerson->gender;
                $validated['nickname'] = $existingPerson->nickname;
                $validated['birth_place'] = $existingPerson->birth_place;
                $validated['death_date'] = $existingPerson->death_date;
                
                // Konversi birth_date ke birth_year + birth_month
                if ($existingPerson->birth_date) {
                    $birthDate = Carbon::parse($existingPerson->birth_date);
                    $validated['birth_year'] = $birthDate->year;
                    $validated['birth_month'] = $birthDate->month;
                } else {
                    $validated['birth_year'] = null;
                    $validated['birth_month'] = null;
                }

                // Photo path
                $validated['photo_path'] = $existingPerson->photo_path;
            }

            // ============================================================
            // VALIDASI: Pastikan data person lengkap
            // ============================================================
            if (empty($validated['full_name'])) {
                throw ValidationException::withMessages([
                    'full_name' => ['Nama lengkap wajib diisi.'],
                ]);
            }
            if (empty($validated['gender'])) {
                throw ValidationException::withMessages([
                    'gender' => ['Jenis kelamin wajib diisi.'],
                ]);
            }

            // ============================================================
            // PROSES
            // ============================================================
            $result = $this->familyTreeStoreService->addPersonWithRelation($validated);

            return response()->json([
                'success' => true,
                'message' => $this->successMessage($validated['source']),
                'data'    => $result,
            ], 201);

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * GET /api/people/search?keyword=...
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'keyword' => 'required|string|min:2|max:100',
            ]);

            $results = $this->familyTreeSearchService->searchPerson($validated['keyword']);

            return response()->json([
                'success' => true,
                'count'   => count($results),
                'data'    => $results,
            ]);

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * GET /api/people/{identifier}/tree
     * 
     * @param Request $request
     * @param string $identifier
     * @param int $level - level maksimal (default 2, min 2, max 5)
     * 
     * Contoh: /api/people/1/tree?level=3
     */
    public function getFamilyTree(Request $request, string $identifier): JsonResponse
    {
        try {
            // Ambil parameter level dari query string, default 2
            $level = (int) $request->query('level', 2);
            
            // Batasi level antara 2-5
            $level = max(2, min(5, $level));
            
            $tree = $this->familyTreeService->getFamilyTree($identifier, $level);

            return response()->json([
                'success' => true,
                'data'    => $tree,
            ]);

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * GET /api/people/{personId}/spouse-options
     */
    public function getSpouseOptionsForChildForm(Request $request, int $personId): JsonResponse
    {
        try {
            $options = $this->familyTreeStoreService->getSpouseOptionsForChildForm($personId);

            return response()->json([
                'success' => true,
                'data'    => $options,
            ]);

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * GET /api/people/{personId}/spouse-options-with-children
     */
    public function getSpouseOptionsWithChildren(Request $request, int $personId): JsonResponse
    {
        try {
            $options = $this->familyTreeStoreService->getSpouseOptionsWithChildren($personId);

            return response()->json([
                'success' => true,
                'data'    => $options,
            ]);

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    
    /**
     * PUT /api/people/{personId}
     * Update data person
     */
    public function updatePerson(Request $request, int $personId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'full_name' => 'sometimes|string|max:255',
                'nickname' => 'sometimes|nullable|string|max:100',
                'gender' => 'sometimes|in:male,female',
                'birth_year' => 'sometimes|nullable|integer|min:1900|max:' . date('Y'),
                'birth_month' => 'sometimes|nullable|integer|min:1|max:12',
                'death_date' => 'sometimes|nullable|date',
                'birth_place' => 'sometimes|nullable|string|max:255',
                'photo_path' => 'sometimes|nullable|string|max:2048',
                'bio' => 'sometimes|nullable|string',
            ]);

            $result = $this->familyTreeUpdateService->updatePerson($personId, $validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['person'],
            ]);

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * DELETE /api/people/{personId}
     * Hapus person beserta semua relasinya
     */
    public function deletePerson(Request $request, int $personId): JsonResponse
    {
        try {
            $result = $this->familyTreeUpdateService->deletePerson($personId);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result,
            ]);

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * GET /api/people/{personId}/spouse-options-for-delete
     * Get spouse options untuk konfirmasi hapus
     */
    public function getSpouseOptionsForDelete(Request $request, int $personId): JsonResponse
    {
        try {
            $options = $this->familyTreeUpdateService->getSpouseOptionsForDelete($personId);

            return response()->json([
                'success' => true,
                'data' => $options,
            ]);

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * PUT /api/marriages/{marriageId}
     * Update marriage (tanggal nikah dan cerai)
     */
    public function updateMarriage(Request $request, int $marriageId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'marriage_date' => 'nullable|date',
                'divorce_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            $result = $this->familyTreeUpdateService->updateMarriage($marriageId, $validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['marriage'],
            ]);

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * DELETE /api/marriages/{marriageId}
     * Hapus marriage
     */
    public function deleteMarriage(Request $request, int $marriageId): JsonResponse
    {
        try {
            $result = $this->familyTreeUpdateService->deleteMarriage($marriageId);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['deleted_marriage'],
            ]);

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * GET /api/people/{personId}/spouse-options-for-marriage
     * Get spouse options untuk form update marriage
     */
    public function getSpouseOptionsForMarriage(Request $request, int $personId): JsonResponse
    {
        try {
            $options = $this->familyTreeUpdateService->getSpouseOptionsForMarriageForm($personId);

            return response()->json([
                'success' => true,
                'data' => $options,
            ]);

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * GET /api/people/{personId}/children-with-relations
     * Get children dengan informasi relasi untuk form delete child
     */
    public function getChildrenWithRelations(Request $request, int $personId): JsonResponse
    {
        try {
            $result = $this->familyTreeUpdateService->getChildrenWithRelations($personId);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * DELETE /api/people/{parentId}/children/{childId}
     * Hapus relasi anak (melepas anak dari orang tua)
     */
    public function deleteChildRelation(Request $request, int $parentId, int $childId): JsonResponse
    {
        try {
            $result = $this->familyTreeUpdateService->deleteChildRelation($parentId, $childId);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'parent' => $result['parent'],
                    'child' => $result['child'],
                ],
            ]);

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    /**
     * DELETE /api/people/{parentId}/children/all
     * Hapus semua relasi anak dari orang tua
     */
    public function deleteAllChildRelations(Request $request, int $parentId): JsonResponse
    {
        try {
            $result = $this->familyTreeUpdateService->deleteAllChildRelations($parentId);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result,
            ]);

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    // ==================== RESPONSE HELPERS ====================

    private function successMessage(string $source): string
    {
        return match ($source) {
            'self'   => 'Data person berhasil ditambahkan',
            'spouse' => 'Pasangan berhasil ditambahkan',
            'child'  => 'Anak berhasil ditambahkan',
            'parent' => 'Orang tua berhasil ditambahkan',
            default  => 'Data berhasil ditambahkan',
        };
    }

    private function validationErrorResponse(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors'  => $e->errors(),
        ], 422);
    }

    private function notFoundResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan',
        ], 404);
    }

    private function serverErrorResponse(\Throwable $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
        ], 500);
    }
}