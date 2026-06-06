<?php


use App\Models\Business;

if (!function_exists('business')) {
    function business(): ?Business
    {
        return app()->bound('business')
            ? app('business')
            : null;
    }
}
