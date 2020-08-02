<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'user_id', 'type', 'type_id', 'controller', 'function', 'action', 'before', 'after'
    ];

    // protected $hidden = [
    //     'before', 'after'
    // ];

    public function user_data()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'type_id', 'id');
    }

    public function file()
    {
        return $this->belongsTo('App\Document', 'type_id', 'id');
    }

    public function tag()
    {
        return $this->belongsTo('App\Tag', 'type_id', 'id');
    }

    public function comment()
    {
        return $this->belongsTo('App\Comment', 'type_id', 'id');
    }
}
