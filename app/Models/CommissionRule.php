<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'min_rating' => 'decimal:2',
        'max_rating' => 'decimal:2',
        'commission_rate' => 'decimal:2',
    ];

}
