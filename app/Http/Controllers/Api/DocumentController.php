<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Document;
use App\DocumentTag;
use App\Log;
use App\Notifications\PendingFile;
use App\Tag;
use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\map;

class DocumentController extends Controller
{
    public function index()
    {
        // query model menggunakan helper
        $model = Document::searchPaginateAndOrder();

        // panggil columns dari model
        $columns = Document::$columns;

        // kembalikan ke frontend berupa respon json
        return response()->json([
            'model' => $model,
            'columns' => $columns
        ]);
    }

    public function documents(Request $request)
    {
        // deklarasi operator untuk query filter
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

        // validasi request dari front end
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

        // jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson());
        }

        try {

            // definisi variabel untuk kolom created_by,approved_by,tag
            $special_column = null;
            $document_tag = null;

            // kondisi jika request column berupa created_by,approved_by,tag
            if ($request->search_column == 'created_by' || $request->search_column == 'approved_by' || $request->search_column == 'tag') {
                // query filter dengan oop
                $special_column = DB::table($request->search_column == 'tag' ? 'tags' : 'users')->where(function ($query) use ($request, $operators) {
                    // kondisi jika search input tidak kosong
                    if ($request->search_input != '') {
                        // kondisi jika search operator berupa in maka query yg dijalankan adalah whereIn
                        if ($request->search_operator == 'in') {
                            $query->whereIn($request->search_column == 'tag' ? 'name' : 'username', explode(',', $request->search_input));
                        } else if ($request->search_operator == 'like') {
                            // jika kondisi search operator berupa lika maka query builder yang dijalankan berupa where dengan like
                            $query->where($request->search_column == 'tag' ? 'name' : 'username', 'LIKE', '%' . $request->search_input . '%');
                        } else {
                            // jika kondisi search operator selain in dan like
                            $query->where($request->search_column == 'tag' ? 'name' : 'username', $operators[$request->search_operator], $request->search_input);
                        }
                    }
                })->first();

                // jika kondisi search column 'tag'
                if ($request->search_column == 'tag') {
                    // ambil tag_id dalam table documents_tags berdasarkan query filter diatas
                    $document_tag = DocumentTag::where('tag_id', $special_column->id)->get();
                    // ambil value setiap document_id dan dijadikan array
                    $document_tag = collect($document_tag)->pluck('document_id')->all();
                }
            }

            // query filter dokumen dengan status 'APPROVED'
            $document = Document::orderBy($request->column, $request->direction)
                ->where('status', 'APPROVED')
                ->where(function ($query) use ($request, $operators, $special_column, $document_tag) {
                    // jika request search input tidak kosong
                    if ($request->search_input != '') {
                        // kondisi jika search operator berupa in maka query yg dijalankan adalah whereIn
                        if ($request->search_operator == 'in') {
                            $query->whereIn($request->search_column, explode(',', $request->search_input));
                        } else if ($request->search_operator == 'like') {
                            // jika kondisi search operator berupa lika maka query builder yang dijalankan berupa where dengan like
                            $query->where($request->search_column, 'LIKE', '%' . $request->search_input . '%');
                        } else {
                            // jika request search column bukan tag
                            if ($request->search_column != 'tag') {
                                // query where jika special column ada maka query berdasarkan id-nya jika tidak maka berdasarkan search input
                                $query->where($request->search_column, $operators[$request->search_operator], $special_column ? $special_column->id : $request->search_input);
                            } else {
                                // jika 'tag' maka lakukan query whereIn berdasarkan variabel document tag yag berisi array
                                $query->whereIn('id', $document_tag);
                            }
                        }
                    }
                })
                ->get();

            // panggil relasi dengan collection
            $document = collect($document)->map(function ($data) {
                // relasi user
                $data->user;

                // relasi siapa yang menyetujui (APPROVED) dokumen
                $data->approved_by_user;

                // relasi tag_id apa saja yang ada di dokumen
                $data->documents_tags;
                $data['documents_tags'] = collect($data->documents_tags)->map(function ($dt) {
                    // relasi tag berdasarkan tag_id
                    $dt->tag;
                    return $dt;
                });
                return $data;
            });

            // menggunakan fungsi paginasi untuk hasil data collection
            $document = $this->paginate($document, $request->per_page);

            // panggil columns_documents dari model
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

    // fungsi untuk mengambil data file apa saja yang dimiliki user berdasarkan id user
    public function getMyDocument($id)
    {
        // panggil query helper
        $model = Document::searchPaginateAndOrder();
        // ubah kedalam string
        $model = json_encode($model);
        // ubah ke json
        $model = json_decode($model);
        // query data berdasarkan id sipembuat file
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
        // validasi request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'file' => 'required|file|max:5000|mimes:pdf,doc,docx,jpeg,png,jpg,gif',
            'created_by' => 'required|integer',
            'status' => 'required|string|in:PENDING,APPROVED',
            'description' => 'required',
            'tag_id' => 'required|string|min:1'
        ]);

        // jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors()->getMessages());
        }

        try {
            // mabil semua request kecuali tag_id
            $document = $request->except(['tag_id']);

            // upload file ke s3 bucket
            $filePath = $request->file('file')->store('uploads', 's3');

            // Storage::disk('s3')->setVisibility($filePath, 'public');
            // Storage::disk('s3')->url($filePath);

            // ambil nama file
            $document['file'] = basename($filePath);

            // simpan dokumen
            $saved_document = Document::create($document);

            // ambil hanya request tag_id
            $document_tag = $request->only(['tag_id']);
            // convert dari string ke json
            $tags_id = json_decode($document_tag['tag_id'], true);

            // perulangan untuk berdasarkan jumlah tag
            foreach ($tags_id as $tag_id) {
                // ambil tag id
                $tag['tag_id'] = $tag_id['id'];
                // ambil document id
                $tag['document_id'] = $saved_document['id'];

                // simpan
                DocumentTag::create($tag);
            }

            // jika status dokumen adalah pending
            if ($saved_document->status == 'PENDING') {
                // ambil semua data yang role-nya admin
                $admins = User::where('role', 1)->get();

                // kirim email ke setiap admin
                foreach ($admins as $admin) {
                    \Notification::route('mail', $admins[0]->email)->notify(new PendingFile($admins[0]));
                }
            }

            // ambil data dokumen dan panggil relasinya untuk Log
            $createdDocument = Document::findOrFail($saved_document->id);
            $createdDocument->documents_tags;
            $createdDocument->user;
            $createdDocument->approved_by_user;

            // buat log untuk pembuatan dokumen baru
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'file',
                'type_id' => $saved_document->id,
                'controller' => 'DocumentController',
                'function' => 'store',
                'action' => 'create',
                'before' => json_encode($createdDocument),
                'after' => null
            ]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }

        return response()->json(['success' => $filePath], 200);
    }

    // fungsi untuk menghitung semua dokumen yang statusnya PENDING
    public function getTotalPendingDocuments()
    {
        try {
            // ambil semua dokumen yang berstatus PENDING
            $pending_document = Document::where('status', 'PENDING')->get();

            // kembalikan respon berupa total dokumen yang statusnya masih PENDING
            return response()->json(count($pending_document), 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    // fungsi untuk mengambil semua dokumen yang statusnya PENDING
    public function getPendingDocument(Request $request)
    {
        // panggil query helper
        $model = Document::searchPaginateAndOrder();

        // mengubah hasil query helper ke string lalu di conver ke json
        $model = json_encode($model);
        $model = json_decode($model);

        // query collection ambil dokumen yang statusnya PENDING
        $model->data = collect($model->data)->where('status', 'PENDING');
        $model->total = count($model->data);
        $model->to = count($model->data);

        // panggil columns dari model
        $columns = Document::$columns;
        return response()->json([
            'model' => $model,
            'columns' => $columns
        ]);
    }

    // fungsi untuk menyetujui dokumen yang berstatus PENDING
    public function approve(Request $request, $id)
    {
        // validasi request
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:PENDING,APPROVED',
            'approved_by' => 'required|int'
        ]);

        // jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors()->messages());
        }
        try {
            // ambil dokumen berdasasrkan id-nya
            $oldFile = Document::findOrFail($id);

            // ambil semua request
            $file = $request->all();

            // isi kolom approved_at dengan waktu hari ini
            $file['approved_at'] = now();

            // update file berdasarkan id
            Document::where('id', $id)->update($file);

            // data dokumen yang telah di simpan beserta relasinya
            $approvedFile = Document::findOrFail($id);
            $approvedFile->documents_tags;
            $approvedFile->user;
            $approvedFile->approved_by_user;

            // buat log untuk pembaruan file yang di setujui
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'file',
                'type_id' => $oldFile->id,
                'controller' => 'DocumentController',
                'function' => 'approve',
                'action' => 'update',
                'before' => json_encode($oldFile),
                'after' => json_encode($approvedFile)
            ]);
            return response()->json(['success' => 'berhasil disetujui'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    // fungsi untuk download file
    public function download($file_name)
    {
        try {
            // ambil mime tipe file
            $mime = Storage::disk('s3')->getDriver()->getMimetype('uploads/' . $file_name);

            // ambil ukuran file
            $size = Storage::disk('s3')->getDriver()->getSize('uploads/' . $file_name);

            // header
            $headers =  [
                'Content-Type' => $mime,
                'Content-Length' => $size,
                'Content-Description' => 'File Transfer',
                'Content-Disposition' => "attachment; filename={$file_name}",
                'Content-Transfer-Encoding' => 'binary',
            ];

            ob_end_clean();

            return \Response::make(Storage::disk('s3')->get('uploads/' . $file_name), 200, $headers);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    // fungsi untuk meberikan data file berdasarkan id-nya
    public function show($id)
    {
        try {
            // ambil file berdasarakna id dan panggil relasinya
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


    // fungsi untuk update file
    public function update(Request $request, $id)
    {
        // validasi request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'file' => 'sometimes|nullable|file|max:5000|mimes:pdf,doc,docx,jpeg,png,jpg,gif',
            'status' => 'required|string|in:PENDING,APPROVED',
            'description' => 'required',
            'tag_id' => 'required|string|min:1'
        ]);

        // jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            // ambil semua request kecuali tag_id, _method, file
            $document = $request->except(['tag_id', '_method', 'file']);

            // ambil data file berdasarkan id-nya dan panggil relasinya
            $oldFile = Document::findOrFail($id);
            $oldFile->documents_tags;
            $oldFile->user;
            $oldFile->approved_by_user;

            // jika ada file
            if ($request->hasFile('file') || $request->file != '') {
                // ambil semua request kecuali tag_id, _method
                $document = $request->except(['tag_id', '_method']);

                // hapus file lama yang ada di s3 bucket
                Storage::disk('s3')->delete('uploads/' . $oldFile->file);

                // upload request file baru
                $filePath = $request->file('file')->store('uploads', 's3');
                // simpan nama filenya
                $document['file'] = basename($filePath);
                // return response()->json(['a' => 'ada file'], 200);
            }
            // update file berdasarkan id-nya
            Document::where('id', $id)->update($document);

            // ambil hanya request tag_id
            $document_tag = $request->only(['tag_id']);
            // merubah data tag_id dari string ke json
            $tags_id = json_decode($document_tag['tag_id'], true);

            // ambil tag lama dokumen
            $oldTags = DocumentTag::where('document_id', $id)->get();
            // jika ada tag lama
            if (count($oldTags) > 0) {
                // maka lakukan hapus tag
                foreach ($oldTags as $oldTag) {
                    DocumentTag::where('document_id', $id)->delete();
                }
            }

            // lakukan pengecekan tag
            if (count($tags_id) > 0) {
                // lakukan perulangan untuk menyimpan tag
                foreach ($tags_id as $tag_id) {
                    $tag['tag_id'] = $tag_id['id'];
                    $tag['document_id'] = $id;
                    DocumentTag::create($tag);
                }
            }

            // ambil data file yang terupdate berdasarkan id-nya dan panggil relasi-nya
            $updatedFile = Document::findOrFail($id);
            $updatedFile->documents_tags;
            $updatedFile->user;
            $updatedFile->approved_by_user;

            // buat log untuk pembaruan file
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'file',
                'type_id' => $id,
                'controller' => 'DocumentController',
                'function' => 'update',
                'action' => 'update',
                'before' => json_encode($oldFile),
                'after' => json_encode($updatedFile)
            ]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }

        return response()->json(['success' => $document], 200);
    }

    // fungsi menghapus file
    public function destroy($id)
    {
        try {
            // ambil file berdasarkan id dan panggil relasi
            $document = Document::findOrFail($id);
            $document->documents_tags;
            $document->user;
            $document->approved_by_user;

            // hapus file dari s3 bucket
            Storage::disk('s3')->delete('uploads/' . $document->file);
            $document->delete();

            // buat log untuk penghapusn file
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'file',
                'type_id' => $document->id,
                'controller' => 'DocumentController',
                'function' => 'destroy',
                'action' => 'delete',
                'before' => json_encode($document),
                'after' => null
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }

        return response()->json(['success' => 'dokumen dihapus'], 200);
    }
}
