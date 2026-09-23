<?php

declare(strict_types=1);

$directory = dirname(__DIR__) . '/storage/mail';
$pointer = @file_get_contents($directory . '/latest');
$filename = basename(trim((string) $pointer));
$path = $directory . '/' . $filename;
if ($filename === '' || !is_file($path)) {
    fwrite(STDERR, "Nenhum e-mail 2FA local foi encontrado.\n");
    exit(1);
}
$message = (string) file_get_contents($path);
preg_match('/<!-- To: ([^|]+) \|/', $message, $recipient);
preg_match('/>([0-9]{6})</', $message, $code);
if (empty($code[1])) {
    fwrite(STDERR, "O último e-mail não contém um código 2FA.\n");
    exit(1);
}
echo 'Destinatário: ' . trim($recipient[1] ?? 'desconhecido') . PHP_EOL;
echo 'Código: ' . $code[1] . PHP_EOL;
echo 'Arquivo: ' . $path . PHP_EOL;
