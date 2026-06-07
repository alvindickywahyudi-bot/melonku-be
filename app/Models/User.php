<?php

namespace App\Models;

use Laravel\Passport\Token;

use Laravel\Passport\HasApiTokens;

use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | 🔐 MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    protected $fillable = [

        'username',

        'email',

        'phone',

        'password',

        'google_id',

        'is_active',

        'phone_verified_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔒 HIDDEN
    |--------------------------------------------------------------------------
    */
    protected $hidden = [

        'password',

        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔄 CASTS
    |--------------------------------------------------------------------------
    */
    protected $casts = [

        'phone_verified_at' => 'datetime',

        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔑 PASSPORT LOGIN
    |--------------------------------------------------------------------------
    */
    public function findForPassport($username)
    {
        return $this

            ->where('email', $username)

            ->orWhere('phone', $username)

            ->orWhere('username', $username)

            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | 🔐 TOKENS
    |--------------------------------------------------------------------------
    */
    public function tokens()
    {
        return $this->hasMany(

            Token::class,

            'user_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🎭 ROLES
    |--------------------------------------------------------------------------
    */
    public function roles()
    {
        return $this->belongsToMany(

            Role::class,

            'user_role',

            'user_id',

            'role_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 PROFILE
    |--------------------------------------------------------------------------
    */
    public function profile()
    {
        return $this->hasOne(

            UserProfile::class,

            'user_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 📦 ORDERS
    |--------------------------------------------------------------------------
    */
    public function orders()
    {
        return $this->hasMany(

            Order::class,

            'user_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🌱 ADOPTION TRANSACTIONS
    |--------------------------------------------------------------------------
    */
    public function adoptionTransactions()
    {
        return $this->hasMany(

            AdoptionTransaction::class,

            'user_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 HAS ROLE
    |--------------------------------------------------------------------------
    */
    public function hasRole($role)
    {
        return $this->roles()

            ->where('nama', $role)

            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 HAS MULTIPLE ROLES
    |--------------------------------------------------------------------------
    */
    public function hasRoles($roles = [])
    {
        if (!is_array($roles)) {

            $roles = [$roles];
        }

        return $this->roles()

            ->whereIn('nama', $roles)

            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | 👑 IS ADMIN
    |--------------------------------------------------------------------------
    */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 IS CUSTOMER
    |--------------------------------------------------------------------------
    */
    public function isCustomer()
    {
        return $this->hasRole('customer');
    }

    /*
    |--------------------------------------------------------------------------
    | 🚚 IS KURIR
    |--------------------------------------------------------------------------
    */
    public function isKurir()
    {
        return $this->hasRole('kurir');
    }

    /*
    |--------------------------------------------------------------------------
    | ⭐ REVIEWS
    |--------------------------------------------------------------------------
    */
    public function reviews()
    {
        return $this->hasMany(
            ProductReview::class
        );
    }
}