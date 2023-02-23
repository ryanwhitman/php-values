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
        // Is the value already an instance of the particular Value being
        // instantiated? If so, it's already been transformed and validated.
        if ($value instanceof static) {
            $transformedValue = $value->get();
            $value = $value->getOrigValue();
        } else {
            // Is the value an instance of this abstract Value class? If so,
            // grab its value and then proceed with transformation / validation.
            if ($value instanceof self) {
                $value = $value->get();
            }

            try {
                $transformedValue = $this->performTransformation($value);
                $isValid = $this->performValidation($transformedValue);
            } catch (TypeError $e) {
                $isValid = false;
            }

            if (! $isValid) {
                throw new InvalidValueException($this, $value);
            }
        }

        $this->origValue = $value;
        $this->transformedValue = $transformedValue;
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

    private function performTransformation($value)
    {
        return method_exists($this, 'transform') ?
            $this->transform($value) :
            $value;
    }

    private function performValidation($value): bool
    {
        if (! method_exists($this, 'validate')) {
            throw new Exception('A validate method must be defined.');
        }

        $isValid = $this->validate($value);
        if (! is_bool($isValid)) {
            throw new Exception(
                'The validate method must return a boolean.'
            );
        }

        return $isValid;
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
