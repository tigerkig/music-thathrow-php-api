<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Beat */
class BeatResource extends JsonResource
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
            'price' => $this->price / 100,
            'bpm' => $this->bpm,
            'is_free' => $this->is_free,
            'is_exclusive' => $this->is_exclusive,
//            'download_enabled' => $this->download_enabled,
//            'purchase_enabled' => $this->purchase_enabled,
//            'user_id' => $this->user_id,
            'status' => $this->status,
//            'deleted_at' => $this->deleted_at,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
//            'genres_count' => $this->genres_count,
//            'parts_count' => $this->parts_count,
            'purchases_count' => $this->purchases_count,

            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'parts' => PartResource::collection($this->whenLoaded('parts')),
            'artwork' => new UploadResource($this->whenLoaded('artwork')),
            'preview' => new UploadResource($this->whenLoaded('preview')),
            'download' => new UploadResource($this->whenAppended('download')),
            'creator' => new ProducerResource($this->creator)
        ];
    }
}
