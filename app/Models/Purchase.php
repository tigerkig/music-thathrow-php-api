<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes, HasFactory;

    const STATUSES = [
        'AWAITING_PAYMENT' => 0,
        'FAILED_PAYMENT' => 1,
        'COMPLETED_PAYMENT' => 2,
        'CANCELLED' => 3,
    ];

    protected $fillable = [
        'user_id',
        'paypal_id',
        'total',
        'completed_at',
        'status'
    ];

    public function beats()
    {
        return $this
            ->belongsToMany(Beat::class)
            ->withTimestamps()
            ->withPivot(['deleted_at'])
            ->using(BeatPurchase::class);
    }

    public function beatPurchase()
    {
        return $this
            ->hasMany(BeatPurchase::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class);
    }
}
