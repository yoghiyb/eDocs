<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            // $user['photo'] = Storage::url('app/public/images/' . $user->photo);
            return response()->json($user);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'User tidak ditemukan']);
        }
    }

    public function userPhoto($id)
    {
        try {
            $user = User::findOrfail($id);

            return response()->json($user['photo'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'gagal ambil foto']);
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

            $user = $request->except('_method', 'created_at', 'updated_at', 'email_verified_at');

            $oldUser = User::findOrFail($id);

            if ($request->photo == '') {
                $user = $request->except('_method', 'photo', 'created_at', 'updated_at', 'email_verified_at');
            }

            if ($request->hasFile('photo')) {

                if (Storage::disk('public')->exists('images/' . $oldUser->photo)) {
                    Storage::disk('public')->delete('images/' . $oldUser->photo);
                }

                $photo = $request->file('photo')->store('public/images');

                $user['photo'] = explode('/', $photo)[2];
            }

            User::where('id', $id)->update($user);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'profile gagal diperbarui ' . $th->getMessage()]);
        }
        return response()->json(['success' => 'profile diperbarui'], 200);
    }



    public function passwordReset(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'old' => 'required_unless:role,1,min:8',
            'new' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $newPass = ['password' => Hash::make($request->new)];
            if ($request->role == 1) {
                User::where('id', $id)->update($newPass);
                return response()->json(['success' => 'password berhasil diubah'], 200);
            } else {
                if (Hash::check($request->old, auth()->user()->password)) {

                    User::where('id', $id)->update($newPass);
                    return response()->json(['success' => 'pasword berhasil diubah'], 200);
                };
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|integer',
            'dept_id' => 'nullable|integer',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages());
        }

        try {
            $user = $request->all();
            $user['photo'] = 'profile.png';
            $user['password'] = Hash::make($request->password);
            $user['api_token'] = Str::random(60);

            User::create($user);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }
        return response()->json(['success' => $user], 200);
    }

    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
        return response()->json($user, 200);
    }

    public function destroy($id)
    {
        try {
            User::where('id', $id)->delete();
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }

        return response()->json(['success' => 'user berhasil dhapus']);
    }
}
