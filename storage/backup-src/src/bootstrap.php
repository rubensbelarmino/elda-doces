<?php
/**
 * ============================================================================
 * BOOTSTRAP & INFRAESTRUTURA DA APLICAÇÃO — ELDA BOLOS E DOCES
 * ============================================================================
 * Inicializador central responsável por carregar variáveis de ambiente,
 * configurar sessões seguras em cluster, injetar cabeçalhos de segurança (CSP),
 * conectar ao banco de dados e fornecer funções utilitárias de SEO e segurança.
 *
 * TÉCNICAS DE SEO & PERFORMANCE IMPLEMENTADAS NESTE ARQUIVO:
 * ----------------------------------------------------------------------------
 * 1. VERSIONAMENTO DE ASSETS (CACHE BUSTING / PERFORMANCE):
 *    - Função versioned_asset(): Anexa o timestamp de modificação do arquivo (?v=...)
 *      aos arquivos CSS e JS. Isso permite configurar tempos de cache longos no Nginx
 *      sem que o usuário final visualize estilos defasados após uma atualização.
 *
 * 2. URL BASE CANÔNICA (REQUEST_BASE_URL):
 *    - Determina com precisão a URL absoluta do site (incluindo HTTPS e porta),
 *      essencial para gerar URLs canônicas, metadados Open Graph e o sitemap.xml.
 *
 * 3. GERADOR DE SLUGS SEMÂNTICOS (SLUGIFY / SEO ON-PAGE):
 *    - Converte títulos de produtos em texto ASCII transliterado e separado por hífens
 *      (ex: "Tartelette de Morango" -> "tartelette-de-morango"), garantindo URLs limpas.
 *
 * 4. PLACEHOLDER ANTI-CLS (CUMULATIVE LAYOUT SHIFT):
 *    - Função image_placeholder(): Retorna um GIF transparente de 1x1 pixel em base64,
 *      evitando ícones de imagem quebrada e saltos no layout antes do carregamento.
 * ============================================================================
 */

declare(strict_types=1);

// Carregamento dos módulos essenciais da aplicação
require_once __DIR__ . '/Store.php';
require_once __DIR__ . '/Mailer.php';
require_once __DIR__ . '/RateLimiter.php';
require_once __DIR__ . '/MercadoPagoGateway.php';

// Fuso horário oficial do Brasil para registros e logs
date_default_timezone_set('America/Sao_Paulo');

// ============================================================================
// 1. CARREGAMENTO DO ARQUIVO DE AMBIENTE (.env)
// ============================================================================
$environmentFile = dirname(__DIR__) . '/.env';
if (is_file($environmentFile)) {
    foreach (file($environmentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$name, $value] = array_map('trim', explode('=', $line, 2));
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $name)) continue;
        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
    }
}

// ============================================================================
// 2. CONFIGURAÇÃO DE DIRETÓRIOS E SESSÕES SEGURAS (CLUSTER MULTI-NÓ)
// ============================================================================
$storageDirectory = dirname(__DIR__) . '/storage';
$sessionDirectory = $storageDirectory . '/sessions';
$runtimeDirectory = $storageDirectory . '/runtime';
if (!is_dir($sessionDirectory)) mkdir($sessionDirectory, 0775, true);
if (!is_dir($runtimeDirectory)) mkdir($runtimeDirectory, 0775, true);

// Proteções contra Fixação e Sequestro de Sessão
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.sid_length', '48');
ini_set('session.sid_bits_per_character', '6');
session_save_path($sessionDirectory);
session_name('doceatelier_session');

// Detecção inteligente de terminação SSL/TLS em balanceadores ou proxies reversos
$forwardedHttps = strtolower(trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0])) === 'https';
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($forwardedHttps && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true));

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ============================================================================
// 3. CABEÇALHOS DE SEGURANÇA REFORÇADOS & CONTENT SECURITY POLICY (CSP)
// ============================================================================
$nonce = base64_encode(random_bytes(18));
header("Content-Security-Policy: default-src 'self'; connect-src 'self' https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com; img-src 'self' data: https://*.google-analytics.com https://*.googletagmanager.com; style-src 'self' 'unsafe-inline' 'nonce-$nonce'; style-src-attr 'unsafe-inline'; style-src-elem 'self' 'nonce-$nonce'; script-src 'self' 'nonce-$nonce' https://www.googletagmanager.com; font-src 'self'; frame-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'; object-src 'none'; manifest-src 'self'; media-src 'none'; worker-src 'none'; upgrade-insecure-requests");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 0');
header('X-DNS-Prefetch-Control: off');
header('X-Download-Options: noopen');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
header('Cross-Origin-Opener-Policy: same-origin');
header('Cross-Origin-Resource-Policy: same-origin');
header('Cross-Origin-Embedder-Policy: require-corp');
header('Origin-Agent-Cluster: ?1');
header('X-Permitted-Cross-Domain-Policies: none');
header('Cache-Control: no-store, private, max-age=0');
header('Pragma: no-cache');

$appNode = getenv('APP_NODE') ?: 'php-' . ($_SERVER['SERVER_PORT'] ?? 'cli');
header('X-App-Node: ' . $appNode);

if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// ============================================================================
// 4. INSTANCIAÇÃO DA CAMADA DE PERSISTÊNCIA (STORE)
// ============================================================================
try {
    $dsn = getenv('DB_DSN') ?: '';
    $store = $dsn !== ''
        ? new MysqlStore($dsn, getenv('DB_USER') ?: 'doce', getenv('DB_PASSWORD') ?: '')
        : new JsonStore(dirname(__DIR__) . '/storage/data.json');
} catch (Throwable $exception) {
    error_log($exception->__toString());
    http_response_code(503);
    exit('Serviço temporariamente indisponível.');
}

// ============================================================================
// 5. SERVIÇOS AUXILIARES: MAILER, CHAVE CRIPTOGRÁFICA & RATE LIMITER
// ============================================================================
$mailer = Mailer::fromEnvironment($storageDirectory);
$appKey = trim(getenv('APP_KEY') ?: '');
if ($appKey === '') {
    $appKeyPath = $storageDirectory . '/app.key';
    $keyHandle = fopen($appKeyPath, 'c+');
    if ($keyHandle === false) throw new RuntimeException('Não foi possível inicializar a chave da aplicação.');
    flock($keyHandle, LOCK_EX);
    $appKey = trim(stream_get_contents($keyHandle) ?: '');
    if ($appKey === '') {
        $appKey = bin2hex(random_bytes(32));
        rewind($keyHandle);
        fwrite($keyHandle, $appKey);
        fflush($keyHandle);
        @chmod($appKeyPath, 0600);
    }
    flock($keyHandle, LOCK_UN);
    fclose($keyHandle);
}

$rateLimiter = new RateLimiter($runtimeDirectory . '/rate-limits.json', $appKey);
$mercadoPago = MercadoPagoGateway::fromEnvironment();

// ============================================================================
// 6. FUNÇÕES UTILITÁRIAS DE SEGURANÇA, FORMATAÇÃO E SEO
// ============================================================================

/**
 * Escapa strings com segurança contra Cross-Site Scripting (XSS).
 */
function e(mixed $value): string 
{ 
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); 
}

/**
 * Formata valores monetários em centavos para a moeda brasileira (BRL / R$).
 */
function money(int $cents): string 
{ 
    return 'R$ ' . number_format($cents / 100, 2, ',', '.'); 
}

/**
 * Retorna uma URL interna com barra inicial normalizada.
 */
function url(string $path = ''): string 
{ 
    return '/' . ltrim($path, '/'); 
}

/**
 * Retorna o caminho de um arquivo estático em /assets/.
 */
function asset(string $path): string 
{ 
    return '/assets/' . ltrim($path, '/'); 
}

/**
 * [SEO & Performance] Gera a URL do asset com hash de modificação para Cache Busting.
 */
function versioned_asset(string $path): string
{
    $relative = ltrim($path, '/');
    $file = dirname(__DIR__) . '/public/assets/' . $relative;
    return asset($relative) . '?v=' . (is_file($file) ? (string) filemtime($file) : '1');
}

/**
 * [Core Web Vitals] Retorna GIF transparente leve para evitar CLS antes de imagens carregarem.
 */
function image_placeholder(): string 
{ 
    return 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs='; 
}

/**
 * Gera ou recupera o token anti-CSRF da sessão.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

/**
 * Valida o token CSRF de formulários POST.
 */
function verify_csrf(?string $retryPath = null): void
{
    $token = (string) ($_POST['_token'] ?? '');
    $expected = (string) ($_SESSION['csrf'] ?? '');
    if ($expected === '' || $token === '' || !hash_equals($expected, $token)) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        if ($retryPath !== null) {
            flash('error', 'O formulário expirou por inatividade e foi renovado. Preencha novamente para continuar.');
            redirect(safe_redirect_target($retryPath));
        }
        render('errors/status', [
            'code' => 403, 
            'title' => 'Acesso recusado', 
            'message' => 'A sessão do formulário expirou. Atualize a página e tente de novo.'
        ], 403);
        exit;
    }
}

/**
 * Armazena mensagem flash de notificação para a próxima requisição.
 */
function flash(string $type, string $message): void 
{ 
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message]; 
}

/**
 * Consome e limpa todas as mensagens flash pendentes na sessão.
 */
function consume_flashes(): array 
{ 
    $items = $_SESSION['flash'] ?? []; 
    unset($_SESSION['flash']); 
    return $items; 
}

/**
 * Executa redirecionamento HTTP 303 See Other seguro.
 */
function redirect(string $path): never 
{ 
    header('Location: ' . $path, true, 303); 
    exit; 
}

/**
 * Valida o destino de redirecionamento para prevenir vulnerabilidades de Open Redirect.
 */
function safe_redirect_target(string $path, string $fallback = '/'): string
{
    return str_starts_with($path, '/') && !str_starts_with($path, '//') && !str_contains($path, "\r") && !str_contains($path, "\n") ? $path : $fallback;
}

/**
 * Retorna os dados do usuário autenticado no momento.
 */
function current_user(): ?array
{
    global $store;
    $id = (string) ($_SESSION['user_id'] ?? '');
    return is_uuid($id) ? $store->userById($id) : null;
}

/**
 * Middleware que exige login para prosseguir.
 */
function require_auth(): array
{
    $user = current_user();
    if ($user === null) {
        $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '/minha-conta';
        flash('info', 'Entre na sua conta para continuar.');
        redirect('/entrar');
    }
    return $user;
}

/**
 * Middleware que restringe o acesso exclusivamente a administradores (RBAC).
 */
function require_admin(): array
{
    $user = require_auth();
    if ($user['role'] !== 'admin') {
        render('errors/status', [
            'code' => 403, 
            'title' => 'Área reservada', 
            'message' => 'Esta página é exclusiva para administradores da loja.'
        ], 403);
        exit;
    }
    return $user;
}

/**
 * Calcula os totais, contagem de itens e regras de frete da sacola de compras.
 */
function cart_details(): array
{
    global $store;
    $items = [];
    $subtotal = 0;
    foreach ($_SESSION['cart'] ?? [] as $productId => $quantity) {
        $product = $store->productById((string) $productId);
        if (!$product || !(bool) $product['active']) continue;
        $quantity = max(1, min((int) $quantity, (int) $product['stock']));
        $line = (int) $product['price_cents'] * $quantity;
        $items[] = ['product' => $product, 'quantity' => $quantity, 'line_cents' => $line];
        $subtotal += $line;
    }
    // Regra comercial: Frete grátis acima de R$ 150,00 ou R$ 14,90 para entregas menores
    $shipping = $subtotal === 0 || $subtotal >= 15000 ? 0 : 1490;
    return [
        'items' => $items, 
        'count' => array_sum(array_column($items, 'quantity')), 
        'subtotal_cents' => $subtotal, 
        'shipping_cents' => $shipping, 
        'total_cents' => $subtotal + $shipping
    ];
}

/**
 * Motor de renderização: carrega a view com os dados e empacota no layout principal.
 */
function render(string $view, array $data = [], int $status = 200): void
{
    global $nonce;
    http_response_code($status);
    extract($data, EXTR_SKIP);
    ob_start();
    require dirname(__DIR__) . '/views/' . $view . '.php';
    $content = (string) ob_get_clean();
    require dirname(__DIR__) . '/views/layout.php';
}

/**
 * Limpa e trunca strings recebidas via POST.
 */
function post_string(string $key, int $max = 180): string
{
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) return '';
    $value = $_POST[$key];
    return trim(function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max));
}

/**
 * [SEO On-Page] Converte títulos em slugs amigáveis para URLs canônicas.
 */
function slugify(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    return trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($value)) ?? '', '-');
}

/**
 * Validação do algoritmo de dígitos verificadores do CPF brasileiro.
 */
function valid_cpf(string $cpf): bool
{
    $cpf = preg_replace('/\D/', '', $cpf) ?? '';
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) return false;
    for ($digit = 9; $digit < 11; $digit++) {
        $sum = 0;
        for ($index = 0; $index < $digit; $index++) $sum += (int) $cpf[$index] * (($digit + 1) - $index);
        $check = (10 * $sum) % 11;
        if ($check === 10) $check = 0;
        if ($check !== (int) $cpf[$digit]) return false;
    }
    return true;
}

/**
 * [SEO Técnico] Obtém a URL base canônica do projeto para Open Graph e Sitemap.
 */
function request_base_url(): string
{
    $configured = rtrim(trim(getenv('APP_URL') ?: ''), '/');
    if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $configured)) return $configured;
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    if (!preg_match('/^[a-z0-9.-]+(?::\d+)?$/i', $host)) $host = 'localhost';
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https' ? 'https' : 'http';
    return $proto . '://' . $host;
}

/**
 * Controla limite de tentativas de login por sessão (máximo 5 em 10 minutos).
 */
function login_is_limited(): bool
{
    $attempts = array_filter($_SESSION['login_attempts'] ?? [], fn (int $time): bool => $time > time() - 600);
    $_SESSION['login_attempts'] = array_values($attempts);
    return count($attempts) >= 5;
}

function register_failed_login(): void 
{ 
    $_SESSION['login_attempts'][] = time(); 
}

/**
 * Detecta o IP real do cliente para fins de auditoria e rate limiting.
 */
function client_ip(): string
{
    $candidate = (string) ($_SERVER['HTTP_X_CLIENT_IP'] ?? '');
    if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_IP)) return $candidate;
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '0.0.0.0';
}

/**
 * Mascara o e-mail para exibição segura na tela do 2FA (ex: jo•••@exemplo.com).
 */
function masked_email(string $email): string
{
    [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
    $visible = substr($local, 0, min(2, strlen($local)));
    return $visible . str_repeat('•', max(3, strlen($local) - strlen($visible))) . '@' . $domain;
}

/**
 * Inicia o fluxo de verificação em duas etapas (2FA) via e-mail com HMAC-SHA256.
 */
function begin_two_factor(array $identity, string $target, int $resendCount = 0, string $purpose = 'login'): void
{
    global $mailer, $appKey;
    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $mailer->sendTwoFactorCode($identity, $code, 10);
    $registration = null;
    if ($purpose === 'registration') {
        $registration = [
            'name' => (string) $identity['name'],
            'email' => strtolower((string) $identity['email']),
            'password_hash' => (string) $identity['password_hash'],
            'role' => 'customer',
        ];
    }
    $_SESSION['two_factor'] = [
        'purpose' => $purpose,
        'user_id' => (string) ($identity['id'] ?? ''),
        'registration' => $registration,
        'code_hash' => hash_hmac('sha256', $code, $appKey),
        'expires_at' => time() + 600,
        'attempts' => 0,
        'sent_at' => time(),
        'resend_count' => $resendCount,
        'target' => safe_redirect_target($target, ($identity['role'] ?? 'customer') === 'admin' ? '/admin' : '/minha-conta'),
    ];
}

/**
 * Recupera os dados da identidade temporária aguardando confirmação do 2FA.
 */
function pending_two_factor_identity(array $pending): ?array
{
    global $store;
    if (($pending['purpose'] ?? 'login') === 'registration') {
        $registration = $pending['registration'] ?? null;
        return is_array($registration) ? $registration : null;
    }
    $user = $store->userById((string) ($pending['user_id'] ?? ''));
    return is_array($user) ? $user : null;
}

/**
 * Valida o código digitado com o hash HMAC armazenado em sessão.
 */
function verify_two_factor_code(string $code): bool
{
    global $appKey;
    $pending = $_SESSION['two_factor'] ?? null;
    if (!is_array($pending) || time() > (int) ($pending['expires_at'] ?? 0)) return false;
    return hash_equals((string) $pending['code_hash'], hash_hmac('sha256', $code, $appKey));
}

// ============================================================================
// 7. FLUXO DE RECUPERAÇÃO E REDEFINIÇÃO DE SENHA
// ============================================================================

/**
 * Gera código aleatório contendo todas as categorias de caracteres do teclado
 * (maiúsculas, minúsculas, números e símbolos especiais do teclado).
 */
function generate_keyboard_reset_code(int $length = 12): string
{
    $uppers = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $lowers = 'abcdefghijkmnopqrstuvwxyz';
    $digits = '23456789';
    $symbols = '!@#$%&*-_=+?';

    // Garante presença obrigatória de cada classe de caracteres do teclado
    $chars = [
        $uppers[random_int(0, strlen($uppers) - 1)],
        $lowers[random_int(0, strlen($lowers) - 1)],
        $digits[random_int(0, strlen($digits) - 1)],
        $symbols[random_int(0, strlen($symbols) - 1)],
    ];

    $allChars = $uppers . $lowers . $digits . $symbols;
    $allLen = strlen($allChars);

    while (count($chars) < $length) {
        $chars[] = $allChars[random_int(0, $allLen - 1)];
    }

    // Embaralha criptograficamente com Fisher-Yates
    for ($i = count($chars) - 1; $i > 0; $i--) {
        $j = random_int(0, $i);
        $tmp = $chars[$i];
        $chars[$i] = $chars[$j];
        $chars[$j] = $tmp;
    }

    return implode('', $chars);
}

/**
 * Retorna o caminho do arquivo de persistência atômica de redefinições de senha.
 */
function password_resets_file(): string
{
    global $runtimeDirectory;
    return $runtimeDirectory . '/password-resets.json';
}

/**
 * Armazena o código de redefinição de senha com HMAC-SHA256 e trava de concorrência.
 */
function store_password_reset_code(string $email, string $userId, string $code, int $expiresMinutes = 15): void
{
    global $appKey;
    $path = password_resets_file();
    $handle = fopen($path, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Não foi possível registrar o código de redefinição de senha.');
    }

    flock($handle, LOCK_EX);
    $raw = stream_get_contents($handle) ?: '';
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = [];

    $now = time();
    foreach ($data as $k => $item) {
        if (!is_array($item) || ($item['expires_at'] ?? 0) < $now) {
            unset($data[$k]);
        }
    }

    $emailKey = strtolower(trim($email));
    $data[$emailKey] = [
        'user_id' => $userId,
        'email' => $emailKey,
        'code_hash' => hash_hmac('sha256', $code, $appKey),
        'expires_at' => $now + ($expiresMinutes * 60),
        'attempts' => 0,
        'created_at' => $now,
    ];

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

/**
 * Valida o código de redefinição informado pelo usuário com limite de tentativas.
 */
function verify_password_reset_code(string $email, string $code): array
{
    global $appKey;
    $path = password_resets_file();
    if (!is_file($path)) {
        return ['success' => false, 'error' => 'expired'];
    }

    $handle = fopen($path, 'c+');
    if ($handle === false) {
        return ['success' => false, 'error' => 'unavailable'];
    }

    flock($handle, LOCK_EX);
    $raw = stream_get_contents($handle) ?: '';
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = [];

    $now = time();
    $emailKey = strtolower(trim($email));
    $entry = $data[$emailKey] ?? null;

    if (!is_array($entry) || ($entry['expires_at'] ?? 0) < $now) {
        if (isset($data[$emailKey])) unset($data[$emailKey]);
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
        return ['success' => false, 'error' => 'expired'];
    }

    $isValid = hash_equals((string) $entry['code_hash'], hash_hmac('sha256', trim($code), $appKey));

    if (!$isValid) {
        $attempts = (int) ($entry['attempts'] ?? 0) + 1;
        $entry['attempts'] = $attempts;
        $remaining = 5 - $attempts;
        if ($remaining <= 0) {
            unset($data[$emailKey]);
        } else {
            $data[$emailKey] = $entry;
        }
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        return ['success' => false, 'error' => 'invalid', 'remaining' => max(0, $remaining)];
    }

    // Sucesso: remove o código consumido
    unset($data[$emailKey]);
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return ['success' => true, 'entry' => $entry];
}
