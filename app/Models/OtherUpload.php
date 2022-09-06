<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherUpload extends Model
{
    protected $fillable = [
        'type',
        'file_type',
        'file_size',
        'size',
        'name',
        'url',
        'public'
    ];

    /**
     * Get all of the models that own comments.
     */
    public function morphable()
    {
        return $this->morphTo();
    }
}
