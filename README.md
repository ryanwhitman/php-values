# PHP Values

[![Latest Version on Packagist][ico-version]][link-packagist] [![PHP from Packagist][ico-php-versions]][link-packagist] [![Software License][ico-license]](LICENSE) [![Total Downloads][ico-downloads]][link-packagist]

PHP Values is a tool for creating immutable value objects in PHP. A value object intakes a raw value, transforms it, validates it, and can be used consistently and dependably across your application.

For instance, suppose you need an email address when creating a user. You can write it more traditionally like this:

```php
public function createUser(string $email)
{
    // perform sanitation and validation on email before using
}
```

But it's more optimal to write it like this:

```php
use RyanWhitman\PhpValues\Email;

public function createUser(Email $email)
{
    // email has already been sanitized and validated and is ready for use
}
```

## Install

You should install the package via composer:

```bash
composer require ryanwhitman/php-values
```

## Example

Start by creating a Value class. For instance, a Value class for an email address:

```php
<?php

namespace App\Values;

use RyanWhitman\PhpValues\Value;
use RyanWhitman\PhpValues\Concerns\Stringable;

class Email extends Value
{
    use Stringable;

    protected function transform(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    protected function validate(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
```

Now, you're ready to use the value:

```php
<?php

use App\Values\Email;

// Valid email address
Email::from('email@example.com'); // instance of Email
Email::from('email@example.com')->get(); // email@example.com
Email::getFrom('email@example.com'); // email@example.com
(string) Email::from('email@example.com'); // email@example.com
Email::isValid('email@example.com'); // true

// Valid email address (with imperfections)
Email::getFrom(' email @example.com '); // email@example.com
Email::isValid(' email @example.com '); // true

// Invalid email address
Email::from('non-email'); // throws exception
Email::tryFrom('non-email'); // null
Email::isValid('non-email'); // false
```

## Usage

To create a new Value class, extend the `RyanWhitman\PhpValues\Value` class. From there, define a `transform` method (optional) and a `validate` method (mandatory). Upon instantiation, the `transform` method receives the raw input and transforms it, as needed. Then, the `validate` method receives the transformed value and returns `true` or `false`. If validation passes, the object is ready for use. If validation passes, `InvalidValueException` is thrown. Note: 2 `try` static methods exist that catch the exception and return `null`.

#### transform(mixed $value): mixed

The `transform` method is an optional method called during instantiation. It receives the input value and, when defined, should return a sanitized/transformed version of the value. The transform method is not defined in the base abstract Value class to allow for proper typing in sub-classes.

```php
protected function transform(string $email): string
{
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}
```

#### validate(mixed $value): bool
The `validate` method is called during instantiation. It receives the transformed value and should return true or false. The validate method is not defined in the base abstract Value class to allow for proper typing in sub-classes.

```php
protected function validate(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
```

### Base Values

Suppose you're creating a Value class for a person's name. You'll likely want to remove all superfluous whitespace. You could, of course, simply call another Value class within your `transform` method, but you can also define a `$baseValues` property to automatically run other Value classes:

```php
<?php

namespace App\Values;

use RyanWhitman\PhpValues\SquishedString;
use RyanWhitman\PhpValues\Value;

class Name extends Value
{
    protected array $baseValues = [
        SquishedString::class,
    ];

    // ...
}
```

### Static Methods

#### from(mixed $value): Value

The `from` static method will return a Value instance when validation passes and will throw an exception when validation fails.

```php
Email::from('email@example.com'); // instance of Email
Email::from('non-email'); // throws InvalidValueException
```

#### getFrom(mixed $value): mixed

The `getFrom` static method is a shortcut for `::from($value)->get()`.

```php
Email::getFrom('email@example.com'); // email@example.com
Email::getFrom('non-email'); // throws InvalidValueException
```

#### tryFrom(mixed $value): ?Value

The `tryFrom` static method will return a Value instance when validation passes and `null` when validation fails.

```php
Email::tryFrom('email@example.com'); // instance of Email
Email::tryFrom('non-email'); // null
```

#### tryGetFrom(mixed $value): mixed

The `tryGetFrom` static method is a shortcut for `::tryFrom($value)->get()`.

```php
Email::tryGetFrom('email@example.com'); // email@example.com
Email::tryGetFrom('non-email'); // null
```

#### isValid(mixed $value): bool

The `isValid` static method will return true or false.

```php
Email::isValid('email@example.com'); // true
Email::isValid('non-email'); // false
```

### Instance Methods

#### getOrigValue(): mixed

The `getOrigValue` method returns the original input value (before transformation).

```php
Email::from('e m ail@example.com')->getOrigValue(); // e m ail@example.com
```

#### get(): mixed

The `get` method returns the transformed and validated value.

```php
Email::from('e m ail@example.com')->get(); // email@example.com
```

### Shortcut Methods

As mentioned above, the `getFrom` and `tryGetFrom` static methods are shortcuts for `::from($value)->get()` and `::tryFrom($value)->get()`, respectively. You may add the `ShortcutMethod` annotation/attribute to your custom get methods to add the same shortcut capabilities. Shortcut methods must be defined using camelCase and start with `get` (e.g. `getFormatted`).

Using a [doctrine annotation](https://www.doctrine-project.org/projects/doctrine-annotations/en/2.0/index.html) in PHP 7.4+:

```php
use RyanWhitman\PhpValues\Annotations\ShortcutMethod;

/**
 * @ShortcutMethod
 */
public function getFormatted()
{
    // ...
}
```

Using an [attribute](https://www.php.net/manual/en/language.attributes.overview.php) in PHP 8.0+:

```php
use RyanWhitman\PhpValues\Attributes\ShortcutMethod;

#[ShortcutMethod]
public function getFormatted()
{
    // ...
}
```

After adding the `ShortcutMethod` annotation/attribute to the `getFormatted` method, for example, the following will work:

```php
::getFormattedFrom($value)
::tryGetFormattedFrom($value
```

### Traits

#### RyanWhitman\PhpValues\Concerns\Stringable

The `Stringable` trait simply defines the `__toString()` magic method with `(string) $this->get()`.

### Exceptions

PHP Values will throw 1 of 2 exceptions:

`RyanWhitman\PhpValues\Exceptions\InvalidValueException` will be thrown when either a `TypeError` occurs (e.g. an array is needed but a string is provided) or when validation fails. This exception is useful as it indicates the raw input is invalid. `RyanWhitman\PhpValues\Exceptions\Exception` is thrown when something else goes wrong (e.g. a `validate` method is not defined). Note: The `try` methods only catch `InvalidValueException`.

## Pre-Built Values

- [Email](https://github.com/RyanWhitman/php-values/blob/main/src/Email.php)
- [SquishedString](https://github.com/RyanWhitman/php-values/blob/main/src/SquishedString.php)
- [TrimmedString](https://github.com/RyanWhitman/php-values/blob/main/src/TrimmedString.php)

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email [ryanawhitman@gmail.com](mailto:ryanawhitman@gmail.com) instead of using the issue tracker.

## Credits

- [Ryan Whitman][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please have a look at [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/ryanwhitman/php-values.svg?style=flat-square
[ico-php-versions]: https://img.shields.io/packagist/php-v/ryanwhitman/php-values?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ryanwhitman/php-values.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/ryanwhitman/php-values
[link-author]: https://github.com/RyanWhitman
[link-contributors]: ../../contributors
