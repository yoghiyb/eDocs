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
                    $d->{$d->type};
                    if ($d->type == 'comment' && $d->{$d->type} != null) {
                        $d->{$d->type}->to_user;
                        $d->{$d->type}->from_user;

                        $id = explode('_', $d->{$d->type}->comment_owner);
                        if ($id[0] == 'doc') {
                            $d->{$d->type}['document'] = Document::where('id', $id[1])->first();
                        } else {
                            $d->{$d->type}['user'] = User::where('id', $id[1])->first();
                        }
                    }
                    $d->user_data;
                    return $d;
                });
            }

            return response()->json($log, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
}
