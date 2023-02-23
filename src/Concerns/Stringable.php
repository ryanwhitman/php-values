<?php

namespace RyanWhitman\PhpValues\Concerns;

trait Stringable
{
    public function __toString(): string
    {
        return (string) $this->get();
    }
}
