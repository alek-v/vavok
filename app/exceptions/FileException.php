<?php

namespace App\Exceptions;

use Exception;

class FileException extends Exception {
    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}