<?php
/**
 * Run: php tests/run.php (PHP 8.2+, proc_open, sockets/streams and fileinfo; no dependencies).
 * Uses a loopback-only PHP server with the real controller, view and selected helpers.
 * Only bootstrap wiring, persistence and network services are replaced. No real bootstrap,
 * database, email, payment request or personal data is used. Runtime files live under tests/
 * in an exclusive directory; the server and files are cleaned up in finally, even on failure.
 * This does not exercise Docker/nginx, database adapters, CSS, or real provider integration.
 */
declare(strict_types=1);

require __DIR__ . '/Harness.php';

function signedHeaders(string $id = '123456', string $requestId = 'fixture-request', string $secret = 'synthetic-webhook-secret-not-a-credential'): array
{
    $timestamp = '1792600000';
    $signature = hash_hmac('sha256', "id:$id;request-id:$requestId;ts:$timestamp;", $secret);
    return ['Content-Type: application/json', "X-Request-Id: $requestId", "X-Signature: ts=$timestamp,v1=$signature"];
}

function syntheticCpf(): string
{
    // Algorithmic fixture, not an identifier obtained from a person.
    $digits = '012345678';
    for ($length = 9; $length <= 10; $length++) {
        $sum = 0;
        for ($i = 0; $i < $length; $i++) {
            $sum += (int) $digits[$i] * ($length + 1 - $i);
        }
        $check = (10 * $sum) % 11;
        $digits .= (string) ($check === 10 ? 0 : $check);
    }
    return $digits;
}

function checkoutFields(): array
{
    return ['_token' => 'fixture-csrf', 'name' => 'Pessoa Ficticia', 'cpf' => syntheticCpf(),
        'email' => 'tampered@example.invalid', 'phone' => '00000000000', 'zip' => '00000-000',
        'address' => 'Rua de Teste', 'number' => '0', 'complement' => 'Fixture', 'city' => 'Cidade Ficticia / ZZ',
        'payment' => 'not-pix', 'role' => 'admin'];
}

function pngBytes(int $size): string
{
    $chunk = static fn (string $type, string $data): string => pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
    $prefix = "\x89PNG\r\n\x1a\n" . $chunk('IHDR', pack('NNCCCCC', 1, 1, 8, 6, 0, 0, 0))
        . $chunk('IDAT', gzcompress("\0\0\0\0\0"));
    $end = $chunk('IEND', '');
    // Valid ancillary padding chunk, with CRC, rather than a truncated or corrupt image.
    return $prefix . $chunk('npAD', str_repeat('x', $size - strlen($prefix) - strlen($end) - 12)) . $end;
}

function multipart(string $file, array $overrides = []): array
{
    $boundary = 'regression-boundary-' . bin2hex(random_bytes(8));
    $fields = array_replace(['_token' => 'fixture-csrf', 'name' => 'Doce Fixture', 'description' => 'Apenas teste',
        'price' => '20.00', 'category' => 'Testes', 'stock' => '1', 'active' => '1'], $overrides);
    $body = '';
    foreach ($fields as $name => $value) {
        $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"$name\"\r\n\r\n$value\r\n";
    }
    $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"image_file\"; filename=\"fixture.png\"\r\n"
        . "Content-Type: image/png\r\n\r\n$file\r\n--$boundary--\r\n";
    return [$body, ['Content-Type: multipart/form-data; boundary=' . $boundary]];
}

$h = new Harness();
$exitCode = 1;
try {
    $h->start();
    require __DIR__ . '/webhook.php';
    require __DIR__ . '/checkout.php';
    require __DIR__ . '/upload.php';
    $h->test('runtime: no unexpected PHP warnings or fatal errors', function () use ($h): void {
        $log = $h->log();
        // PHP itself warns when post_max_size is exceeded; these requests are intentional.
        $log = preg_replace('/^.*POST Content-Length of .* exceeds the limit of .*$/m', '', $log);
        $h->same(0, preg_match('/(?:Fatal error|Parse error|Warning:|Deprecated:|Notice:)/i', $log), $log);
    });
    echo "\n{$h->passed} passed, {$h->failed} failed; {$h->assertions} assertions.\n";
    $exitCode = $h->failed === 0 ? 0 : 1;
} catch (Throwable $error) {
    fwrite(STDERR, 'Harness error: ' . $error->getMessage() . "\n" . $h->log());
} finally {
    $h->close();
}
if (is_dir($h->directory)) {
    fwrite(STDERR, "Runtime cleanup failed.\n");
    $exitCode = 1;
}
exit($exitCode);
