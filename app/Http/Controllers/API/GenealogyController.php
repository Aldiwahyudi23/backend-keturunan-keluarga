<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Report\GenealogyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GenealogyController extends Controller
{
    protected GenealogyService $genealogyService;

    public function __construct(GenealogyService $genealogyService)
    {
        $this->genealogyService = $genealogyService;
    }

    /**
     * Get genealogy/keturunan berdasarkan person UUID
     * 
     * @param string $uuid UUID person
     * @param Request $request
     * @return JsonResponse
     * 
     * Query Parameters:
     * - max_generations: Jumlah generasi maksimal (optional, default: unlimited)
     * - format: Format output [json|text] (optional, default: json)
     * 
     * Contoh:
     * GET /api/genealogy/550e8400-e29b-41d4-a716-446655440000
     * GET /api/genealogy/550e8400-e29b-41d4-a716-446655440000?max_generations=5
     * GET /api/genealogy/550e8400-e29b-41d4-a716-446655440000?format=text
     */
    public function show(string $uuid, Request $request): JsonResponse
    {
        $request->validate([
            'max_generations' => 'nullable|integer|min:1',
            'format' => 'nullable|in:json,text',
        ]);

        $maxGenerations = $request->query('max_generations', 0);
        $format = $request->query('format', 'json');

        // Generate genealogy data
        $genealogyData = $this->genealogyService->generateGeneology($uuid, $maxGenerations);

        // Jika error, return dengan status 404
        if (!$genealogyData['success']) {
            return response()->json($genealogyData, 404);
        }

        // Jika format text, return sebagai text
        if ($format === 'text') {
            return response()->json([
                'success' => true,
                'message' => 'Genealogy berhasil di-generate',
                'data' => [
                    'root_person' => $genealogyData['data']['root_person'],
                    'content' => $this->genealogyService->formatAsText($genealogyData),
                    'format' => 'text',
                ],
            ]);
        }

        // Default: return JSON structured
        return response()->json($genealogyData);
    }


}