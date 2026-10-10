<?php

namespace App\Models;

use Filament\Panel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use SoftDeletes,HasRoles;
    protected $guarded = ['id'];

    public function getNameAttribute(): string
    {
        $full = trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
        return $full !== ''
            ? $full
            : ($this->mobile ?? $this->email ?? 'ادمین');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin';
    }

}
