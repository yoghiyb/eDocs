<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'name', 'file', 'created_by', 'status', 'description'
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }

    public function documents_tags()
    {
        return $this->hasMany('App\DocumentTag');
    }
}
