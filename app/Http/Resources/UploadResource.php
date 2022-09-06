<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\Upload */
class UploadResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'name' => $this->name,
            'url' => $this->when($this->public, Storage::url($this->url))
//            'deleted_at' => $this->deleted_at,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
//
//            'beat_id' => $this->beat_id,
        ];
    }
}
