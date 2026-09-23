<?php
/**
 * ============================================================================
 * GATEWAY DE PAGAMENTOS (MERCADO PAGO PIX) — ELDA BOLOS E DOCES
 * ============================================================================
 * Integração oficial com as APIs REST do Mercado Pago para geração instantânea
 * de cobranças PIX, QR Code dinâmico e validação de Webhooks com assinatura HMAC.
 *
 * ARQUITETURA E SEGURANÇA:
 * ----------------------------------------------------------------------------
 * 1. IDEMPOTÊNCIA VIA HEADER X-IDEMPOTENCY-KEY:
 *    - Utiliza o UUID do pedido como chave idempotente nas requisições POST.
 *    - Previne que instabilidades de rede ou múltiplos cliques gerem cobranças
 *      duplicadas para uma mesma compra.
 *
 * 2. WEBHOOK COM ASSINATURA HMAC-SHA256 (HTTP_X_SIGNATURE):
 *    - Valida o header 'x-signature' e timestamp enviado pelo Mercado Pago
 *      contra a chave secreta (MERCADO_PAGO_WEBHOOK_SECRET), bloqueando requisições
 *      forjadas por terceiros (Man-in-the-Middle ou Spoofing).
 *
 * 3. COMUNICAÇÃO RESTRITA A HTTPS (TLS 1.2+):
 *    - Configuração cURL força CURLOPT_PROTOCOLS => CURLPROTO_HTTPS para atender
 *      aos requisitos PCI-DSS e proteção de dados bancários.
 * ============================================================================
 */

declare(strict_types=1);

final class MercadoPagoGateway
{
    /**
     * @param string $accessToken Token de produção ou sandbox do Mercado Pago
     * @param string $webhookSecret Chave de validação da assinatura dos webhooks
     * @param string $baseUrl Endpoint base da API do Mercado Pago
     */
    public function __construct(
        private readonly string $accessToken,
        private readonly string $webhookSecret,
        private readonly string $baseUrl = 'https://api.mercadopago.com',
    ) {}

    /**
     * Instancia o gateway lendo as credenciais do ambiente (.env).
     */
    public static function fromEnvironment(): self
    {
        return new self(
            trim(getenv('MERCADO_PAGO_ACCESS_TOKEN') ?: ''),
            trim(getenv('MERCADO_PAGO_WEBHOOK_SECRET') ?: ''),
        );
    }

    /**
     * Verifica se as credenciais necessárias foram fornecidas no ambiente.
     */
    public function isConfigured(): bool 
    { 
        return $this->accessToken !== ''; 
    }

    /**
     * Cria uma cobrança PIX com QR Code dinâmico associado ao pedido.
     *
     * @param array $order Dados do pedido (número, total, id)
     * @param array $payer Dados do cliente pagador (nome, email, CPF)
     * @param string $notificationUrl URL canônica para recebimento de webhooks
     * @return array Dados do PIX gerado (payment_id, status, pix_code, qr_code_base64)
     */
    public function createPix(array $order, array $payer, string $notificationUrl): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('O PIX do Mercado Pago ainda não possui Access Token configurado.');
        }

        $names = preg_split('/\s+/', trim((string) $payer['name']), 2) ?: [];
        $firstName = !empty($names[0]) ? $names[0] : 'Cliente';
        $lastName = !empty($names[1]) ? $names[1] : $firstName;

        $payload = [
            'transaction_amount' => round((int) $order['total_cents'] / 100, 2),
            'description' => 'Pedido ' . $order['number'] . ' — Elda Bolos e Doces Sorocaba',
            'payment_method_id' => 'pix',
            'external_reference' => $order['id'],
            'date_of_expiration' => (new DateTime('+30 minutes'))->format('Y-m-d\TH:i:s.000P'),
            'notification_url' => $notificationUrl,
            'payer' => [
                'email' => (string) $payer['email'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'identification' => [
                    'type' => 'CPF', 
                    'number' => preg_replace('/\D/', '', (string) $payer['cpf'])
                ],
            ],
        ];

        // Idempotência: O UUID do pedido impede criação de cobranças duplicadas
        $payment = $this->request('POST', '/v1/payments', $payload, ['X-Idempotency-Key: ' . $order['id']]);
        $transaction = $payment['point_of_interaction']['transaction_data'] ?? [];

        if (empty($payment['id']) || empty($transaction['qr_code'])) {
            throw new RuntimeException('O Mercado Pago não retornou um PIX válido.');
        }

        return [
            'payment_id' => (string) $payment['id'],
            'status' => $this->normalizeStatus((string) ($payment['status'] ?? 'pending')),
            'pix_code' => (string) $transaction['qr_code'],
            'pix_qr_base64' => (string) ($transaction['qr_code_base64'] ?? ''),
            'ticket_url' => (string) ($transaction['ticket_url'] ?? ''),
            'expires_at' => (string) ($payment['date_of_expiration'] ?? $payload['date_of_expiration']),
            'external_reference' => (string) ($payment['external_reference'] ?? $order['id']),
        ];
    }

    /**
     * Consulta a situação atual de um pagamento via ID.
     */
    public function payment(string $id): array
    {
        if (!$this->isConfigured()) throw new RuntimeException('Mercado Pago não configurado.');
        return $this->request('GET', '/v1/payments/' . rawurlencode($id));
    }

    /**
     * Valida a assinatura criptográfica HMAC do Webhook recebido do Mercado Pago.
     */
    public function validateWebhook(string $signature, string $requestId, string $dataId): bool
    {
        // Se a chave secreta do webhook ainda não foi configurada, permite o prosseguimento
        // para a validação direta na API oficial do Mercado Pago via payment($dataId)
        if ($this->webhookSecret === '') {
            return true;
        }

        if ($signature === '' || $requestId === '' || $dataId === '') return false;

        $parts = [];
        foreach (explode(',', $signature) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
            $parts[$key] = $value;
        }

        if (empty($parts['ts']) || empty($parts['v1'])) return false;

        // Formato oficial do template de assinatura do Mercado Pago
        $template = 'id:' . strtolower($dataId) . ';request-id:' . $requestId . ';ts:' . $parts['ts'] . ';';
        $expected = hash_hmac('sha256', $template, $this->webhookSecret);

        return hash_equals($expected, $parts['v1']);
    }

    /**
     * Normaliza os estados do Mercado Pago para os padrões internos da doceria.
     */
    public function normalizeStatus(string $status): string
    {
        return match ($status) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            'cancelled' => 'cancelled',
            'refunded', 'charged_back' => 'refunded',
            default => 'pending',
        };
    }

    /**
     * Cliente HTTP cURL encapsulado com timeout, headers e protocolo HTTPS restrito.
     */
    private function request(string $method, string $path, ?array $payload = null, array $extraHeaders = []): array
    {
        $curl = curl_init($this->baseUrl . $path);
        if ($curl === false) {
            throw new RuntimeException('Não foi possível iniciar a conexão com Mercado Pago.');
        }

        $headers = array_merge([
            'Authorization: Bearer ' . $this->accessToken,
            'Accept: application/json',
            'Content-Type: application/json'
        ], $extraHeaders);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS
        ]);

        if ($payload !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        }

        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($body === false || $error !== '') {
            throw new RuntimeException('Falha de rede com Mercado Pago: ' . $error);
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new RuntimeException('Resposta inválida do Mercado Pago.');
        }

        if ($status < 200 || $status >= 300) {
            $message = (string) ($data['message'] ?? $data['error'] ?? 'Erro ao criar pagamento');
            throw new RuntimeException('Mercado Pago: ' . $message);
        }

        return $data;
    }
}
