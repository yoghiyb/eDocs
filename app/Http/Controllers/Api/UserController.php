<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Log;
use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            // ambil data user
            $user = $request->user();
            // $user['photo'] = Storage::url('app/public/images/' . $user->photo);
            return response()->json($user);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'User tidak ditemukan']);
        }
    }

    // fungsi untuk mengambil foto user
    public function userPhoto($id)
    {
        try {
            // ambil data user berdasarkan id-nya
            $user = User::findOrfail($id);

            return response()->json($user['photo'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'gagal ambil foto']);
        }
    }

    // fungsi untuk ambil data semua user
    public function allUsers()
    {
        // panggil query helper
        $model = User::searchPaginateAndOrder();

        // ambil comlumn dari model
        $columns = User::$columns;
        return response()->json([
            'model' => $model,
            'columns' => $columns
        ]);
    }

    // fungsi untuk memperbarui user
    public function update(Request $request, $id)
    {
        // validasi request
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'email' => 'required|string|email',
            'role' => 'required|integer',
            'photo' => 'nullable|image|mimes:jpg,png,jpeg|max:2000'
        ]);

        // jika tidak lolos validasi
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {

            $photo = null;
            // ambil semua request kecual '_method', 'created_at', 'updated_at', 'email_verified_at'
            $user = $request->except('_method', 'created_at', 'updated_at', 'email_verified_at');
            // ambil data user berdasarkan id-nya
            $oldUser = User::findOrFail($id);
            // jika request foto kosong
            if ($request->photo == '') {
                // ambil semua request kecuali '_method', 'photo', 'created_at', 'updated_at', 'email_verified_at'
                $user = $request->except('_method', 'photo', 'created_at', 'updated_at', 'email_verified_at');
            }
            // jika didalam request photo ada file
            if ($request->hasFile('photo')) {
                // lakukan pengecekan foto lama dan hapus jika ada
                if (Storage::disk('public')->exists('images/' . $oldUser->photo)) {
                    Storage::disk('public')->delete('images/' . $oldUser->photo);
                }
                // simpan foto baru ke direktori public
                $photo = $request->file('photo')->store('public/images');
                // ambil nama foto
                $user['photo'] = explode('/', $photo)[2];
            }
            // perbarui data user
            User::where('id', $id)->update($user);
            // ambil data user yang diperbarui
            $updateUser = User::findOrFail($id);
            // buat log untuk pembaruan data user
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'user',
                'type_id' => $oldUser->id,
                'controller' => 'UserController',
                'function' => 'update',
                'action' => 'update',
                'before' => json_encode($oldUser),
                'after' => json_encode($updateUser)
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'profile gagal diperbarui ' . $th->getMessage()]);
        }
        return response()->json(['success' => 'profile diperbarui'], 200);
    }

    // fungsi untuk reset pasword
    public function passwordReset(Request $request, $id)
    {
        // validasi request
        $validator = Validator::make($request->all(), [
            'old' => 'required_unless:role,1,min:8',
            'new' => 'required|min:8'
        ]);

        // jika tidak lolos validasi
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            // simpan password baru kedalam variabel newPass
            $newPass = ['password' => Hash::make($request->new)];
            // jika yang melakukan reset password adalah admin
            if ($request->role == 1) {
                // lakukan pembaruan password
                User::where('id', $id)->update($newPass);
                return response()->json(['success' => 'password berhasil diubah'], 200);
            } else {
                // jika yang melakukan reset password bukan admin maka lakukan pengecekan passwod lama
                if (Hash::check($request->old, auth()->user()->password)) {
                    // lakukan pembaruan password
                    User::where('id', $id)->update($newPass);
                    return response()->json(['success' => 'pasword berhasil diubah'], 200);
                };
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }

    // fungsi untuk membuat user baru
    public function store(Request $request)
    {
        // validasi request
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|integer',
            'dept_id' => 'nullable|integer',
            'password' => 'required|string|min:8'
        ]);
        // jika tidak lolos validasi
        if ($validator->fails()) {
            return response()->json($validator->messages());
        }

        try {
            // simpan semua request
            $user = $request->all();
            // standar foto profile
            $user['photo'] = 'profile.png';
            // hash password
            $user['password'] = Hash::make($request->password);
            // buat api_token
            $user['api_token'] = Str::random(60);
            // buat user baru
            $newUser = User::create($user);
            // buat log untuk pembuatan user baru
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'user',
                'type_id' => $newUser->id,
                'controller' => 'UserController',
                'function' => 'store',
                'action' => 'create',
                'before' => null,
                'after' => json_encode($newUser)
            ]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }
        return response()->json(['success' => $user], 200);
    }

    // fungsi untuk memanggil data user berdasarkan id-nya
    public function edit($id)
    {
        try {
            // ambil data user berdasarkan id-nya
            $user = User::findOrFail($id);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
        return response()->json($user, 200);
    }

    // fungsi untuk menghapus user
    public function destroy($id)
    {
        try {
            // ambil data user untuk log
            $oldUser = User::findOrFail($id);
            // hapus data user
            User::where('id', $id)->delete();
            // buat log untuk penghapusan data user
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'user',
                'type_id' => $oldUser->id,
                'controller' => 'UserController',
                'function' => 'destroy',
                'action' => 'delete',
                'before' => $oldUser,
                'after' => null
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }

        return response()->json(['success' => 'user berhasil dhapus']);
    }
}
