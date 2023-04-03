<?php

use RyanWhitman\PhpValues\SquishedString;

testWithValidInput(SquishedString::class, [
    'This is a Test' => 'This is a Test',
    "  This \n\nis\n  a  \r\t\n\r\t\n Test  \r\t\n" => 'This is a Test',
]);

testWithInvalidInput(SquishedString::class, [
    new stdClass(),
    [],
]);

assertStringable(new SquishedString('string'));
