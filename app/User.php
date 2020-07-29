<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Helper\DataViewer;

class User extends Authenticatable
{
    use Notifiable, DataViewer;

    public static $columns = [
        'role', 'dept_id', 'username', 'email'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'role', 'dept_id', 'photo', 'api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function documents()
    {
        return $this->hasMany('App\Document', 'id', 'created_by');
    }

    public function approved_by_user()
    {
        return $this->hasOne('App\Document', 'id', 'approved_by');
    }

    public function comment()
    {
        return $this->hasMany('App\Comment',  'id', ['from_id', 'to_id']);
    }
}
