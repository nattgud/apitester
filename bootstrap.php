<?php

declare(strict_types=1);

$config = require __DIR__ . '/config/config.php';

spl_autoload_register(function (string $class): void {

    $prefix = 'App\\';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));

    $file = __DIR__ . '/src/' .
        str_replace('\\', '/', $relative) .
        '.php';

    if (is_file($file)) {
        require $file;
    }
});