<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\Address;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'date_of_birth',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

     /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    // protected $appends = ['role'];

    //  /**
    //  * Sets the extended attribute value with first index role value to role
    //  */
    // public function getRoleAttribute()
    // {
    //     return $this->getRoleNames()[0];
    // }

    /**
     * one to many relationship with Loans
     *
     * @return HasMany
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'user_id', 'id');
    }

    /**
     * one to one relationship with Address
     *
     * @return HasOne
     */
    public function address(): HasOne
    {
        return $this->hasOne(Address::class, 'user_id', 'id');
    }

}
