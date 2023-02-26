<?php

use Pest\Expectation;
use RyanWhitman\PhpValues\Exceptions\InvalidValueException;
use RyanWhitman\PhpValues\Value;

function expectClass(object $obj): Expectation
{
    return expect(get_class($obj));
}

function testWithValidInput(string $className, array $data): void
{
    $count = 0;
    foreach ($data as $input => $output) {
        $count++;

        test("test constructor with valid input #{$count}", function () use ($className, $input, $output) {
            $value = new $className($input);
            expect($value->getOrigValue())->toBe($input);
            expect($value->get())->toBe($output);
        });

        test("test 'from' with valid input #{$count}", function () use ($className, $input, $output) {
            $value = $className::from($input);
            expectClass($value)->toBe($className);
            expect($value->getOrigValue())->toBe($input);
            expect($value->get())->toBe($output);
        });

        test("test 'tryFrom' with valid input #{$count}", function () use ($className, $input, $output) {
            $value = $className::tryFrom($input);
            expectClass($value)->toBe($className);
            expect($value->getOrigValue())->toBe($input);
            expect($value->get())->toBe($output);
        });

        test("test 'getFrom' with valid input #{$count}", function () use ($className, $input, $output) {
            expect($className::getFrom($input))->toBe($output);
        });

        test("test 'tryGetFrom' with valid input #{$count}", function () use ($className, $input, $output) {
            expect($className::tryGetFrom($input))->toBe($output);
        });

        test("test 'isValid' with valid input #{$count}", function () use ($className, $input) {
            expect($className::isValid($input))->toBeTrue();
        });
    }
}

function testWithInvalidInput(string $className, array $data): void
{
    $count = 0;
    foreach ($data as $input) {
        $count++;

        test("test constructor with invalid input #{$count}", function () use ($className, $input) {
            new $className($input);
        })->throws(InvalidValueException::class);

        test("test 'from' with invalid input #{$count}", function () use ($className, $input) {
            $className::from($input);
        })->throws(InvalidValueException::class);

        test("test 'tryFrom' with invalid input #{$count}", function () use ($className, $input) {
            expect($className::tryFrom($input))->toBeNull();
        });

        test("test 'getFrom' with invalid input #{$count}", function () use ($className, $input) {
            $className::getFrom($input);
        })->throws(InvalidValueException::class);

        test("test 'tryGetFrom' with invalid input #{$count}", function () use ($className, $input) {
            expect($className::tryGetFrom($input))->toBeNull();
        });

        test("test 'isValid' with invalid input #{$count}", function () use ($className, $input) {
            expect($className::isValid($input))->toBeFalse();
        });
    }
}

function assertStringable(Value $value): void
{
    it('is stringable')
        ->expect((string) $value)
        ->toBe((string) $value->get());
}
