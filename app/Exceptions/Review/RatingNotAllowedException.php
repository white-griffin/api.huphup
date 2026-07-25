<?php

namespace App\Exceptions\Review;

use Exception;

class RatingNotAllowedException extends Exception
{
    public function __construct()
    {
        parent::__construct('عدم دسترسی برای ثبت نظر');
    }
}
