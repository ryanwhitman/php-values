<?php

namespace RyanWhitman\PhpValues;

use Doctrine\Common\Annotations\AnnotationReader;
use Error;
use ReflectionMethod;
use RyanWhitman\PhpValues\Annotations\ShortcutMethod as ShortcutMethodAnnotation;
use RyanWhitman\PhpValues\Attributes\ShortcutMethod as ShortcutMethodAttribute;
use RyanWhitman\PhpValues\Exceptions\Exception;
use RyanWhitman\PhpValues\Exceptions\InvalidValueException;
use TypeError;

abstract class Value
{
    private $origValue;

    private $transformedValue;

    protected array $baseValues = [];

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
                $transformedValue = $this->applyBaseValues($value);
                $transformedValue = $this->performTransformation($transformedValue);
                $isValid = $this->performValidation($transformedValue);
            } catch (InvalidValueException|TypeError $e) {
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

    private static function canUseAttributes(): bool
    {
        return PHP_VERSION_ID >= 80000;
    }

    private static function parseMethodNameGetMiddle(
        string $methodName,
        string $startsWith,
        string $endsWith
    ): ?string {
        $matches = [];
        preg_match(
            "/(^{$startsWith})([A-Z][a-zA-Z\d]*)({$endsWith}$)/",
            $methodName,
            $matches
        );

        return $matches[2] ?? null;
    }

    private static function methodExists(string $methodName): bool
    {
        return method_exists(static::class, $methodName);
    }

    private static function isShortcutMethod(string $methodName): bool
    {
        static::throwIfMethodUndefined($methodName);

        return
            static::methodHasAnnotation($methodName, ShortcutMethodAnnotation::class) ||
            static::methodHasAttribute($methodName, ShortcutMethodAttribute::class);
    }

    private static function methodHasAnnotation(
        string $methodName,
        string $annotationClass
    ): bool {
        static::throwIfMethodUndefined($methodName);

        return
            (bool) (new AnnotationReader())->getMethodAnnotation(
                new ReflectionMethod(static::class, $methodName),
                $annotationClass
            );
    }

    private static function methodHasAttribute(
        string $methodName,
        string $attributeClass
    ): bool {
        static::throwIfMethodUndefined($methodName);

        return
            static::canUseAttributes() &&
            (new ReflectionMethod(static::class, $methodName))
                ->getAttributes($attributeClass);
    }

    private static function throwIfMethodUndefined(string $methodName): void
    {
        if (! static::methodExists($methodName)) {
            static::throwMethodUndefinedError($methodName);
        }
    }

    private static function throwMethodUndefinedError(string $methodName): void
    {
        throw new Error(
            'Call to undefined method '.static::class."::{$methodName}()"
        );
    }

    private function applyBaseValues($value)
    {
        foreach ($this->baseValues as $baseValueClass) {
            if (! is_subclass_of($baseValueClass, self::class)) {
                throw new Exception(
                    "The base value '{$baseValueClass}' is not a subclass of '".self::class."'"
                );
            }

            if ($baseValueClass === static::class) {
                throw new Exception(
                    "The value '{$baseValueClass}' contains itself as a base value."
                );
            }

            $value = $baseValueClass::getFrom($value);
        }

        return $value;
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

    public static function __callStatic(string $methodName, array $args)
    {
        $value = $args[0];

        // get*From
        $parsedMethodName = static::parseMethodNameGetMiddle(
            $methodName,
            'get',
            'From'
        );
        if (! is_null($parsedMethodName)) {
            $realMethodName = "get{$parsedMethodName}";
            if (
                static::methodExists($realMethodName) &&
                static::isShortcutMethod($realMethodName)
            ) {
                return static::from($value)->{$realMethodName}();
            }

            static::throwMethodUndefinedError($methodName);
        }

        // tryGet*From
        $parsedMethodName = static::parseMethodNameGetMiddle(
            $methodName,
            'tryGet',
            'From'
        );
        if (! is_null($parsedMethodName)) {
            $realMethodName = "get{$parsedMethodName}";
            if (
                static::methodExists($realMethodName) &&
                static::isShortcutMethod($realMethodName)
            ) {
                $value = static::tryFrom($value);

                return $value ? $value->{$realMethodName}() : null;
            }

            static::throwMethodUndefinedError($methodName);
        }

        static::throwMethodUndefinedError($methodName);
    }
}
