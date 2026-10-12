<?php

declare(strict_types=1);

$publicDir = '/app/public';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = $publicDir.$path;

if ('/' !== $path && is_file($file)) {
    return false;
}

$_SERVER['SCRIPT_FILENAME'] = $publicDir.'/index.php';

require '/app/vendor/autoload_runtime.php';
