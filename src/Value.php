<?php

namespace RyanWhitman\PhpValues;

use RyanWhitman\PhpValues\Exceptions\Exception;
use RyanWhitman\PhpValues\Exceptions\InvalidValueException;
use TypeError;

abstract class Value
{
    private $origValue;

    private $transformedValue;

    public function __construct($value)
    {
        if ($value instanceof static) {
            $this->origValue = $value->getOrigValue();
            $this->transformedValue = $value->get();
        } else {
            try {
                $transformedValue = method_exists($this, 'transform') ?
                    $this->transform($value) :
                    $value;

                if (! method_exists($this, 'validate')) {
                    throw new Exception('A validate method must be defined.');
                }

                $isValid = $this->validate($transformedValue);
                if (! is_bool($isValid)) {
                    throw new Exception(
                        'The validate method must return a boolean.'
                    );
                }
            } catch (TypeError $e) {
                $isValid = false;
            }

            if (! $isValid) {
                throw new InvalidValueException($this, $value);
            }

            $this->origValue = $value;
            $this->transformedValue = $transformedValue;
        }
    }

    public static function isValid($value): bool
    {
        return (bool) static::tryFrom($value);
    }

    public static function from($value): self
    {
        return new static($value);
    }

    public static function getFrom($value)
    {
        return static::from($value)->get();
    }

    public static function tryFrom($value): ?self
    {
        try {
            return static::from($value);
        } catch (InvalidValueException $e) {
            return null;
        }
    }

    public static function tryGetFrom($value)
    {
        $value = static::tryFrom($value);

        return $value ? $value->get() : null;
    }

    public function getOrigValue()
    {
        return $this->origValue;
    }

    public function get()
    {
        return $this->transformedValue;
    }
}
