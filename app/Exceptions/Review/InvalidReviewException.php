<?php

namespace App\Exceptions\Review;

use Exception;

class InvalidReviewException extends Exception
{
    public function __construct()
    {
        parent::__construct('Review must contain at least a rating or a message.');
    }
}
