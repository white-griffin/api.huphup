<?php

namespace App\Models;

use App\Support\SlugService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    protected $guarded = ['id'];


    protected $appends = ['image_url'];


    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    protected static function booted(): void
    {
        static::saving(function ($brand) {

            if (!$brand->slug && $brand->name) {
                $brand->slug = app(SlugService::class)
                    ->generate($brand);
            }

        });
    }

    public function getImageUrlAttribute()
    {
        return $this->image
            ? Storage::disk('public')->url($this->image)
            : null;
    }
}
