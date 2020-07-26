<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Document;
use App\DocumentTag;
use Illuminate\Support\Facades\Storage;

use function PHPSTORM_META\map;

class DocumentController extends Controller
{
    public function index()
    {
        $model = Document::searchPaginateAndOrder();

        $columns = Document::$columns;
        return response()->json([
            'model' => $model,
            'columns' => $columns
        ]);
    }

    public function getMyDocument($id)
    {
        $model = Document::searchPaginateAndOrder();
        $model = json_encode($model);
        $model = json_decode($model);

        $model->data = collect($model->data)->where('created_by', $id);
        $model->total = count($model->data);
        $model->to = count($model->data);

        $columns = Document::$columns;
        return response()->json([
            'model' => $model,
            'columns' => $columns
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'file' => 'required|file|max:5000|mimes:pdf,doc,docx,jpeg,png,jpg,gif',
            'created_by' => 'required|integer',
            'status' => 'required|string|in:PENDING,APPROVED',
            'description' => 'required',
            'tag_id' => 'required|string|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->getMessages());
        }

        try {
            $document = $request->except(['tag_id']);

            $filePath = $request->file('file')->store('uploads', 's3');

            // Storage::disk('s3')->setVisibility($filePath, 'public');
            // Storage::disk('s3')->url($filePath);

            $document['file'] = basename($filePath);

            $saved_document = Document::create($document);

            $document_tag = $request->only(['tag_id']);
            $tags_id = json_decode($document_tag['tag_id'], true);

            foreach ($tags_id as $tag_id) {
                $tag['tag_id'] = $tag_id['id'];
                $tag['document_id'] = $saved_document['id'];
                DocumentTag::create($tag);
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }

        return response()->json(['success' => 'berhasil'], 200);
    }

    public function getPendingDocument(Request $request)
    {
        $model = Document::searchPaginateAndOrder();

        $model = json_encode($model);
        $model = json_decode($model);

        $model->data = collect($model->data)->where('status', 'PENDING');
        $model->total = count($model->data);
        $model->to = count($model->data);

        $columns = Document::$columns;
        return response()->json([
            'model' => $model,
            'columns' => $columns
        ]);
    }

    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:PENDING,APPROVED',
            'approved_by' => 'required|int'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages());
        }
        try {
            $file = $request->all();

            $file['approved_at'] = now();
            Document::where('id', $id)->update($file);
            return response()->json(['success' => 'berhasil disetujui'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    // public function show($file)
    // {
    //     return Storage::disk('s3')->response('uploads/' . $file);
    // }

    public function show($id)
    {
        try {
            $document = Document::findOrfail($id);
            $document->user;
            $document->approved_by_user;
            $document['documents_tags'] = collect($document->documents_tags)->map(function ($data) {
                $data->tag;
                return $data;
            });
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }

        return response()->json($document, 200);
    }


    // note : kurang remove file jika ada file yang di update
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'file' => 'sometimes|nullable|file|max:5000|mimes:pdf,doc,docx,jpeg,png,jpg,gif',
            'status' => 'required|string|in:PENDING,APPROVED',
            'description' => 'required',
            'tag_id' => 'required|string|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $document = $request->except(['tag_id', '_method', 'file']);

            if ($request->hasFile('file') || $request->file != '') {
                $document = $request->except(['tag_id', '_method']);
                return response()->json(['a' => 'ada file'], 200);
            }

            Document::where('id', $id)->update($document);

            $document_tag = $request->only(['tag_id']);
            $tags_id = json_decode($document_tag['tag_id'], true);

            // get old tags
            $oldTags = DocumentTag::where('document_id', $id)->get();

            if (count($oldTags) > 1) {
                foreach ($oldTags as $oldTag) {
                    DocumentTag::where('document_id', $id)->delete();
                }
            }

            if (count($tags_id) > 1) {
                foreach ($tags_id as $tag_id) {
                    $tag['tag_id'] = $tag_id['id'];
                    $tag['document_id'] = $id;
                    DocumentTag::create($tag);
                }
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }

        return response()->json(['success' => 'dokumen berhasil diperbarui'], 200);
    }

    public function destroy($id)
    {
        try {
            Document::where('id', $id)->delete();
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }

        return response()->json(['success' => 'dokumen dihapus'], 200);
    }
}
