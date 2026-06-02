<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BusinessService extends Pivot
{
    protected $guarded =['id'];

    protected $casts = [
        'settings' => 'array',
    ];
}
