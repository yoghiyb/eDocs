<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'parent_id', 'from_id', 'to_id', 'comment_owner', 'comment'
    ];

    public function from_user()
    {
        return $this->belongsTo('App\User', 'from_id', 'id');
    }

    public function to_user()
    {
        return $this->belongsTo('App\User', 'to_id', 'id');
    }
}
