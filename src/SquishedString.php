<?php

namespace RyanWhitman\PhpValues;

use RyanWhitman\PhpValues\Concerns\Stringable;

class SquishedString extends Value
{
    use Stringable;

    protected function transform(string $string): string
    {
        return $this->squish($string);
    }

    protected function validate(string $string): bool
    {
        return true;
    }

    private function squish(string $string): string
    {
        return preg_replace(
            '~(\s|\x{3164})+~u',
            ' ',
            preg_replace('~^[\s﻿]+|[\s﻿]+$~u', '', $string)
        );
    }
}
