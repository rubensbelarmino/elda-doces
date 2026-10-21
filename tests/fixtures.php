<?php

declare(strict_types=1);

// This file is loaded only by the isolated controller copy, never by the application.
if (!defined('TEST_RUNTIME') || !defined('TEST_ROOT')) {
    throw new RuntimeException('Run php tests/run.php instead.');
}
require TEST_RUNTIME . '/helpers.php';
require TEST_ROOT . '/src/MercadoPagoGateway.php';

$testState = json_decode(file_get_contents(TEST_RUNTIME . '/state.json'), true, 512, JSON_THROW_ON_ERROR);
$_SESSION = ['csrf' => 'fixture-csrf', 'cart' => ['33333333-3333-4333-8333-333333333333' => 1]];
if ($testState['role'] !== null) {
    $_SESSION['user_id'] = '22222222-2222-4222-8222-222222222222';
}
register_shutdown_function(function (): void {
    global $testState;
    $testState['flash'] = $_SESSION['flash'] ?? [];
    $testState['upload'] = isset($_FILES['image_file']) ? [
        'error' => $_FILES['image_file']['error'], 'size' => $_FILES['image_file']['size'],
    ] : null;
    file_put_contents(TEST_RUNTIME . '/state.json', json_encode($testState, JSON_THROW_ON_ERROR));
});

function record_event(string $name, mixed ...$arguments): void
{
    global $testState;
    $testState['events'][] = [$name, ...$arguments];
}

final class FixtureStore
{
    public function userById(string $id): ?array
    {
        global $testState;
        return $testState['role'] === null ? null : [
            'id' => $id, 'name' => 'Cliente Ficticio', 'email' => 'fixture@example.invalid', 'role' => $testState['role'],
        ];
    }

    public function orderById(string $id): ?array
    {
        global $testState;
        record_event('orderById', $id);
        return ($testState['order']['id'] ?? null) === $id ? $testState['order'] : null;
    }

    public function orderByPaymentId(string $id): ?array
    {
        global $testState;
        record_event('orderByPaymentId', $id);
        return $testState['order'];
    }

    public function updateOrderPayment(string $id, string $status, ?string $paymentId, array $details = []): void
    {
        record_event('updateOrderPayment', $id, $status, $paymentId, $details);
    }

    public function userDeliveryAddress(string $id): ?array
    {
        global $testState;
        record_event('userDeliveryAddress', $id);
        return $testState['address'];
    }

    public function saveUserDeliveryAddress(string $id, array $address): void
    {
        global $testState;
        record_event('saveUserDeliveryAddress', $id, $address);
        $testState['address'] = $address;
    }

    public function createOrder(string $userId, array $customer, array $items, int $total): array
    {
        global $testState;
        record_event('createOrder', $customer);
        $order = ['id' => '11111111-1111-4111-8111-111111111111', 'number' => 'TEST123',
            'user_id' => $userId, 'customer' => $customer, 'items' => $items, 'total_cents' => $total];
        $testState['orders'][] = $order;
        return $order;
    }

    public function productById(string $id): array
    {
        return ['id' => $id, 'name' => 'Produto Ficticio', 'price_cents' => 2000,
            'stock' => 10, 'active' => 1, 'image' => 'fixture.png'];
    }

    public function saveProduct(array $product): void
    {
        global $testState;
        record_event('saveProduct', $product);
        $testState['products'][] = $product;
    }
}

final class FixtureGateway
{
    private MercadoPagoGateway $validator;

    public function __construct(string $secret)
    {
        // Only validation/status normalization are delegated. No real request method is reachable.
        $this->validator = new MercadoPagoGateway('', $secret, 'http://127.0.0.1:1');
    }

    public function validateWebhook(string $signature, string $requestId, string $dataId): bool
    {
        record_event('validateWebhook', $signature, $requestId, $dataId);
        return $this->validator->validateWebhook($signature, $requestId, $dataId);
    }

    public function payment(string $id): array
    {
        global $testState;
        record_event('payment', $id);
        if ($testState['api_error']) {
            throw new RuntimeException('Simulated API failure; no network call.');
        }
        return $testState['payment'];
    }

    public function normalizeStatus(string $status): string
    {
        return $this->validator->normalizeStatus($status);
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function createPix(array $order, array $payer, string $notificationUrl): array
    {
        record_event('createPix', $payer, $order, $notificationUrl);
        return ['payment_id' => '123456', 'status' => 'pending', 'pix_code' => 'SYNTHETIC-NOT-A-PIX',
            'pix_qr_base64' => '', 'expires_at' => '2030-01-01T00:00:00Z'];
    }
}

function render(string $view, array $data = [], int $status = 200): void
{
    http_response_code($status);
    if ($view === 'checkout') {
        extract($data, EXTR_SKIP);
        require TEST_ROOT . '/views/checkout.php';
        return;
    }
    header('Content-Type: application/json');
    echo json_encode(['view' => $view, 'data' => $data], JSON_THROW_ON_ERROR);
}

function request_base_url(): string
{
    return 'http://127.0.0.1';
}

$store = new FixtureStore();
$mercadoPago = new FixtureGateway($testState['secret']);
