<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Property extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title', 'slug', 'description', 'type', 'price', 'address', 'latitude', 'longitude',
        'bedrooms', 'bathrooms', 'size', 'unit', 'category_id', 'location_id', 'agent_id',
        'is_featured', 'status', 'seo_metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'seo_metadata' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PropertyCategory::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'property_features');
    }

    // Spatie Media Library setup (example for images)
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
        $this->addMediaCollection('videos');
        $this->addMediaCollection('virtual_tours');
    }
    
}