<?php

namespace Seungmun\Sens\Exceptions;

use Exception;

class SensException extends Exception
{
    /**
     * Exception for Invalid NCLOUD SENS Tokens.
     */
    public static function InvalidNCPTokens(string $message): self
    {
        return new static($message);
    }
}
