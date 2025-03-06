<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VariationTypeOption extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $timestamps = false;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100);

        $this->addMediaConversion('small')
            ->width(480);

        $this->addMediaConversion('large')
            ->width(1200);
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
      * Get the variationType that owns the VariationTypeOption
      *
      * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
      */
     public function variationType(): BelongsTo
     {
         return $this->belongsTo(VariationType::class);
     }

    /**
     * Get all of the variation type option's images
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')->where('collection_name', 'images');
    }
}
