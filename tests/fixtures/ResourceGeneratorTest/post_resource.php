<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    //TODO implement custom serialization logic or remove method redefining
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}