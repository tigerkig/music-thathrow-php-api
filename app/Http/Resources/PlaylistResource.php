<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Playlist */
class PlaylistResource extends JsonResource
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
            'date' => $this->created_at->format('Y-m-d'),
            'creator' => $this->whenLoaded('creator', new ProducerResource($this->creator)),
            'image' => new OtherUploadResource($this->image),
            'beats' => BeatResource::collection($this->whenLoaded('beats')),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
        ];
    }
}
