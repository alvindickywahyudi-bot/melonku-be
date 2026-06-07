<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Role;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;

use Google_Client;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📱 REGISTER CUSTOMER
    |--------------------------------------------------------------------------
    */
    public function register(Request $request)
    {
        $data = $request->validate([

            'phone' => [
                'required',
                'regex:/^08[0-9]{8,11}$/',
                'unique:users,phone'
            ],

            'password' => [
                'required',
                'min:6'
            ]
        ]);

        try {

            $user = DB::transaction(function () use ($data) {

                $user = User::create([

                    'username' => 'user_' . Str::random(6),

                    'phone' => $data['phone'],

                    'password' => bcrypt($data['password']),

                    'is_active' => 1
                ]);

                $role = Role::where('nama', 'customer')->first();

                if ($role) {

                    $user->roles()->syncWithoutDetaching([
                        $role->id
                    ]);
                }

                return $user->load('roles');
            });

            return $this->respondWithToken(
                $user,
                'Register berhasil'
            );

        } catch (\Throwable $e) {

            Log::error($e);

            return response()->json([

                'message' => 'Register gagal',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🔐 LOGIN CUSTOMER
    |--------------------------------------------------------------------------
    */
    public function login(Request $request)
    {
        $data = $request->validate([

            'phone' => 'required|string',

            'password' => 'required'
        ]);

        try {

            $user = User::where(
                'phone',
                $data['phone']
            )->first();

            if (
                !$user ||
                !Hash::check(
                    $data['password'],
                    $user->password
                )
            ) {

                return response()->json([

                    'message' => 'Nomor HP atau password salah'

                ], 401);
            }

            if (!$user->is_active) {

                return response()->json([

                    'message' => 'Akun tidak aktif'

                ], 403);
            }

            return $this->respondWithToken(

                $user->load('roles'),

                'Login berhasil'
            );

        } catch (\Throwable $e) {

            Log::error($e);

            return response()->json([

                'message' => 'Login gagal',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🔐 LOGIN ADMIN
    |--------------------------------------------------------------------------
    */
    public function adminLogin(Request $request)
    {
        $data = $request->validate([

            'email' => 'required|email',

            'password' => 'required'
        ]);

        try {

            $user = User::where(
                'email',
                $data['email']
            )->first();

            if (
                !$user ||
                !Hash::check(
                    $data['password'],
                    $user->password
                )
            ) {

                return response()->json([

                    'message' => 'Email atau password salah'

                ], 401);
            }

            $isAdmin = $user->roles()

                ->where('nama', 'admin')

                ->exists();

            if (!$isAdmin) {

                return response()->json([

                    'message' => 'Akses admin ditolak'

                ], 403);
            }

            if (!$user->is_active) {

                return response()->json([

                    'message' => 'Akun tidak aktif'

                ], 403);
            }

            return $this->respondWithToken(

                $user->load('roles'),

                'Login admin berhasil'
            );

        } catch (\Throwable $e) {

            Log::error($e);

            return response()->json([

                'message' => 'Login admin gagal',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🔵 LOGIN GOOGLE
    |--------------------------------------------------------------------------
    */
    public function google(Request $request)
    {
        $data = $request->validate([

            'id_token' => 'required|string'
        ]);

        try {

            $googleUser = $this->verifyGoogleToken(
                $data['id_token']
            );

            if (!$googleUser) {

                return response()->json([

                    'message' => 'Token Google tidak valid'

                ], 401);
            }

            $isNewUser = false;

            $user = DB::transaction(function ()
            use (
                $googleUser,
                &$isNewUser
            ) {

                $user = User::where(

                    'google_id',

                    $googleUser['id']

                )->first();

                if (!$user) {

                    $user = $this->createUserFromGoogle(
                        $googleUser
                    );

                    $isNewUser = true;
                }

                return $user->load('roles');
            });

            return $this->respondWithToken(

                $user,

                $isNewUser
                    ? 'Register Google berhasil'
                    : 'Login Google berhasil'
            );

        } catch (\Throwable $e) {

            Log::error('GOOGLE LOGIN ERROR', [

                'message' => $e->getMessage(),

                'line' => $e->getLine(),

                'file' => $e->getFile()
            ]);

            return response()->json([

                'message' => 'Google login gagal',

                'errors' => [
                    $e->getMessage()
                ]

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🔍 VERIFY GOOGLE TOKEN
    |--------------------------------------------------------------------------
    */
    private function verifyGoogleToken(
        string $idToken
    ): ?array {

        $client = new Google_Client([

            'client_id' => config(
                'services.google.client_id'
            )
        ]);

        $payload = $client->verifyIdToken(
            $idToken
        );

        if (!$payload) {

            return null;
        }

        return [

            'id' => $payload['sub'],

            'name' => $payload['name'] ?? 'User'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 CREATE USER GOOGLE
    |--------------------------------------------------------------------------
    */
    private function createUserFromGoogle(
        array $googleUser
    ): User {

        $user = User::create([

            'username' => $this->generateUsername(
                $googleUser['name']
            ),

            'google_id' => $googleUser['id'],

            'password' => bcrypt(
                Str::random(32)
            ),

            'is_active' => 1
        ]);

        $role = Role::where(
            'nama',
            'customer'
        )->first();

        if ($role) {

            $user->roles()->syncWithoutDetaching([
                $role->id
            ]);
        }

        return $user;
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 GENERATE USERNAME
    |--------------------------------------------------------------------------
    */
    private function generateUsername(
        string $name
    ): string {

        $base = Str::slug($name);

        $username = $base;

        $i = 1;

        while (
            User::where(
                'username',
                $username
            )->exists()
        ) {

            $username = $base . $i++;
        }

        return $username;
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 ME
    |--------------------------------------------------------------------------
    */
    public function me(Request $request)
    {
        return response()->json([

            'user' => $request
                ->user()
                ->load('roles')
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🚪 LOGOUT
    |--------------------------------------------------------------------------
    */
    public function logout(Request $request)
    {
        $request->user()?->token()?->revoke();

        return response()->json([

            'message' => 'Logout berhasil'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔑 TOKEN RESPONSE
    |--------------------------------------------------------------------------
    */
    private function respondWithToken(
        User $user,
        string $message
    ) {

        $token = $user
            ->createToken('auth_token')
            ->accessToken;

        return response()->json([

            'message' => $message,

            'access_token' => $token,

            'token_type' => 'Bearer',

            'user' => [

                'id' => $user->id,

                'name' => $user->username,

                'email' => $user->email,

                'phone' => $user->phone,

                'google_id' => $user->google_id,

                'is_google_connected' =>
                    !empty($user->google_id),

                'roles' =>
                    $user->roles->pluck('nama')
            ]
        ]);
    }
}