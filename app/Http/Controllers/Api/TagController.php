<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Log;
use App\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    // fungsi untuk menampilan data tag
    public function index()
    {
        // panggil query helper
        $model = Tag::searchPaginateAndOrder();

        // ambil columns dari model
        $columns = Tag::$columns;

        return response()->json([
            'model' => $model,
            'columns' => $columns
        ]);
    }

    // fungsi untk
    public function getAllTag()
    {
        try {
            $tag = Tag::get();
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
        return response()->json($tag, 200);
    }

    // fungsi untuk membuat tag baru
    public function store(Request $request)
    {
        // validasi request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:12'
        ]);

        // jika tidak lolos validasi
        if ($validator->fails()) {
            return response()->json($validator->errors()->messages());
        }

        try {
            // ambil semua requst
            $tag = $request->all();
            // ambil id pembuat tag
            $tag['created_by'] = $request->user()->id;
            // buat tag baru
            $newTag = Tag::create($tag);
            // buat log untuk pembuatan tag baru
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'tag',
                'type_id' => $newTag->id,
                'controller' => 'TagController',
                'function' => 'store',
                'action' => 'create',
                'before' => null,
                'after' => json_encode($newTag)
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }

        return response()->json(['success' => 'tag berhasil ditambahkan'], 200);
    }

    // fungsi untuk menghapus tag
    public function destroy($id)
    {
        try {
            // ambil data tag
            $oldTag = Tag::findOrFail($id);
            // hapus data tag
            Tag::findOrFail($id)->delete();
            // buat log untuk penghapusan tag
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'tag',
                'type_id' => $oldTag->id,
                'controller' => 'TagController',
                'function' => 'destroy',
                'action' => 'delete',
                'before' => json_encode($oldTag),
                'after' => null
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
        return response()->json(['success' => 'tag berhasil dihapus'], 200);
    }

    public function show($id)
    {
        $tag = Tag::findOrFail($id);
        // $tag->documents_tags;

        $tag['document_tags'] = collect($tag->documents_tags)->map(function ($data) {
            $data->document;
            $data->tag;
            return $data;
        });
        return response()->json($tag);
    }
}
