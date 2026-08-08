<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    // Opsi 1 — Dengan Filter User
    public function index(Request $request)
    {
        $user = $request->attributes->get('user');

        $surveys = Survey::with('category:id,name')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $surveys->map(function ($survey) {
            return [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
                'category_id' => $survey->category_id,
                'category_name' => $survey->category
                    ? $survey->category->name
                    : null,
                'latitude' => $survey->latitude,
                'longitude' => $survey->longitude,
                'created_at' => $survey->created_at
                    ? $survey->created_at->format('Y-m-d H:i:s')
                    : null,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Daftar survei berhasil diambil.',
            'data'    => $data,
        ]);
    }

    // Opsi 2 — Tanpa Filter User
    // public function index1(Request $request)
    // {
    //     $surveys = Survey::with('category:id,name')
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     $data = $surveys->map(function ($survey) {
    //         return [
    //             'id' => $survey->id,
    //             'title' => $survey->title,
    //             'description' => $survey->description,
    //             'category_id' => $survey->category_id,
    //             'category_name' => $survey->category ? $survey->category->name : null,
    //             'latitude' => $survey->latitude,
    //             'longitude' => $survey->longitude,
    //             'created_at' => $survey->created_at
    //                 ? $survey->created_at->format('Y-m-d H:i:s')
    //                 : null,
    //         ];
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'data' => $data,
    //     ]);
    // }

    public function surveyCategories()
    {
        $categories = Category::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $categories,
        ]);
    }

    public function save(Request $request)
    {
        $user = $request->attributes->get('user');

        // Validasi
        $validator = Validator::make(
            $request->all(),
            [
                'id'          => 'nullable|integer',
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'latitude'    => 'nullable|numeric',
                'longitude'   => 'nullable|numeric',
            ],
            [
                'id.integer' => 'ID survei harus berupa angka.',

                'title.required' => 'Judul survei wajib diisi.',
                'title.string'   => 'Judul survei harus berupa teks.',
                'title.max'      => 'Judul survei maksimal 255 karakter.',

                'description.string' =>
                    'Deskripsi survei harus berupa teks.',

                'category_id.required' =>
                    'Kategori survei wajib dipilih.',
                'category_id.exists' =>
                    'Kategori survei yang dipilih tidak ditemukan.',

                'latitude.numeric' =>
                    'Latitude harus berupa angka.',
                'longitude.numeric' =>
                    'Longitude harus berupa angka.',
            ]
        );

        // Validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Data yang dikirim tidak valid.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // UPDATE
        if ($request->filled('id')) {

            $survey = Survey::where('id', $request->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$survey) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data survei tidak ditemukan.',
                ], 404);
            }

            $survey->update([
                'title'       => $request->title,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'latitude'    => $request->latitude,
                'longitude'   => $request->longitude,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Data survei berhasil diperbarui.',
                'data'    => $survey,
            ]);
        }

        // INSERT
        $survey = Survey::create([
            'user_id'     => $user->id,
            'title'       => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Data survei berhasil disimpan.',
            'data'    => $survey,
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $user = $request->attributes->get('user');

        $survey = Survey::with('category:id,name')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$survey) {
            return response()->json([
                'status'  => false,
                'message' => 'Data survei tidak ditemukan.',
            ], 404);
        }

        $data = [
            'id' => $survey->id,
            'title' => $survey->title,
            'description' => $survey->description,
            'category_id' => $survey->category_id,
            'category_name' => $survey->category
                ? $survey->category->name
                : null,
            'latitude' => $survey->latitude,
            'longitude' => $survey->longitude,
            'created_at' => $survey->created_at
                ? $survey->created_at->format('Y-m-d H:i:s')
                : null,
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Data survei berhasil ditemukan.',
            'data'    => $data,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->attributes->get('user');

        $survey = Survey::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$survey) {
            return response()->json([
                'status' => false,
                'message' => 'Data survei tidak ditemukan.',
            ], 404);
        }

        $survey->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data survei berhasil dihapus.',
        ]);
    }
}
