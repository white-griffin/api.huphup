<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    protected $casts =[
        'settings' => 'array',
    ];
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'business_services')
            ->using(BusinessService::class)
            ->withPivot(['price', 'duration', 'settings', 'activity_status'])
            ->withTimestamps();
    }

}
