<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'price' => $this->getPriceForFirstOption(),
            'quantity' => $this->quantity,
            // 'image' => $this->getFirstMediaUrl('images', 'small'),
            'image' => $this->getFirstImageUrl(),
            'vendor' => [
                'store_name' => $this->vendor->store_name,
            ],
            'department' => [
                'id' => $this->department->id,
                'name' => $this->department->name,
                'slug' => $this->department->slug,
            ],
        ];
    }
}
