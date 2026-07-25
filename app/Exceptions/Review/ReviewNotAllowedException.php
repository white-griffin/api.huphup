<?php

namespace App\Exceptions\Review;

use Exception;

class ReviewNotAllowedException extends Exception
{
    public function __construct()
    {
        parent::__construct('ثبت امتیاز مجاز نیست');
    }
}
