<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'beats' => BeatResource::collection($this->whenLoaded('beats')),
            'profile_picture' => $this->when($this->profileImage !== null, new OtherUploadResource($this->profileImage), null)
        ];
    }
}
