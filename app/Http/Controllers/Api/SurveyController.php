<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
                'photo' => $survey->photo ?: 'placeholder.jpg',
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
    public function index2(Request $request)
    {
        $surveys = Survey::with('category:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $surveys->map(function ($survey) {
            return [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
                'category_id' => $survey->category_id,
                'category_name' => $survey->category ? $survey->category->name : null,
                'photo' => $survey->photo ?: 'placeholder.jpg',
                'latitude' => $survey->latitude,
                'longitude' => $survey->longitude,
                'created_at' => $survey->created_at
                    ? $survey->created_at->format('Y-m-d H:i:s')
                    : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

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
                'category_id' => 'required|exists:a_survey_categories,id',
                'latitude'    => 'nullable|numeric',
                'longitude'   => 'nullable|numeric',
                'photo'       => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
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

                'photo.image' => 'File harus berupa gambar.',
                'photo.mimes' => 'Foto harus berformat jpeg, png, jpg, atau gif.',
                'photo.max'   => 'Ukuran foto maksimal 2 MB.',
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

            $updateData = [
                'title'       => $request->title,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'latitude'    => $request->latitude,
                'longitude'   => $request->longitude,
            ];

            // Upload Foto Baru (mengikuti pola updateProfileV1)
            if ($request->hasFile('photo')) {
                // Pastikan folder tersedia
                $destinationPath = public_path('images');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Simpan foto baru terlebih dahulu
                $fileName = Str::random(20) . '.' . $request->file('photo')->extension();
                $request->file('photo')->move($destinationPath, $fileName);

                // Hapus foto lama hanya jika foto baru dipastikan berhasil disimpan
                if (!empty($survey->photo) && $survey->photo !== 'placeholder.jpg') {
                    $oldPath = public_path('images/' . $survey->photo);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $updateData['photo'] = $fileName;
            }

            $survey->update($updateData);

            return response()->json([
                'status'  => true,
                'message' => 'Data survei berhasil diperbarui.',
                'data'    => $survey,
            ], 200);
        }

        // INSERT
        $insertData = [
            'user_id'     => $user->id,
            'title'       => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
        ];

        // Upload Foto Baru jika ada
        if ($request->hasFile('photo')) {
            $destinationPath = public_path('images');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $fileName = Str::random(20) . '.' . $request->file('photo')->extension();
            $request->file('photo')->move($destinationPath, $fileName);

            $insertData['photo'] = $fileName;
        }

        $survey = Survey::create($insertData);

        return response()->json([
            'status'  => true,
            'message' => 'Data survei berhasil disimpan.',
            'data'    => $survey,
        ], 201);
    }

    public function show1(Request $request, string $id)
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

    public function show(Request $request, string $id)
    {
        $survey = Survey::with('category:id,name')
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
            'photo'    => $survey->photo ?: 'placeholder.jpg',
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

        // Hapus foto jika ada dan bukan placeholder
        if (!empty($survey->photo) && $survey->photo !== 'placeholder.jpg') {
            $photoPath = public_path('images/' . $survey->photo);
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        $survey->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data survei berhasil dihapus.',
        ]);
    }
}
