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

    /**
     * Exception for API errors.
     */
    public static function apiError(string $message, int $statusCode): self
    {
        return new static("API Error [{$statusCode}]: {$message}");
    }

    /**
     * Exception for invalid message data.
     */
    public static function invalidMessage(string $message): self
    {
        return new static("Invalid message: {$message}");
    }

    /**
     * Exception for network errors.
     */
    public static function networkError(string $message): self
    {
        return new static("Network error: {$message}");
    }
}
