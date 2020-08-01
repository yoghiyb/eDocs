<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'user_id', 'type', 'type_id', 'controller', 'function', 'action', 'before', 'after'
    ];

    protected $hidden = [
        'before', 'after'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    // public function type_data(){
    //     if($this->)
    // }
}
