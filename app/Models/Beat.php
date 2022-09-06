<?php

namespace App\Models;

use App\Http\Resources\GenreResource;
use Gloudemans\Shoppingcart\Contracts\Buyable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

class Beat extends Model implements Buyable
{
    use HasFactory, Searchable, SoftDeletes;

    const STATUSES = [
        'DELETED' => 0,
        'INACTIVE' => 1,
        'UNPRINTED' => 2,
        'AVAILABLE' => 3,
        'PURCHASED' => 4
    ];

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_free',
        'is_exclusive',
        'download_enabled',
        'purchase_enabled',
        'bpm',
        'status',
        'user_id'
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'is_exclusive' => 'boolean',
        'download_enabled' => 'boolean',
        'purchase_enabled' => 'boolean',
    ];

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function artwork(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Upload::class)->where('type', 'ARTWORK');
    }

    public function preview(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Upload::class)->where('type', 'PREVIEW');
    }

    public function original(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Upload::class)->where('type', 'ORIGINAL');
    }

    public function download(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Upload::class)->where('type', 'DOWNLOAD');
    }

    public function purchases()
    {
        return $this
            ->belongsToMany(Purchase::class)
            ->withTimestamps()
            ->withPivot(['deleted_at'])
            ->using(BeatPurchase::class);
    }

    public function playlists()
    {
        return $this
            ->belongsToMany(Playlist::class)
            ->withTimestamps();
    }

    public function beatPurchase()
    {
        return $this
            ->belongsToMany(BeatPurchase::class);
    }

    public function toSearchableArray(): array
    {
        $creator = $this->creator;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'price' => $this->price,
            'bpm' => $this->bpm,
            'uploader' => [
                'id' => $creator->id,
                'name' => $creator->name,
            ],
            'genres' => GenreResource::collection($this->genres),
            'is_free' => $this->is_free,
            'is_exclusive' => $this->is_exclusive,
            'created_at' => $this->created_at,
            'artwork_url' => Storage::url($this->artwork->url),
            'preview_url' => Storage::url($this->preview->url),
        ];
    }

    public function shouldBeSearchable()
    {
        return $this->status === Beat::STATUSES['AVAILABLE'];
    }

    public function getBuyableIdentifier($options = null)
    {
        return $this->id;
    }

    public function getBuyableDescription($options = null)
    {
        return $this->description;
    }

    public function getBuyablePrice($options = null)
    {
        return $this->price;

    }

    public function getBuyableWeight($options = null)
    {
        return 0;
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUSES['AVAILABLE'];
    }
}
