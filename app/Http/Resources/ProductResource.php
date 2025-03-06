<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Remove the 'data' wrapper
     */
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the variation type options if available
        $options = $request->input('options') ?: [];

        // If options exist, return their images
        if ($options) {
            $images = $this->getImagesForOptions($options);

        // Else, return base product images 
        } else {
            $images = $this->getImages();
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'images' => $images->map(function ($image) { // This is product images

                // Return the following specific properties from each image media instance
                return [
                    'id' => $image->id,
                    'thumb' => $image->getUrl('thumb'),
                    'small' => $image->getUrl('small'),
                    'large' => $image->getUrl('large'),
                ];
            }),
            'vendor' => [
                'store_name' => $this->vendor->store_name ?? $this->user->name,
            ],
            'department' => [
                'id' => $this->department->id,
                'name' => $this->department->name,
                'slug' => $this->department->slug,
            ],
            'variationTypes' => $this->loadMissing(['variationTypes', 'variationTypes.options'])->variationTypes->map(function ($variationType) {

                return [
                    'id' => $variationType->id,
                    'name' => $variationType->name,
                    'type' => $variationType->type,
                    'options' => $variationType->loadMissing('options.media')->options->map(function ($option) {

                        return [
                            'id' => $option->id,
                            'name' => $option->name,
                            'images' => $option->getMedia('images')->map(function ($image) { // This is variation type option images for each combination (if the type is image)

                                return [
                                    'id' => $image->id,
                                    'thumb' => $image->getUrl('thumb'),
                                    'small' => $image->getUrl('small'),
                                    'large' => $image->getUrl('large'),
                                ];
                            })
                        ];
                    }),
                ];
            }),
            'variations' => $this->variations->map(function ($variation) { // Every combination of variation type option has an associated quantity and price

                return [
                    'id' => $variation->id,
                    'variation_type_option_ids' => $variation->variation_type_option_ids,
                    'quantity' => $variation->quantity,
                    'price' => $variation->price,
                ];
            }),
        ];
    }
}
