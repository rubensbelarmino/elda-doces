<?php
/**
 * ============================================================================
 * CAMADA DE DADOS E PERSISTÊNCIA (STORE REPOSITORY) — ELDA BOLOS E DOCES
 * ============================================================================
 * Abstração agnóstica de persistência com suporte duplo:
 * 1. JsonStore: Armazenamento local leve em JSON com controle de lock exclusivo
 *    (flock LOCK_EX) para desenvolvimento ágil e sem dependências externas.
 * 2. MysqlStore: Persistência escalável em MySQL 8.4 com transações ACID (PDO),
 *    locks de migração distribuídos e prepared statements.
 *
 * TÉCNICAS DE SEGURANÇA E ARQUITETURA:
 * ----------------------------------------------------------------------------
 * 1. IDENTIFICADORES UUIDv4:
 *    - Geração de identificadores pseudoaleatórios com entropia criptográfica
 *      (random_bytes(16)), prevenindo ataques de enumeração e scraping da base.
 *
 * 2. HASHING ROBUSTO DE SENHAS COM BCRYPT (COST 12):
 *    - Senhas salvas com password_hash() utilizando o algoritmo Bcrypt e salt
 *      automático, protegendo as credenciais contra ataques de Rainbow Tables.
 *
 * 3. PREVENÇÃO DE SQL INJECTION:
 *    - Todas as consultas utilizam prepared statements com vinculação estrita de
 *      parâmetros (? / PDO::prepare), impedindo injeção de comandos SQL maliciosos.
 * ============================================================================
 */

declare(strict_types=1);

/**
 * Gera um identificador único universal versão 4 (UUIDv4) RFC 4122.
 */
function uuid_v4(): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
}

/**
 * Valida o formato canônico de um UUIDv4.
 */
function is_uuid(string $value): bool
{
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
}

/**
 * Cria hash seguro de senha utilizando Bcrypt com fator de custo 12.
 */
function bcrypt_hash(string $password): string
{
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    if ($hash === false) throw new RuntimeException('Não foi possível proteger a senha.');
    return $hash;
}

/**
 * Contrato de repositório para acesso aos dados da doceria.
 */
interface Store
{
    public function products(bool $includeInactive = false): array;
    public function productById(string $id): ?array;
    public function productBySlug(string $slug): ?array;
    public function saveProduct(array $product): string;
    public function userByEmail(string $email): ?array;
    public function userById(string $id): ?array;
    public function updateUserEmail(string $id, string $email): void;
    public function updateUserPassword(string $id, string $passwordHash): void;
    public function createUser(string $name, string $email, string $passwordHash, string $role = 'customer'): string;
    public function deleteUserByEmail(string $email): bool;
    public function createOrder(string $userId, array $customer, array $items, int $totalCents): array;
    public function orderById(string $id): ?array;
    public function orderByPaymentId(string $paymentId): ?array;
    public function orders(?string $userId = null): array;
    public function updateOrderStatus(string $id, string $status): void;
    public function updateOrderPayment(string $id, string $paymentStatus, ?string $paymentId, array $paymentData = []): void;
    public function dashboard(): array;
}

/**
 * Implementação em JSON transacional para desenvolvimento local.
 */
final class JsonStore implements Store
{
    public function __construct(private readonly string $path)
    {
        if (!is_file($path)) $this->write($this->seedData());
        else $this->mutate(function (array &$data): null { $this->migrateData($data); return null; });
    }

    public function products(bool $includeInactive = false): array
    {
        $products = $this->read()['products'];
        if (!$includeInactive) $products = array_values(array_filter($products, fn (array $p): bool => (bool) $p['active']));
        usort($products, fn (array $a, array $b): int => ($b['featured'] <=> $a['featured']) ?: strcasecmp($a['name'], $b['name']));
        return $products;
    }

    public function productById(string $id): ?array
    {
        foreach ($this->read()['products'] as $product) if ($product['id'] === $id) return $product;
        return null;
    }

    public function productBySlug(string $slug): ?array
    {
        foreach ($this->read()['products'] as $product) if ($product['slug'] === $slug) return $product;
        return null;
    }

    public function saveProduct(array $product): string
    {
        return $this->mutate(function (array &$data) use ($product): string {
            $id = (string) ($product['id'] ?? '');
            if (!is_uuid($id)) {
                $id = uuid_v4();
                $product['id'] = $id;
                $data['products'][] = $product;
            } else {
                $found = false;
                foreach ($data['products'] as $index => $current) {
                    if ($current['id'] === $id) { $data['products'][$index] = array_merge($current, $product); $found = true; break; }
                }
                if (!$found) throw new RuntimeException('Produto não encontrado.');
            }
            return $id;
        });
    }

    public function userByEmail(string $email): ?array
    {
        foreach ($this->read()['users'] as $user) if (strtolower($user['email']) === strtolower($email)) return $user;
        return null;
    }

    public function userById(string $id): ?array
    {
        foreach ($this->read()['users'] as $user) if ($user['id'] === $id) return $user;
        return null;
    }

    public function updateUserEmail(string $id, string $email): void
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('E-mail inválido.');
        $this->mutate(function (array &$data) use ($id, $email): null {
            foreach ($data['users'] as $user) if ($user['email'] !== $email && strtolower($user['email']) === $email) throw new DomainException('E-mail já cadastrado.');
            foreach ($data['users'] as &$user) if ($user['id'] === $id) { $user['email'] = $email; return null; }
            throw new RuntimeException('Usuário não encontrado.');
        });
    }

    public function updateUserPassword(string $id, string $passwordHash): void
    {
        $this->mutate(function (array &$data) use ($id, $passwordHash): null {
            foreach ($data['users'] as &$user) {
                if ($user['id'] === $id) {
                    $user['password_hash'] = $passwordHash;
                    return null;
                }
            }
            throw new RuntimeException('Usuário não encontrado.');
        });
    }

    public function createUser(string $name, string $email, string $passwordHash, string $role = 'customer'): string
    {
        return $this->mutate(function (array &$data) use ($name, $email, $passwordHash, $role): string {
            foreach ($data['users'] as $user) if (strtolower($user['email']) === strtolower($email)) throw new DomainException('E-mail já cadastrado.');
            $id = uuid_v4();
            $data['users'][] = ['id' => $id, 'name' => $name, 'email' => strtolower($email), 'password_hash' => $passwordHash, 'role' => $role, 'created_at' => date(DATE_ATOM)];
            return $id;
        });
    }

    public function deleteUserByEmail(string $email): bool
    {
        return $this->mutate(function (array &$data) use ($email): bool {
            $ids = array_values(array_map(fn (array $u): string => $u['id'], array_filter($data['users'], fn (array $u): bool => strtolower($u['email']) === strtolower($email))));
            if ($ids === []) return false;
            $data['users'] = array_values(array_filter($data['users'], fn (array $u): bool => !in_array($u['id'], $ids, true)));
            $data['orders'] = array_values(array_filter($data['orders'], fn (array $o): bool => !in_array($o['user_id'], $ids, true)));
            return true;
        });
    }

    public function createOrder(string $userId, array $customer, array $items, int $totalCents): array
    {
        return $this->mutate(function (array &$data) use ($userId, $customer, $items, $totalCents): array {
            $data['meta']['order_sequence'] = (int) ($data['meta']['order_sequence'] ?? count($data['orders'])) + 1;
            $id = uuid_v4();
            $order = [
                'id' => $id,
                'number' => 'DA' . date('ymd') . str_pad((string) $data['meta']['order_sequence'], 4, '0', STR_PAD_LEFT),
                'user_id' => $userId, 'customer' => $customer, 'items' => $items, 'total_cents' => $totalCents,
                'status' => 'received', 'payment_status' => 'pending', 'payment_id' => null,
                'pix_code' => null, 'pix_qr_base64' => null, 'payment_expires_at' => null,
                'created_at' => date(DATE_ATOM),
            ];
            $data['orders'][] = $order;
            return $order;
        });
    }

    public function orderById(string $id): ?array
    {
        foreach ($this->read()['orders'] as $order) if ($order['id'] === $id) return $order;
        return null;
    }

    public function orderByPaymentId(string $paymentId): ?array
    {
        foreach ($this->read()['orders'] as $order) if ((string) ($order['payment_id'] ?? '') === $paymentId) return $order;
        return null;
    }

    public function orders(?string $userId = null): array
    {
        $orders = $this->read()['orders'];
        if ($userId !== null) $orders = array_values(array_filter($orders, fn (array $order): bool => $order['user_id'] === $userId));
        usort($orders, fn (array $a, array $b): int => strcmp($b['created_at'], $a['created_at']));
        return $orders;
    }

    public function updateOrderStatus(string $id, string $status): void
    {
        $this->mutate(function (array &$data) use ($id, $status): null { foreach ($data['orders'] as &$order) if ($order['id'] === $id) $order['status'] = $status; return null; });
    }

    public function updateOrderPayment(string $id, string $paymentStatus, ?string $paymentId, array $paymentData = []): void
    {
        $this->mutate(function (array &$data) use ($id, $paymentStatus, $paymentId, $paymentData): null {
            foreach ($data['orders'] as &$order) {
                if ($order['id'] === $id) {
                    $order['payment_status'] = $paymentStatus;
                    $order['payment_id'] = $paymentId ?? $order['payment_id'];
                    if (isset($paymentData['pix_code'])) $order['pix_code'] = $paymentData['pix_code'];
                    if (isset($paymentData['pix_qr_base64'])) $order['pix_qr_base64'] = $paymentData['pix_qr_base64'];
                    if (isset($paymentData['payment_expires_at'])) $order['payment_expires_at'] = $paymentData['payment_expires_at'];
                    return null;
                }
            }
            return null;
        });
    }

    public function dashboard(): array
    {
        $data = $this->read();
        $approvedOrders = array_filter($data['orders'], fn (array $o): bool => ($o['payment_status'] ?? '') === 'approved' && ($o['status'] ?? '') !== 'cancelled');
        return [
            'revenue_cents' => array_sum(array_column($approvedOrders, 'total_cents')),
            'orders' => count($data['orders']),
            'customers' => count(array_filter($data['users'], fn (array $u): bool => ($u['role'] ?? 'customer') === 'customer')),
            'products' => count(array_filter($data['products'], fn (array $p): bool => (bool) $p['active'])),
        ];
    }

    private function read(): array
    {
        $handle = fopen($this->path, 'r');
        if ($handle === false) throw new RuntimeException('Não foi possível ler os dados da loja.');
        flock($handle, LOCK_SH);
        $contents = stream_get_contents($handle) ?: '{}';
        flock($handle, LOCK_UN);
        fclose($handle);
        $data = json_decode($contents, true);
        if (!is_array($data)) throw new RuntimeException('Arquivo de dados corrompido.');
        return $data;
    }

    private function write(array $data): void
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $tmp = $this->path . '.' . bin2hex(random_bytes(6)) . '.tmp';
        file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
        rename($tmp, $this->path);
        @chmod($this->path, 0660);
    }

    private function mutate(callable $callback): mixed
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $handle = fopen($this->path, 'c+');
        if ($handle === false) throw new RuntimeException('Não foi possível abrir os dados para gravação.');
        flock($handle, LOCK_EX);
        $contents = stream_get_contents($handle) ?: '{}';
        $data = json_decode($contents, true);
        if (!is_array($data)) $data = $this->seedData();
        $result = $callback($data);
        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
        @chmod($this->path, 0660);
        return $result;
    }

    private function migrateData(array &$data): void
    {
        $data['schema_version'] ??= 1;
        $data['products'] ??= [];
        $data['users'] ??= [];
        $data['orders'] ??= [];
        $data['meta'] ??= [];
        $data['meta']['order_sequence'] ??= count($data['orders']);
        $data['schema_version'] = 2;
    }

    private function seedData(): array
    {
        $rows = [
            ['Caixa Afeto', 'caixa-afeto', 'Uma seleção de 12 brigadeiros de chocolate belga, pistache e flor de sal.', 5890, 6490, 'Brigadeiros', 'chocolate.jpg', 18, 1, 1, '12 unidades'],
            ['Tartelette Lumière', 'tartelette-lumiere', 'Massa amanteigada, creme de baunilha e morangos frescos selecionados.', 1890, null, 'Tortinhas', 'morango.jpg', 24, 1, 1, '1 unidade'],
            ['Macarons Jardim', 'macarons-jardim', 'Seis macarons delicados de pistache com ganache branca e praliné.', 4690, null, 'Macarons', 'pistache.jpg', 12, 1, 1, '6 unidades'],
            ['Bolo Caramelo Dourado', 'bolo-caramelo-dourado', 'Camadas de cacau, creme aveludado e caramelo artesanal com castanhas.', 12990, 13990, 'Bolos', 'caramelo.jpg', 8, 1, 1, 'Serve 8 pessoas'],
            ['Trufa Cacau Intenso', 'trufa-cacau-intenso', 'Ganache 70% cacau envolta em uma camada fina de chocolate e nibs.', 990, null, 'Brigadeiros', 'chocolate.jpg', 40, 0, 1, '1 unidade'],
            ['Torta Bosque Rosa', 'torta-bosque-rosa', 'Torta cremosa de frutas vermelhas, baunilha e crocante de amêndoas.', 11990, null, 'Bolos', 'morango.jpg', 6, 1, 1, 'Serve 8 pessoas'],
            ['Nuvem de Pistache', 'nuvem-de-pistache', 'Entremet leve de pistache, chocolate branco e um toque de limão-siciliano.', 2490, null, 'Tortinhas', 'pistache.jpg', 15, 0, 1, '1 unidade'],
            ['Fatia Caramelo & Cacau', 'fatia-caramelo-cacau', 'Fatia generosa de bolo de cacau com caramelo salgado e praliné.', 2790, null, 'Bolos', 'caramelo.jpg', 20, 0, 1, '1 fatia'],
        ];
        $products = array_map(fn (array $row): array => array_combine(['id', 'name', 'slug', 'description', 'price_cents', 'compare_cents', 'category', 'image', 'stock', 'featured', 'active', 'portion'], array_merge([uuid_v4()], $row)), $rows);
        return [
            'schema_version' => 2,
            'meta' => ['order_sequence' => 0],
            'products' => $products,
            'users' => [
                ['id' => uuid_v4(), 'name' => 'Admin Doce', 'email' => 'admin@doceatelier.com', 'password_hash' => bcrypt_hash('Doce@2026'), 'role' => 'admin', 'created_at' => date(DATE_ATOM)],
                ['id' => uuid_v4(), 'name' => 'Marina Cliente', 'email' => 'cliente@doceatelier.com', 'password_hash' => bcrypt_hash('Doce@2026'), 'role' => 'customer', 'created_at' => date(DATE_ATOM)],
            ],
            'orders' => []
        ];
    }
}

/**
 * Implementação de produção com banco relacional MySQL 8.4 e PDO.
 */
final class MysqlStore implements Store
{
    private PDO $pdo;

    public function __construct(string $dsn, string $user, string $password)
    {
        $this->pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        $this->migrate();
    }

    public function products(bool $includeInactive = false): array
    {
        return $this->pdo->query('SELECT * FROM products' . ($includeInactive ? '' : ' WHERE active = 1') . ' ORDER BY featured DESC, name ASC')->fetchAll();
    }

    public function productById(string $id): ?array
    {
        return $this->one('SELECT * FROM products WHERE id = ?', [$id]);
    }

    public function productBySlug(string $slug): ?array
    {
        return $this->one('SELECT * FROM products WHERE slug = ?', [$slug]);
    }

    public function saveProduct(array $product): string
    {
        $fields = ['name', 'slug', 'description', 'price_cents', 'compare_cents', 'category', 'image', 'stock', 'featured', 'active', 'portion'];
        $id = (string) ($product['id'] ?? '');
        if (!is_uuid($id)) {
            $id = uuid_v4();
            $stmt = $this->pdo->prepare('INSERT INTO products (id,' . implode(',', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields) + 1, '?')) . ')');
            $stmt->execute(array_merge([$id], array_map(fn (string $f): mixed => $product[$f] ?? null, $fields)));
        } else {
            $stmt = $this->pdo->prepare('UPDATE products SET ' . implode(', ', array_map(fn (string $f): string => "$f = ?", $fields)) . ' WHERE id = ?');
            $stmt->execute(array_merge(array_map(fn (string $f): mixed => $product[$f] ?? null, $fields), [$id]));
        }
        return $id;
    }

    public function userByEmail(string $email): ?array
    {
        return $this->one('SELECT * FROM users WHERE email = ?', [strtolower($email)]);
    }

    public function userById(string $id): ?array
    {
        return $this->one('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function updateUserEmail(string $id, string $email): void
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('E-mail inválido.');
        $this->pdo->prepare('UPDATE users SET email=? WHERE id=?')->execute([$email, $id]);
    }

    public function updateUserPassword(string $id, string $passwordHash): void
    {
        $this->pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$passwordHash, $id]);
    }

    public function createUser(string $name, string $email, string $passwordHash, string $role = 'customer'): string
    {
        $id = uuid_v4();
        $this->pdo->prepare('INSERT INTO users (id,name,email,password_hash,role,created_at) VALUES (?,?,?,?,?,NOW())')->execute([$id, $name, strtolower($email), $passwordHash, $role]);
        return $id;
    }

    public function deleteUserByEmail(string $email): bool
    {
        $user = $this->userByEmail($email);
        if (!$user) return false;
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('DELETE oi FROM order_items oi INNER JOIN orders o ON o.id = oi.order_id WHERE o.user_id = ?')->execute([$user['id']]);
            $this->pdo->prepare('DELETE FROM orders WHERE user_id = ?')->execute([$user['id']]);
            $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$user['id']]);
            $this->pdo->commit();
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function createOrder(string $userId, array $customer, array $items, int $totalCents): array
    {
        $id = uuid_v4();
        $number = 'DA' . date('ymd') . strtoupper(substr(str_replace('-', '', $id), 0, 8));
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('INSERT INTO orders (id,number,user_id,customer_json,total_cents,status,payment_status,created_at) VALUES (?,?,?,?,?,?,?,NOW())')->execute([$id, $number, $userId, json_encode($customer, JSON_UNESCAPED_UNICODE), $totalCents, 'received', 'pending']);
            $stmt = $this->pdo->prepare('INSERT INTO order_items (id,order_id,product_id,name,quantity,unit_cents) VALUES (?,?,?,?,?,?)');
            foreach ($items as $item) {
                $stmt->execute([uuid_v4(), $id, $item['product_id'], $item['name'], $item['quantity'], $item['unit_cents']]);
            }
            $this->pdo->commit();
            return $this->orderById($id) ?? throw new RuntimeException('Pedido não encontrado após criação.');
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function orderById(string $id): ?array
    {
        $orders = $this->hydrateOrders($this->all('SELECT * FROM orders WHERE id = ?', [$id]));
        return $orders[0] ?? null;
    }

    public function orderByPaymentId(string $paymentId): ?array
    {
        $orders = $this->hydrateOrders($this->all('SELECT * FROM orders WHERE payment_id = ?', [$paymentId]));
        return $orders[0] ?? null;
    }

    public function orders(?string $userId = null): array
    {
        return $this->hydrateOrders($this->all('SELECT * FROM orders' . ($userId === null ? '' : ' WHERE user_id = ?') . ' ORDER BY created_at DESC', $userId === null ? [] : [$userId]));
    }

    public function updateOrderStatus(string $id, string $status): void
    {
        $this->pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
    }

    public function updateOrderPayment(string $id, string $paymentStatus, ?string $paymentId, array $paymentData = []): void
    {
        $expiresAt = $paymentData['payment_expires_at'] ?? null;
        if (is_string($expiresAt) && $expiresAt !== '') $expiresAt = date('Y-m-d H:i:s', strtotime($expiresAt));
        $this->pdo->prepare('UPDATE orders SET payment_status=?,payment_id=?,pix_code=COALESCE(?,pix_code),pix_qr_base64=COALESCE(?,pix_qr_base64),payment_expires_at=COALESCE(?,payment_expires_at) WHERE id=?')->execute([$paymentStatus, $paymentId, $paymentData['pix_code'] ?? null, $paymentData['pix_qr_base64'] ?? null, $expiresAt, $id]);
    }

    public function dashboard(): array
    {
        return [
            'revenue_cents' => (int) $this->pdo->query("SELECT COALESCE(SUM(total_cents),0) FROM orders WHERE payment_status='approved' AND status<>'cancelled'")->fetchColumn(),
            'orders' => (int) $this->pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'customers' => (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
            'products' => (int) $this->pdo->query('SELECT COUNT(*) FROM products WHERE active=1')->fetchColumn()
        ];
    }

    private function one(string $sql, array $params): ?array
    {
        $rows = $this->all($sql, $params);
        return $rows[0] ?? null;
    }

    private function all(string $sql, array $params): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function hydrateOrders(array $orders): array
    {
        $stmt = $this->pdo->prepare('SELECT product_id,name,quantity,unit_cents FROM order_items WHERE order_id=?');
        foreach ($orders as &$order) {
            $order['customer'] = json_decode($order['customer_json'], true) ?: [];
            $stmt->execute([$order['id']]);
            $order['items'] = $stmt->fetchAll();
        }
        return $orders;
    }

    private function migrate(): void
    {
        $locked = (int) $this->pdo->query("SELECT GET_LOCK('doce_atelier_migrate',15)")->fetchColumn() === 1;
        if (!$locked) throw new RuntimeException('Não foi possível obter o lock de migração MySQL.');
        try {
            $schema = file_get_contents(dirname(__DIR__) . '/database/schema-v2.sql');
            if ($schema === false) throw new RuntimeException('Schema MySQL não encontrado.');
            $this->pdo->exec($schema);
            if ((int) $this->pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() === 0) {
                $seedPath = dirname(__DIR__) . '/storage/seed-' . getmypid() . '.tmp.json';
                $seed = new JsonStore($seedPath);
                foreach ($seed->products(true) as $product) {
                    unset($product['id']);
                    $this->saveProduct($product);
                }
                @unlink($seedPath);
            }
            if ((int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
                $this->createUser('Admin Doce', 'admin@doceatelier.com', bcrypt_hash('Doce@2026'), 'admin');
                $this->createUser('Marina Cliente', 'cliente@doceatelier.com', bcrypt_hash('Doce@2026'));
            }
        } finally {
            $this->pdo->query("SELECT RELEASE_LOCK('doce_atelier_migrate')");
        }
    }
}
