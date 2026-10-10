<?php

namespace App\Models;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use SoftDeletes,HasRoles,HasPageShield;
    protected $guarded = ['id'];

    public function getNameAttribute(): string
    {
        $full = trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
        return $full !== ''
            ? $full
            : ($this->mobile ?? $this->email ?? 'ادمین');
    }


}
