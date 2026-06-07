<?php

namespace App\Http\Controllers\API\V1;

use Google_Client;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | 🔵 CONNECT GOOGLE
    |--------------------------------------------------------------------------
    */
    public function connectGoogle(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string'
        ]);

        $client = new Google_Client([
            'client_id' => config('services.google.client_id')
        ]);

        $payload = $client->verifyIdToken($request->id_token);

        if (!$payload) {
            return response()->json([
                'message' => 'Token Google tidak valid'
            ], 401);
        }

        $googleId = $payload['sub'];

        if (empty($googleId)) {
            return response()->json([
                'message' => 'Google ID tidak ditemukan'
            ], 401);
        }

        // cek apakah sudah dipakai akun lain
        $alreadyUsed = User::where('google_id', $googleId)
            ->where('id', '!=', $request->user()->id)
            ->exists();

        if ($alreadyUsed) {
            return response()->json([
                'message' => 'Google sudah digunakan akun lain'
            ], 422);
        }

        $user = $request->user();

        $user->update([
            'google_id' => $googleId
        ]);

        return response()->json([
            'message' => 'Google berhasil disambungkan',
            'user' => $user->load('roles')
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 SHOW PROFILE
    |--------------------------------------------------------------------------
    */
    public function show(Request $request)
    {
        $user = $request->user()->load([

            'profile.provinsi',
            'profile.kabupaten',
            'profile.kecamatan',
            'profile.village',

            'roles',
        ]);

        return response()->json([

            'user' => [

                'id' => $user->id,

                'username' => $user->username,

                'email' => $user->email,

                /*
                |--------------------------------------------------------------------------
                | 📱 PHONE
                |--------------------------------------------------------------------------
                */
                'phone' => $user->phone,

                'is_phone_connected' => !empty($user->phone),

                'is_phone_verified' => (bool) $user->phone_verified_at,

                /*
                |--------------------------------------------------------------------------
                | 🔵 GOOGLE
                |--------------------------------------------------------------------------
                */
                'google_id' => $user->google_id,

                'is_google_connected' => !empty($user->google_id),

                /*
                |--------------------------------------------------------------------------
                | 👤 ROLES
                |--------------------------------------------------------------------------
                */
                'roles' => $user->roles->pluck('nama'),
            ],

            'profile' => $user->profile
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ✏️ UPDATE PROFILE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([

            /*
            |--------------------------------------------------------------------------
            | BASIC PROFILE
            |--------------------------------------------------------------------------
            */
            'nama' => 'required|string|max:255',

            'phone' => 'nullable|regex:/^08[0-9]{8,11}$/|unique:users,phone,' . $user->id,

            'desc' => 'nullable|string',

            'gender' => 'nullable|in:L,P,TRANSGENDER',

            'tempat_lahir' => 'nullable|string|max:255',

            'tgl_lahir' => 'nullable|date',

            /*
            |--------------------------------------------------------------------------
            | ALAMAT
            |--------------------------------------------------------------------------
            */
            'alamat_detail' => 'nullable|string|max:1000',

            'provinsi_id' => 'nullable|exists:provinsi,id',

            'kabupaten_id' => 'nullable|exists:kabupaten,id',

            'kecamatan_id' => 'nullable|exists:kecamatan,id',

            'village_id' => 'nullable|exists:villages,id',

            /*
            |--------------------------------------------------------------------------
            | MAPS
            |--------------------------------------------------------------------------
            */
            'lat' => 'nullable',

            'lng' => 'nullable',

            /*
            |--------------------------------------------------------------------------
            | FOTO
            |--------------------------------------------------------------------------
            */
            'foto' => 'nullable|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE USER
        |--------------------------------------------------------------------------
        */
        if (isset($data['phone'])) {

            $user->update([

                'phone' => $data['phone'],

                // reset verifikasi jika nomor berubah
                'phone_verified_at' => null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE PROFILE
        |--------------------------------------------------------------------------
        */
        $profile = $user->profile()->updateOrCreate(

            [
                'user_id' => $user->id
            ],

            [

                'nama' => $data['nama'] ?? null,


                /*
                |--------------------------------------------------------------------------
                | ALAMAT
                |--------------------------------------------------------------------------
                */
                'alamat_detail' => $data['alamat_detail'] ?? null,

                /*
                |--------------------------------------------------------------------------
                | WILAYAH
                |--------------------------------------------------------------------------
                */
                'provinsi_id' => $data['provinsi_id'] ?? null,

                'kabupaten_id' => $data['kabupaten_id'] ?? null,

                'kecamatan_id' => $data['kecamatan_id'] ?? null,

                'village_id' => $data['village_id'] ?? null,

                /*
                |--------------------------------------------------------------------------
                | MAPS
                |--------------------------------------------------------------------------
                */
                'lat' => $data['lat'] ?? null,

                'lng' => $data['lng'] ?? null,

                /*
                |--------------------------------------------------------------------------
                | FOTO
                |--------------------------------------------------------------------------
                */
                'foto' => $data['foto'] ?? null,
            ]
        );

        $profile->load([

            'provinsi',
            'kabupaten',
            'kecamatan',
            'village',
        ]);

        return response()->json([

            'message' => 'Profile updated',

            'user' => [

                'phone' => $user->phone,

                'google_id' => $user->google_id,
            ],

            'profile' => $profile
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 📱 CHANGE PHONE
    |--------------------------------------------------------------------------
    */
    public function changePhone(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|regex:/^08[0-9]{8,11}$/|unique:users,phone'
        ]);

        $request->user()->update([
            'phone' => $data['phone']
        ]);

        return response()->json([
            'message' => 'Nomor HP berhasil diubah'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔒 CHANGE PASSWORD
    |--------------------------------------------------------------------------
    */
    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'password' => 'required|min:6|confirmed'
        ]);

        $request->user()->update([
            'password' => bcrypt($data['password'])
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah'
        ]);
    }
}