<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeatPlaylist extends Model
{
    protected $table = 'beat_playlist';

    protected $fillable = [
        'beat_id',
        'playlist_id'
    ];
}
