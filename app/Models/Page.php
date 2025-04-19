<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Page extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['title', 'slug', 'content', 'status', 'seo_metadata'];

    protected $casts = [
        'seo_metadata' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')->single();
    }
}