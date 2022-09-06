<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class ProducerResource extends JsonResource
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'beats_count' => $this->beats_count,

            'beats' => BeatResource::collection($this->whenLoaded('beats')),
            'profile_picture' => $this->when($this->profileImage !== null, new OtherUploadResource($this->profileImage), null)
        ];
    }
}
