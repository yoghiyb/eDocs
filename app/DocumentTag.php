<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentTag extends Model
{
    protected $table = 'documents_tags';

    protected $fillable = [
        'document_id', 'tag_id'
    ];

    public function document()
    {
        return $this->belongsTo('App\Document');
    }

    public function tag()
    {
        return $this->belongsTo('App\Tag');
    }
}
