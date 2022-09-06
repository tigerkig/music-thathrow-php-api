<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Beat */
class CartItemResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'bpm' => $this->bpm,
//            'is_free' => $this->is_free,
//            'is_exclusive' => $this->is_exclusive,
//            'download_enabled' => $this->download_enabled,
//            'purchase_enabled' => $this->purchase_enabled,
//            'status' => $this->status,
//            'deleted_at' => $this->deleted_at,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
//            'genres_count' => $this->genres_count,
//            'parts_count' => $this->parts_count,

            'user_id' => $this->user_id,
            'creator' => new ProducerResource($this->whenLoaded('creator')),
            'artwork' => new UploadResource($this->whenLoaded('artwork')),
//            'download' => new UploadResource($this->whenLoaded('download')),
            'preview' => new UploadResource($this->whenLoaded('preview')),
        ];
    }
}
