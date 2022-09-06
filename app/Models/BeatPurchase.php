<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeatPurchase extends Pivot
{
    use SoftDeletes, HasFactory;

    protected $table = 'beat_purchase';
}
