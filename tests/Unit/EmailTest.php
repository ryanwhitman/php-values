<?php

use RyanWhitman\PhpValues\Email;

testWithValidInput(Email::class, [
    'Test@Example.com' => 'Test@Example.com',
    '  test@example.com  ' => 'test@example.com',
    "  \n\n 123  test@example.com \r\t\n " => '123test@example.com',
]);

testWithInvalidInput(Email::class, [
    new stdclass(),
    1,
    'non-email',
    'test@example.com@',
    '@test@example.com',
]);

assertStringable(new Email('test@example.com'));
