<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Document;
use App\DocumentTag;
use App\Notifications\PendingFile;
use App\Tag;
use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

    public function documents(Request $request)
    {
        $operators = [
            'equal' => '=',
            'not_equal' => '!=',
            'less_than' => '<',
            'greather_than' => '>',
            'less_than_or_equal_to' => '<=',
            'greater_than_or_equal_to' => '>=',
            'in' => 'IN',
            'like' => 'LIKE'
        ];

        $validator = Validator::make($request->only([
            'column', 'direction', 'per_page',
            'search_column', 'search_operator', 'search_input'
        ]), [
            'column' => 'required|alpha_dash|in:' . implode(',', Document::$columns_documents),
            'direction' => 'required|in:asc,desc',
            'per_page' => 'integer|min:1',
            'search_column' => 'required|alpha_dash|in:' . implode(',', Document::$columns_documents),
            'search_operator' => 'required|alpha_dash|in:' . implode(',', array_keys($operators)),
            'search_input' => 'max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson());
        }

        try {

            $special_column = null;
            $document_tag = null;

            if ($request->search_column == 'created_by' || $request->search_column == 'approved_by' || $request->search_column == 'tag') {
                $special_column = DB::table($request->search_column == 'tag' ? 'tags' : 'users')->where(function ($query) use ($request, $operators) {
                    if ($request->search_input != '') {
                        if ($request->search_operator == 'in') {
                            $query->whereIn($request->search_column == 'tag' ? 'name' : 'username', explode(',', $request->search_input));
                        } else if ($request->search_operator == 'like') {
                            $query->where($request->search_column == 'tag' ? 'name' : 'username', 'LIKE', '%' . $request->search_input . '%');
                        } else {
                            $query->where($request->search_column == 'tag' ? 'name' : 'username', $operators[$request->search_operator], $request->search_input);
                        }
                    }
                })->first();

                if ($request->search_column == 'tag') {
                    $document_tag = DocumentTag::where('tag_id', $special_column->id)->get();
                    $document_tag = collect($document_tag)->pluck('document_id')->all();
                }
            }

            $document = Document::orderBy($request->column, $request->direction)
                ->where('status', 'APPROVED')
                ->where(function ($query) use ($request, $operators, $special_column, $document_tag) {
                    if ($request->search_input != '') {
                        if ($request->search_operator == 'in') {
                            $query->whereIn($request->search_column, explode(',', $request->search_input));
                        } else if ($request->search_operator == 'like') {
                            $query->where($request->search_column, 'LIKE', '%' . $request->search_input . '%');
                        } else {
                            if ($request->search_column != 'tag') {
                                $query->where($request->search_column, $operators[$request->search_operator], $special_column ? $special_column->id : $request->search_input);
                            } else {
                                $query->whereIn('id', $document_tag);
                            }
                        }
                    }
                })
                ->get();

            $document = collect($document)->map(function ($data) use ($request, $operators) {
                $data->user;
                $data->approved_by_user;
                $data->documents_tags;
                $data['documents_tags'] = collect($data->documents_tags)->map(function ($dt) {
                    $dt->tag;
                    return $dt;
                });
                return $data;
            });

            $document = $this->paginate($document, $request->per_page);

            $columns = Document::$columns_documents;

            return response()->json([
                'model' => $document,
                'columns' => $columns
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    // fungsi pagination untuk data collection
    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
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

            if ($saved_document->status == 'PENDING') {
                $admins = User::where('role', 1)->get();

                foreach ($admins as $admin) {
                    \Notification::route('mail', $admins[0]->email)->notify(new PendingFile($admins[0]));
                }
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }

        return response()->json(['success' => $admins[0]->email], 200);
    }

    public function getTotalPendingDocuments()
    {
        try {

            $pending_document = Document::where('status', 'PENDING')->get();
            return response()->json(count($pending_document), 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
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

    public function download($file)
    {
        return Storage::disk('s3')->response('uploads/' . $file);
    }

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

            if (count($oldTags) > 0) {
                foreach ($oldTags as $oldTag) {
                    DocumentTag::where('document_id', $id)->delete();
                }
            }

            if (count($tags_id) > 0) {
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
