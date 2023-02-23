<?php

namespace RyanWhitman\PhpValues;

use RyanWhitman\PhpValues\Concerns\Stringable;

class TrimmedString extends Value
{
    use Stringable;

    protected function transform(string $string): string
    {
        return trim($string);
    }

    protected function validate(string $string): bool
    {
        return true;
    }
}
