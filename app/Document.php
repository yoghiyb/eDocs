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

    public static $columns_documents = [
        'name', 'created_by', 'description', 'approved_by', 'approved_at', 'created_at', 'tag'
    ];

    protected $fillable = [
        'name', 'file', 'created_by', 'status', 'access_role', 'access_dept', 'description', 'approved_by', 'approved_at'
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

    public function log()
    {
        return $this->hasMany('App\Log', 'id', 'type_id');
    }

    // public function comment()
    // {
    //     return $this->hasMany('App\Comment');
    // }
}
