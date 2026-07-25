<?php

namespace App\Exceptions\Review;

use Exception;

class AlreadyReviewedException extends Exception
{
    public function __construct()
    {
        parent::__construct('نظر شما قبلا برای این محصول ثبت شده');
    }
}
