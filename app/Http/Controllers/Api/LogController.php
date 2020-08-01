<?php

namespace App\Http\Controllers\Api;

use App\Document;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Log;
use App\User;
use Carbon\Carbon;
use App\Tag;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    // fungsi untuk menampilkan log berdasarkan created_at
    public function index()
    {
        try {
            // qurey group berdasarkan created_at secara descending
            $log = Log::orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($val) {
                    return Carbon::parse($val->created_at)->format('Y-m-d');
                });

            // perulangan untuk mengambil relasi dari model
            foreach ($log as $key => $value) {
                collect($value)->map(function ($d) {
                    // kondisi menyesuaikan type data
                    if ($d->type == 'file') {
                        $query = Document::where('id', $d->type_id)->first();
                        $d['type_data'] = $query;
                    } else if ($d->type == 'user') {
                        $query = User::where('id', $d->type_id)->first();
                        $d['type_data'] = $query;
                    } else {
                        $query = Tag::where('id', $d->type_id)->first();
                        $d['type_data'] = $query;
                    };
                    $d->user;
                    return $d;
                });
            }

            return response()->json($log, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
}
