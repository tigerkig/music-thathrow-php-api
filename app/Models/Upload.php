<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'beat_id',
        'type',
        'file_type',
        'file_size',
        'size',
        'name',
        'url',
        'public'
    ];

    public function beat()
    {
        return $this->belongsTo(Beat::class);
    }
}
