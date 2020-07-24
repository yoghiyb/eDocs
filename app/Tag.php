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
}
