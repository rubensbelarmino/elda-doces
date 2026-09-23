<?php
//
// $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
// $publicRoot = realpath(__DIR__ . '/public');
// $file = $publicRoot !== false ? realpath($publicRoot . $path) : false;
// if ($path !== '/' && $publicRoot !== false && $file !== false && str_starts_with($file, $publicRoot . DIRECTORY_SEPARATOR) && is_file($file)) return false;
// require __DIR__ . '/public/index.php';
