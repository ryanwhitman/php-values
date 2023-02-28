# PHP Values

[![Latest Version on Packagist][ico-version]][link-packagist] [![PHP from Packagist][ico-php-versions]][link-packagist] [![Software License][ico-license]](LICENSE.md) [![Total Downloads][ico-downloads]][link-packagist]

This package is a tool for creating immutable value objects in PHP. A value object intakes any raw value of your choosing, transforms it, validates it, and is ready for use across your application.

## Install

You can install the package via composer:

```bash
composer require ryanwhitman/php-values
```

## Basic Example

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

Now, you're ready to use the value.
```php
<?php

use App\Values\Email;

function saveEmail(Email $email): void
{
    $email = $email->get();
}

$email = Email::from('email@example.com');
saveEmail($email);
```

## Testing

```bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email [ryanawhitman@gmail.com](mailto:ryanawhitman@gmail.com) instead of using the issue tracker.

## Credits

- [Ryan Whitman][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/ryanwhitman/php-values.svg?style=flat-square
[ico-php-versions]: https://img.shields.io/packagist/php-v/ryanwhitman/php-values?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ryanwhitman/php-values.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/ryanwhitman/php-values
[link-author]: https://github.com/RyanWhitman
[link-contributors]: ../../contributors
