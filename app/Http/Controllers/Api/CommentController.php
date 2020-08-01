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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_id' => 'required|integer',
            'to_id' => 'required|integer',
            'comment_owner' => 'required|string',
            'comment' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->messages());
        }

        try {
            $comment = $request->all();

            $newComment = Comment::create($comment);
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

    public function getComment($comment_owner)
    {
        try {
            // $comment_owner = explode('_', $comment_owner);

            $comment = Comment::where('comment_owner', $comment_owner)->orderBy('created_at', 'desc')->get();

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

    public function reply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|integer',
            'from_id' => 'required|integer',
            'to_id' => 'required|integer',
            'comment_owner' => 'required|string',
            'comment' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->messages());
        }

        try {
            $replyComment = $request->all();

            $replied = Comment::create($replyComment);
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

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->messages());
        }

        try {
            $oldComment = Comment::findOrFail($id);
            $comment = $request->all();

            Comment::where('id', $id)->update($comment);

            $updateComment = Comment::findOrFail($id);

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

    public function destroy($id)
    {
        try {
            $oldComment = Comment::findOrFail($id);
            Comment::where('id', $id)->delete();

            $child_comment = Comment::where('parent_id', $id)->get();

            if (count($child_comment) > 0) {
                foreach ($child_comment as $comment) {
                    Comment::where('parent_id', $id)->delete();
                }
            }

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
