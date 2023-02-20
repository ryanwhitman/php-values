<?php

namespace RyanWhitman\PhpValues;

class Email extends Value
{
    protected function transform(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    protected function validate(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
