<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helper\DataViewer;

class Tag extends Model
{
    use DataViewer;

    public static $columns = [
        'name', 'created_by'
    ];

    protected $fillable = [
        'name', 'created_by'
    ];

    public function documents_tags()
    {
        return $this->hasMany('App\DocumentTag');
    }

    public function log()
    {
        return $this->hasMany('App\Log', 'id', 'type_id');
    }

    // public function documents_tags()
    // {
    //     return $this->belongsTo('App\DocumentTag', 'id', 'tag_id');
    // }
}
