<?php

use RyanWhitman\PhpValues\Exceptions\InvalidValueException;
use RyanWhitman\PhpValues\Value;

function mockValue($input, bool $isValid): Value
{
    $mockedValue = test()->getMockBuilder(Value::class)
        ->disableOriginalConstructor()
        ->addMethods(['validate'])
        ->getMockForAbstractClass();

    $mockedValue
        ->expects(test()->once())
        ->method('validate')
        ->with($input)
        ->willReturn($isValid);

    (new ReflectionClass(Value::class))
        ->getConstructor()
        ->invoke($mockedValue, $input);

    return $mockedValue;
}

it('throws exception when invalid')
    ->mockValue('test', false)
    ->throws(InvalidValueException::class);

$input = 'test';
it('gets value when valid')
    ->expect(fn () => mockValue($input, true))
    ->get()
    ->toBe($input);
