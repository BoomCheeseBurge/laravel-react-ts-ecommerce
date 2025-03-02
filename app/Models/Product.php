<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100);

        $this->addMediaConversion('small')
            ->width(480);

        $this->addMediaConversion('large')
            ->width(1200);
    }

    // ---------------------------------------------------------------------
    /**
     * 
     *   _______ _______  _____   _____  _______
     *   |______ |       |     | |_____] |______
     *   ______| |_____  |_____| |       |______
     *
     */

    /**
     * Scope a query to only include products belonging to the vendor user.
     */
    public function scopeBelongsToVendor(Builder $query): Builder
    {
        return $query->where('created_by', auth()->user()->id);
    }

    /**
     * Scope a query to only include published products.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProductStatusEnum::Published);
    }

    /**
     * Scope a query to only determine whether the product can be displayed on the website.
     * 
     * The condition are not only being published but the vendor must be approved as well
     */
    public function scopeForWebsite(Builder $query): Builder
    {
        return $query->published();
    }

    // ----------------------------------------------------------------
    /**
     * 
     *   ______ _______        _______ _______ _____  _____  __   _
     *  |_____/ |______ |      |_____|    |      |   |     | | \  |
     *  |    \_ |______ |_____ |     |    |    __|__ |_____| |  \_|
     *
     */

    /**
     * Get the department that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the category that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all of the variationTypes for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function variationTypes(): HasMany
    {
        return $this->hasMany(VariationType::class);
    }

    /**
     * Get all of the options for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function options(): HasManyThrough
    {
        return $this->hasManyThrough(VariationTypeOption::class, // Target model 
                                    VariationType::class,        // Intermediate model
                                    'product_id',               // Foreign key on VariationType table
                                    'variation_type_id',       // Foreign key on Option table
                                    'id',                       // Local key on Product table
                                    'id'                  // Local key on VariationType table
                                    );
    }

    /**
     * Get all of the variations for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    /**
     * Get the user that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // --------------------------------------------------------------------
    /**
     * 
     *   _     _ _______         _____  _______  ______
     *   |_____| |______ |      |_____] |______ |_____/
     *   |     | |______ |_____ |       |______ |    \_
     *
     */

     /**
      * Directly returning the price of the matching variation if found, or the product's base price otherwise
      *
      * @param mixed $optionIds
      */
     public function getPriceForOptions($optionIds = [])
     {
         // Create an array of only the values from the associative array
         $optionIds = array_values($optionIds);
         
        // Sort the option IDs for easier checking below
        sort($optionIds);

        foreach($this->variations as $variation) {

            $varTypeOptIds = $variation->variation_type_option_ids;

            // Sort the option IDs for easier comparison
            sort($varTypeOptIds);

            // Find the associated variation type option IDs
            if ($optionIds == $varTypeOptIds) {
                /**
                 * Return the price of that variation type option (if not null)
                 * else, return general product price
                 * 
                 * Note: price of variation type option can be zero if the product is given for free
                 */
                return $variation->price !== null ? $variation->price : $this->price;
            }
        }

        // Else, return the general price of the product
        return $this->price;
     }

     /**
      * Retrieves the first available "small" conversion image URL from a set of provided variation option IDs, or returns the product's base image otherwise.
      *
      * @param array $optionIds
      * @return string
      */
     public function getImageForOptions(array $optionIds = null): string
     {
        if ($optionIds) {

            // Get the option IDs only into an array
            $optionIds = array_values($optionIds);

            // Sort the option IDs for easier checking
            sort($optionIds);

            // Get the variation type options based on the sorted IDs
            $options = VariationTypeOption::whereIn('id', $optionIds)->get();

            // Get the images of every option
            foreach ($options as $option) {

                $image = $option->getFirstMediaUrl('images', 'small');

                // Return the first image of the option if exists
                if ($image) {
                    return $image;
                }
            }
        }

        // Else, product has no variation option, return product image
        return $this->getFirstMediaUrl('images', 'small');
     }

     /**
      * Get the image URL for the first matched product variation, or the product's base image otherwise
      *
      * @param string $collectionName
      * @param string $conversion
      * @return string
      */
     public function getFirstImageUrl(string $collectionName = 'images', string $conversion = 'small'): string
     {
        if ($this->options->count() > 0) {
            
            // Loop through the product variation type options
            foreach ($this->options as $option) {
                
                // Get the corresponding image of the option
                $imageUrl = $option->getFirstMediaUrl($collectionName, $conversion);

                // Return the first image that is stored
                if ($imageUrl) {
                    return $imageUrl;
                }
            }
        }
        
        // Else, return the image of the product in general
        return $this->getFirstMediaUrl($collectionName, $conversion);
     }

     /**
      * Get the price of the first product option matched, or the product's base price
      *
      * @return float
      */
     public function getPriceForFirstOption(): float
     {  
        // Get the first option, or null
        $firstOptions = $this->getFirstOptionsMap();

        // Check if first options is not empty
        if ($firstOptions) {

            // Return the price of that first option
            return $this->getPriceForOptions($firstOptions);
        }

        // Else, return the price of the product in general
        return $this->price;
     }

     /**
      * Return an associative array of variation type ID as key and its 'first type option' as value
      *
      * @return array
      */
     public function getFirstOptionsMap(): array
     {
        return $this->variationTypes
                    ->mapWithKeys(fn($type) => [$type->id => $type->options[0]?->id])
                    ->toArray();
     }

     /**
      * Return the images of either the variation option or the product in general
      *
      * @return \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection
      */
     public function getImages(): MediaCollection
     {
        // Check if this product has any variation options
        if ($this->options->count() > 0) {
            
            // Loop through the options
            foreach ($this->options as $option) {

                // Get the images associated with those options
                $images = $option->getMedia('images');

                // If images not empty, return those
                if ($images) {

                    return $images;
                }
            }
        }

        // Else, return the images of the product in general
        return $this->getMedia('images');
     }
}
