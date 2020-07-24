<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Document;
use App\DocumentTag;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $document = Document::all();

        $collection = collect($document)->map(function ($data) {
            $data->user;
            return $data;
        });
        // $document->user;
        return response()->json($collection, 200);
    }

    public function create(Request $request)
    {
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'file' => 'required|file|max:5000|mimes:pdf,doc,docx,jpeg,png,jpg,gif',
            'created_by' => 'required|integer|max:1',
            'status' => 'required|string|in:PENDING,APPROVED',
            'description' => 'required',
            'tag_id' => 'required|array|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages());
        }

        try {
            $document = $request->except(['tag_id']);

            $filePath = $request->file('file')->store('uploads', 's3');

            // Storage::disk('s3')->setVisibility($filePath, 'public');
            // Storage::disk('s3')->url($filePath);

            $document['file'] = basename($filePath);

            $saved_document = Document::create($document);

            $document_tag = $request->only(['tag_id']);

            foreach ($document_tag['tag_id'] as $tag_id) {
                $tag['tag_id'] = $tag_id['id'];
                $tag['document_id'] = $saved_document['id'];
                DocumentTag::create($tag);
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }

        return response()->json(['success' => $document], 200);
    }

    public function show($file)
    {
        return Storage::disk('s3')->response('uploads/' . $file);
    }
}
