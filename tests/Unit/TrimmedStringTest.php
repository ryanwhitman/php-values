<?php

use RyanWhitman\PhpValues\TrimmedString;

testWithValidInput(TrimmedString::class, [
    'This is a Test' => 'This is a Test',
    "  This \r\t\nis a Test \r\t\n  " => "This \r\t\nis a Test",
]);

testWithInvalidInput(TrimmedString::class, [
    new stdclass(),
    [],
]);

assertStringable(new TrimmedString('string'));
