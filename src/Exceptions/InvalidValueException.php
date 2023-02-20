<?php

namespace RyanWhitman\Values\Exceptions;

use RyanWhitman\Values\Value;

class InvalidValueException extends Exception
{
    public function __construct(Value $valueObj, $value)
    {
        parent::__construct(
            'An invalid value (' . get_class($valueObj).") was attempted: {$this->describeValue($value)}"
        );
    }

    private function describeValue($value): string
    {
        if (is_resource($value)) {
            return 'resource';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return 'boolean of "'.($value ? 'true' : 'false').'"';
        }

        if (is_scalar($value)) {
            return gettype($value).' of "'.$value.'"';
        }

        if (is_object($value)) {
            return 'instance of '.get_class($value).(
                method_exists($value, '__toString') ?
                ' with string value of "'.$value.'"' :
                ''
            );
        }

        return 'array of '.json_encode($value);
    }
}
