<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class WalletException extends Exception
{
    public function __construct(
        string $message,
        int $code = Response::HTTP_UNPROCESSABLE_ENTITY
    ) {
        parent::__construct($message, $code);
    }
}
