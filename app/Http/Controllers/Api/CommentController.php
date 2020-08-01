<?php

namespace App\Http\Controllers\Api;

use App\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    // fungsi untuk menyimpan komen
    public function store(Request $request)
    {
        // validas request
        $validator = Validator::make($request->all(), [
            'from_id' => 'required|integer',
            'to_id' => 'required|integer',
            'comment_owner' => 'required|string',
            'comment' => 'required'
        ]);
        // jika tidak lolos validasi
        if ($validator->fails()) {
            return response()->json($validator->errors()->messages());
        }

        try {
            // ambil semua request
            $comment = $request->all();
            // buat komen
            $newComment = Comment::create($comment);
            // buat log untuk komen baru
            Log::create([
                'user_id' => Auth::id(),
                'controller' => 'CommentController',
                'function' => 'store',
                'action' => 'create',
                'before' => null,
                'after' => json_encode($newComment)
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }

        return response()->json(['success' => $comment], 200);
    }

    // ambil komen berdasarkan pemilik-nya
    public function getComment($comment_owner)
    {
        try {
            // $comment_owner = explode('_', $comment_owner);
            // ambil komen berdasarkan pemiliknya dan urutkan berdasarkan yang terbaru
            $comment = Comment::where('comment_owner', $comment_owner)->orderBy('created_at', 'desc')->get();
            // panggil relasi
            $collection = collect($comment)->map(function ($data) {
                $data->from_user;
                $data->to_user;
                return $data;
            });
            return response()->json($collection, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    // fungsi untuk membuat reply komen
    public function reply(Request $request)
    {
        // validasi request
        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|integer',
            'from_id' => 'required|integer',
            'to_id' => 'required|integer',
            'comment_owner' => 'required|string',
            'comment' => 'required'
        ]);
        // jika tidak lolos validasi
        if ($validator->fails()) {
            return response()->json($validator->errors()->messages());
        }

        try {
            // ambil semua request
            $replyComment = $request->all();
            // buat reply komen
            $replied = Comment::create($replyComment);
            // buat log untuk reply komen
            Log::create([
                'user_id' => Auth::id(),
                'controller' => 'CommentController',
                'function' => 'reply',
                'action' => 'create',
                'before' => null,
                'after' => json_encode($replied)
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }

        return response()->json(['success' => 'reply message success']);
    }

    // fungsi untuk melakukan update komen
    public function update(Request $request, $id)
    {
        // validasi request
        $validator = Validator::make($request->all(), [
            'comment' => 'required'
        ]);
        // jika tidak lolos validasi
        if ($validator->fails()) {
            return response()->json($validator->errors()->messages());
        }

        try {
            // ambil data komen berdasarkan id-nya
            $oldComment = Comment::findOrFail($id);
            // ambil semua request
            $comment = $request->all();
            // perbarui komen
            Comment::where('id', $id)->update($comment);
            // ambil data komen yg terbaru
            $updateComment = Comment::findOrFail($id);
            // buat log untuk pembaruan komen
            Log::create([
                'user_id' => Auth::id(),
                'controller' => 'CommentController',
                'function' => 'update',
                'action' => 'update',
                'before' => json_encode($oldComment),
                'after' => json_encode($updateComment)
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }

        return response()->json(['success' => 'comment diperbarui']);
    }

    // fungsi untuk hapus komen
    public function destroy($id)
    {
        try {
            // ambil data komen
            $oldComment = Comment::findOrFail($id);
            // hapus komen
            Comment::where('id', $id)->delete();
            // ambil data komentar yang terhubung
            $child_comment = Comment::where('parent_id', $id)->get();
            // lakukan pengecekan komentar yang terhubung
            if (count($child_comment) > 0) {
                // jika ada maka hapus komentar yang terhubung
                foreach ($child_comment as $comment) {
                    Comment::where('parent_id', $id)->delete();
                }
            }
            // buat log untuk hapus komentar
            Log::create([
                'user_id' => Auth::id(),
                'controller' => 'CommentController',
                'function' => 'destroy',
                'action' => 'delete',
                'before' => json_encode($oldComment, $child_comment),
                'after' => null,
            ]);

            return response()->json(['success' => 'komen dihapus'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
}
