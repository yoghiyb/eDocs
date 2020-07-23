<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $user['photo'] = Storage::url('app/public/images/' . $user->photo);
            return response()->json($user);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'User tidak ditemukan']);
        }
    }

    public function allUsers()
    {
        $model = User::searchPaginateAndOrder();

        $columns = User::$columns;
        return response()->json([
            'model' => $model,
            'columns' => $columns
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'email' => 'required|string|email',
            'role' => 'required|integer',
            'photo' => 'nullable|image|mimes:jpg,png,jpeg|max:2000'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {

            $photo = null;

            $user = $request->except('_method');

            $oldUser = User::findOrFail($id);

            if ($request->hasFile('photo')) {

                if (Storage::disk('public')->exists('images/' . $oldUser->photo)) {
                    Storage::disk('public')->delete('images/' . $oldUser->photo);
                }

                $photo = $request->file('photo')->store('public/images');

                $user['photo'] = explode('/', $photo)[2];
            }

            User::where('id', $id)->update($user);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'profile gagal diperbarui' . $th]);
        }
        return response()->json(['success' => 'profile diperbarui'], 200);
    }



    public function passwordReset(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'old' => 'required|min:8',
            'new' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            if (Hash::check($request->old, auth()->user()->password)) {
                $newPass = ['password' => Hash::make($request->new)];
                User::where('id', $id)->update($newPass);
                return response()->json(['success' => 'pasword berhasil diubah'], 200);
            };
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }
}
