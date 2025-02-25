<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

     public function getPriceForOptions($optionIds = [])
     {
        // Retrieve only the values from the associative array
        $optionIds = array_values($optionIds);

        // Sort the option IDs for easier checking below
        sort($optionIds);

        foreach($this->variations as $variation) {

            $varTypeOptIds = $variation->variation_type_option_ids;
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
}
