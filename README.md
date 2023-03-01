# PHP Values

[![Latest Version on Packagist][ico-version]][link-packagist] [![PHP from Packagist][ico-php-versions]][link-packagist] [![Software License][ico-license]](LICENSE) [![Total Downloads][ico-downloads]][link-packagist]

PHP Values is a tool for creating immutable value objects in PHP. A value object intakes any raw value of your choosing, transforms it, validates it, and is ready for use across your application. For instance, suppose you need an email address when creating a user. While you can write a method like this:

```php
public function saveUser(string $email)
{
    // perform sanitation and validation on email before using
}
```

It's more optimal to write it like this:

```php
use RyanWhitman\PhpValues\Email;

public function saveUser(Email $email)
{
    // email has been sanitized and validated and is ready for use
}
```

## Install

You can install the package via composer:

```bash
composer require ryanwhitman/php-values
```

## Usage

Start by creating a value class. For instance, a value class for an email address:

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

## API

### Static Methods

#### from(mixed $value): Value

The `from` static method will return a Value instance when validation passes and will throw an exception when validation fails.

```php
Email::from('email@example.com'); // instance of Email
Email::from('non-email'); // throws RyanWhitman\PhpValues\Exceptions\InvalidValueException
```

#### getFrom(mixed $value): mixed

The `getFrom` static method is a shortcut for `::from($value)->get()`.

```php
Email::getFrom('email@example.com'); // email@example.com
Email::getFrom('non-email'); // throws RyanWhitman\PhpValues\Exceptions\InvalidValueException
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

#### getOrigValue(): mixed

The `getOrigValue` method returns the original input value (prior to transformation).

```php
Email::from('e m ail@example.com')->getOrigValue(); // e m ail@example.com
```

#### get(): mixed

The `get` method returns the transformed and validated value.

```php
Email::from('e m ail@example.com')->get(); // email@example.com
```

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
