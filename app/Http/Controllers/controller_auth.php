<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_auth;
use App\Models\model_customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\resource_login;
use App\Http\Resources\resource_register;
use App\Models\model_karyawan;
use Illuminate\Support\Facades\DB;
use App\Services\service_auth;

class controller_auth extends Controller
{
    public function register(request_auth $request)
    {
        $request->setIsRegistration(true);

        $registerData = $request->validated();
        $registerData['Password'] = Hash::make($registerData['Password']);

        $user = model_customer::create($registerData);

        return new resource_register($user);
    }

    public function register_karyawan(Request $request)
    {
        $request->validate([
            'Role_Id' => 'required',
            'Password' => 'required',
            'Nama' => 'required|string|max:255'
        ]);

        $registerData = $request->all();
        $registerData['Password'] = Hash::make($registerData['Password']);

        $user = model_karyawan::create($registerData);

        return response()->json([
            'message' => 'Karyawan berhasil didaftarkan',
            'data' => $user
        ]);
    }


    public function login(Request $request, service_auth $service_auth)
    {
        if ($service_auth->isCustomer($request)) {
            return $service_auth->login_customer($request);
        } else {
            return $service_auth->login_karyawan($request);
        }
    }


    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            "message" => "logged out"
        ]);
    }

    public function coba()
    {
        return response()->json([
            "message" => "coba"
        ]);
    }

    public function updatePassword()
    {
        try {
            $karyawan = model_karyawan::findOrFail($id);

            $validated = Validator::make($request, [
                'current_password' => 'required',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if (!Hash::check($request->current_password, $karyawan->password)) {
                return response()->json(['error' => 'Current password is incorrect'], 401);
            }
    
            // Update the user's password
            $karyawan->password = Hash::make($request->new_password);
            $karyawan->save();

            $karyawan->update($validated);
            return new resource_karyawan($karyawan);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'karyawan dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }
}
