<?php

namespace App;

use App\Model\Conversation;
use App\Model\CustomerAddress;
use App\Model\Order;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'f_name', 'l_name', 'phone', 'email', 'password', 'point'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_phone_verified' => 'integer',
        'point' => 'integer',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function getTotalPaymentAttribute()
    {
        return $this->orders->sum("order_amount");
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'user_id');
    }

    public function getNameAttribute()
    {
        return $this->f_name." ". $this->l_name;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    public function conversations()
    {
      return $this->hasMany(Conversation::class, 'user_id');
    }
}
