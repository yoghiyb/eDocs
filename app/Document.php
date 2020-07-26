<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helper\DataViewer;

class Document extends Model
{
    use DataViewer;

    public static $columns = [
        'name', 'status', 'approved_at', 'created_at'
    ];

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

    public function approved_by_user()
    {
        return $this->belongsTo('App\User', 'approved_by', 'id');
    }
}
