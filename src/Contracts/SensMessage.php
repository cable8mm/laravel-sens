<?php

namespace Seungmun\Sens\Contracts;

interface SensMessage
{
    /**
     * Serialize to Array.
     */
    public function toArray(): array;
}
