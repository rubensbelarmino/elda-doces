<?php

declare(strict_types=1);

final class Harness
{
    public string $directory;
    private mixed $process = null;
    private string $baseUrl;
    public int $passed = 0;
    public int $failed = 0;
    public int $assertions = 0;

    public function __construct()
    {
        // All generated files (including PHP upload files) stay inside this exclusive directory.
        $this->directory = __DIR__ . DIRECTORY_SEPARATOR . '.runtime-' . bin2hex(random_bytes(10));
        if (!mkdir($this->directory, 0700)) {
            throw new RuntimeException('Cannot create isolated test directory.');
        }
    }

    public function start(): void
    {
        $root = dirname(__DIR__);
        foreach (['public', 'public/assets', 'public/assets/images', 'uploads'] as $path) {
            mkdir($this->directory . '/' . $path, 0700, true);
        }
        $source = file_get_contents($root . '/public/index.php');
        $needle = "require dirname(__DIR__) . '/src/bootstrap.php';";
        $replacement = 'require ' . var_export($this->directory . '/bootstrap.php', true) . ';';
        $controller = str_replace($needle, $replacement, $source, $count);
        if ($count !== 1) {
            throw new RuntimeException('Expected exactly one bootstrap require in the real controller.');
        }
        file_put_contents($this->directory . '/public/index.php', $controller);
        file_put_contents($this->directory . '/bootstrap.php', "<?php\n"
            . 'define("TEST_ROOT", ' . var_export($root, true) . ");\n"
            . 'define("TEST_RUNTIME", ' . var_export($this->directory, true) . ");\n"
            . 'require ' . var_export(__DIR__ . '/fixtures.php', true) . ";\n");

        // Extract only these real pure/middleware helpers; never execute the production bootstrap.
        $helpers = self::extractFunctions($root . '/src/bootstrap.php', [
            'post_string', 'valid_cpf', 'slugify', 'e', 'money', 'asset', 'versioned_asset',
            'csrf_token', 'verify_csrf', 'flash', 'redirect', 'safe_redirect_target',
            'current_user', 'require_auth', 'require_admin', 'cart_details',
        ]);
        $helpers .= self::extractFunctions($root . '/src/Store.php', ['is_uuid']);
        file_put_contents($this->directory . '/helpers.php', "<?php\ndeclare(strict_types=1);\n" . $helpers);

        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
        if ($socket === false) {
            throw new RuntimeException("Cannot reserve local port: $error");
        }
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        $this->baseUrl = 'http://' . $address;
        $configuration = parse_ini_file($root . '/docker/php.ini');
        $command = [PHP_BINARY, '-n'];
        if (PHP_OS_FAMILY === 'Windows') {
            $command = array_merge($command, ['-d', 'extension_dir=' . dirname(PHP_BINARY) . '/ext']);
        } elseif (ini_get('extension_dir')) {
            $command = array_merge($command, ['-d', 'extension_dir=' . ini_get('extension_dir')]);
        }
        // Probe the same isolated interpreter, not the parent's loaded extensions.
        if (!$this->fileinfoIsBuiltIn()) {
            $command = array_merge($command, ['-d', 'extension=fileinfo']);
        }
        $command = array_merge($command, ['-d', 'post_max_size=' . $configuration['post_max_size'],
            '-d', 'upload_max_filesize=' . $configuration['upload_max_filesize'],
            '-d', 'upload_tmp_dir=' . $this->directory . '/uploads',
            '-d', 'sys_temp_dir=' . $this->directory . '/uploads',
            '-d', 'display_errors=0', '-d', 'log_errors=1', '-d', 'memory_limit=128M',
            '-d', 'max_execution_time=15', '-S', $address,
            '-t', $this->directory . '/public', $this->directory . '/public/index.php']);
        $environment = getenv();
        unset($environment['PHP_CLI_SERVER_WORKERS']);
        $environment['APP_URL'] = $this->baseUrl;
        $this->process = proc_open($command, [
            0 => ['pipe', 'r'],
            1 => ['file', $this->directory . '/server.log', 'a'],
            2 => ['file', $this->directory . '/server.log', 'a'],
        ], $pipes, $this->directory, $environment, ['bypass_shell' => true]);
        if (!is_resource($this->process)) {
            throw new RuntimeException('Cannot start the isolated PHP server.');
        }
        fclose($pipes[0]);
        $this->seed();
        $ready = false;
        for ($attempt = 0; $attempt < 60; $attempt++) {
            if (!proc_get_status($this->process)['running']) {
                break;
            }
            try {
                $response = $this->request('GET', '/health', '', [], 0.2);
                $ready = $response['status'] === 200
                    && (json_decode($response['body'], true)['status'] ?? '') === 'ok';
            } catch (Throwable) {
                // Startup is bounded to approximately three seconds.
            }
            if ($ready) {
                break;
            }
            usleep(50_000);
        }
        if (!$ready) {
            throw new RuntimeException('Server failed readiness: ' . $this->log());
        }
    }

    private function fileinfoIsBuiltIn(): bool
    {
        $stdout = $this->directory . '/fileinfo-probe.out';
        $stderr = $this->directory . '/fileinfo-probe.err';
        $probe = proc_open(
            [PHP_BINARY, '-n', '-r', 'echo extension_loaded("fileinfo") ? "1" : "0";'],
            [0 => ['pipe', 'r'], 1 => ['file', $stdout, 'w'], 2 => ['file', $stderr, 'w']],
            $pipes,
            $this->directory,
            null,
            ['bypass_shell' => true]
        );
        if (!is_resource($probe)) {
            throw new RuntimeException('Cannot start isolated fileinfo probe.');
        }
        try {
            fclose($pipes[0]);
            $deadline = microtime(true) + 3;
            do {
                $status = proc_get_status($probe);
                if (!$status['running']) {
                    break;
                }
                usleep(20_000);
            } while (microtime(true) < $deadline);
            if ($status['running']) {
                throw new RuntimeException('Isolated fileinfo probe timed out.');
            }
            $output = file_get_contents($stdout);
            $error = file_get_contents($stderr);
            if ($status['exitcode'] !== 0 || $error !== '' || !in_array($output, ['0', '1'], true)) {
                throw new RuntimeException('Isolated fileinfo probe failed: ' . $error . $output);
            }
            return $output === '1';
        } finally {
            if (is_resource($pipes[0])) {
                fclose($pipes[0]);
            }
            if (proc_get_status($probe)['running']) {
                proc_terminate($probe, 9);
            }
            proc_close($probe);
        }
    }

    private static function extractFunctions(string $file, array $names): string
    {
        $tokens = token_get_all(file_get_contents($file));
        $found = [];
        $result = '';
        for ($i = 0; $i < count($tokens); $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) {
                continue;
            }
            $nameIndex = $i + 1;
            while (is_array($tokens[$nameIndex]) && $tokens[$nameIndex][0] === T_WHITESPACE) {
                $nameIndex++;
            }
            $name = is_array($tokens[$nameIndex]) ? $tokens[$nameIndex][1] : '';
            if (!in_array($name, $names, true)) {
                continue;
            }
            $body = '';
            $depth = 0;
            $opened = false;
            for (; $i < count($tokens); $i++) {
                $token = $tokens[$i];
                $body .= is_array($token) ? $token[1] : $token;
                if ($token === '{') {
                    $depth++;
                    $opened = true;
                } elseif ($token === '}') {
                    $depth--;
                }
                if ($opened && $depth === 0) {
                    break;
                }
            }
            $result .= $body . "\n";
            $found[] = $name;
        }
        if (array_diff($names, $found) !== [] || count($found) !== count($names)) {
            throw new RuntimeException('Could not uniquely extract real helpers from ' . $file);
        }
        return $result;
    }

    public function seed(array $overrides = []): array
    {
        $state = array_replace([
            'secret' => 'synthetic-webhook-secret-not-a-credential',
            'role' => 'admin',
            'address' => null,
            'events' => [],
            'orders' => [],
            'products' => [],
            'order' => ['id' => '11111111-1111-4111-8111-111111111111', 'total_cents' => 3490],
            'payment' => ['id' => '123456', 'external_reference' => '11111111-1111-4111-8111-111111111111',
                'transaction_amount' => 34.90, 'status' => 'approved'],
            'api_error' => false,
        ], $overrides);
        file_put_contents($this->directory . '/state.json', json_encode($state, JSON_THROW_ON_ERROR));
        return $state;
    }

    public function state(): array
    {
        return json_decode(file_get_contents($this->directory . '/state.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    public function events(string $name): array
    {
        return array_values(array_filter($this->state()['events'], fn ($event) => $event[0] === $name));
    }

    public function request(string $method, string $path, string $body = '', array $headers = [], float $timeout = 10): array
    {
        $headers = array_merge(['Connection: close', 'Content-Length: ' . strlen($body)], $headers);
        $context = stream_context_create(['http' => [
            'method' => $method, 'content' => $body, 'header' => implode("\r\n", $headers),
            'ignore_errors' => true, 'follow_location' => 0, 'timeout' => $timeout,
        ]]);
        $response = @file_get_contents($this->baseUrl . $path, false, $context);
        if ($response === false) {
            throw new RuntimeException('Local request failed: ' . $method . ' ' . $path);
        }
        $responseHeaders = $http_response_header ?? [];
        preg_match('/\s(\d{3})\s/', $responseHeaders[0] ?? '', $matches);
        $location = '';
        foreach ($responseHeaders as $header) {
            if (str_starts_with(strtolower($header), 'location:')) {
                $location = trim(substr($header, 9));
            }
        }
        return ['status' => (int) ($matches[1] ?? 0), 'body' => $response, 'location' => $location];
    }

    public function form(string $path, array $fields): array
    {
        return $this->request('POST', $path, http_build_query($fields), ['Content-Type: application/x-www-form-urlencoded']);
    }

    public function test(string $name, callable $test): void
    {
        try {
            $test();
            $this->passed++;
            echo "PASS $name\n";
        } catch (Throwable $error) {
            $this->failed++;
            fwrite(STDERR, "FAIL $name: {$error->getMessage()}\n");
        }
    }

    public function same(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertions++;
        if ($expected !== $actual) {
            throw new RuntimeException($message . ' expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
    }

    public function log(): string
    {
        return is_file($this->directory . '/server.log') ? file_get_contents($this->directory . '/server.log') : '';
    }

    public function close(): void
    {
        if (is_resource($this->process)) {
            proc_terminate($this->process);
            for ($attempt = 0; $attempt < 50 && proc_get_status($this->process)['running']; $attempt++) {
                usleep(20_000);
            }
            if (proc_get_status($this->process)['running']) {
                proc_terminate($this->process, 9);
            }
            proc_close($this->process);
            $this->process = null;
        }
        if (is_dir($this->directory)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($this->directory);
        }
    }
}
