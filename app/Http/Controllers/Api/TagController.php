<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tag;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    public function index()
    {
        $model = Tag::searchPaginateAndOrder();
        $columns = Tag::$columns;

        return response()->json([
            'model' => $model,
            'columns' => $columns
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:12'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages());
        }

        try {
            $tag = $request->all();
            $tag['created_by'] = $request->user()->id;
            Tag::create($tag);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }

        return response()->json(['success' => 'tag berhasil ditambahkan'], 200);
    }

    public function destroy($id)
    {
        try {
            Tag::findOrFail($id)->delete();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'tag gagal dihapus']);
        }
        return response()->json(['success' => 'tag berhasil dihapus'], 200);
    }
}
