<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\OtherUpload */
class OtherUploadResource extends JsonResource
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
            'url' => $this->when($this->public, Storage::url($this->url), sprintf(
                'https://randomuser.me/api/portraits/%s/%d.jpg',
                'men',
                90
            ))
        ];
    }
}
