<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'category'    => $this->category,
            'description' => $this->description,
            'features'    => $this->features,
            'sort_order'  => $this->sort_order,
            'is_active'   => $this->is_active,
        ];
    }
}
