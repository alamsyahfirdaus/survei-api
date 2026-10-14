<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('user');

        $totalSurveys = Survey::where('user_id', $user->id)->count();

        $totalCategories = Survey::where('user_id', $user->id)
            ->distinct('category_id')
            ->count('category_id');

        return response()->json([
            'status' => true,
            'message' => 'Data dashboard berhasil diambil.',
            'data' => [
                'total_surveys' => $totalSurveys,
                'total_categories' => $totalCategories,
            ],
        ]);
    }
}
