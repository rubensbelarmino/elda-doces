<?php

declare(strict_types=1);

$legacyAddress = ['name' => 'Destinatario Anterior', 'email' => 'old@example.invalid', 'phone' => '00000000001',
    'zip' => '00000-001', 'address' => 'Endereco Anterior', 'number' => '1', 'complement' => 'Antigo',
    'city' => 'Cidade Antiga / ZZ', 'updated_at' => '2020-01-01T00:00:00Z', 'cpf' => 'LEGACY-SYNTHETIC-CPF'];
foreach (['new account' => null, 'existing address with legacy CPF' => $legacyAddress] as $account => $address) {
    foreach (['unchecked (absent)' => null, 'unchecked (zero)' => '0', 'checked' => '1', 'noncanonical value' => 'on'] as $choice => $save) {
        $h->test("checkout $account / $choice", function () use ($h, $address, $save): void {
            $h->seed(['role' => 'customer', 'address' => $address]);
            $fields = checkoutFields();
            if ($save !== null) {
                $fields['save_address'] = $save;
            }
            $response = $h->form('/checkout', $fields);
            $h->same(303, $response['status']);
            $h->same('/pedido/sucesso/TEST123', $response['location']);
            $h->same(1, count($h->state()['orders']));
            $h->same(1, count($h->events('createPix')));
            $h->same(syntheticCpf(), $h->events('createPix')[0][1]['cpf'], 'CPF must reach the payer only');
            $h->same('fixture@example.invalid', $h->events('createPix')[0][1]['email']);
            $customer = $h->state()['orders'][0]['customer'];
            $h->same(false, array_key_exists('cpf', $customer));
            $h->same('Rua de Teste', $customer['address']);
            $h->same('pix', $customer['payment']);
            $h->same('fixture@example.invalid', $customer['email']);
            $h->same(3490, $h->state()['orders'][0]['total_cents']);
            $h->same(1, count($h->events('updateOrderPayment')));
            if ($save === '1') {
                $h->same(1, count($h->events('saveUserDeliveryAddress')));
                $saved = $h->state()['address'];
                $expectedKeys = ['name', 'email', 'phone', 'zip', 'address', 'number', 'complement', 'city', 'updated_at'];
                $h->same($expectedKeys, array_keys($saved), 'Address must use the explicit allowlist');
                foreach (['name', 'phone', 'zip', 'address', 'number', 'complement', 'city'] as $key) {
                    $h->same($fields[$key], $saved[$key], $key);
                }
                $h->same('fixture@example.invalid', $saved['email']);
                $h->same(true, strtotime($saved['updated_at']) !== false);
                $h->same(false, array_key_exists('cpf', $saved));
            } else {
                $h->same([], $h->events('saveUserDeliveryAddress'));
                // No retroactive migration: an unchecked form must not touch even legacy CPF data.
                $h->same($address, $h->state()['address']);
            }
            $persisted = json_encode($h->state()['orders']) . json_encode($h->state()['address']);
            $h->same(false, str_contains($persisted, syntheticCpf()));
        });
    }
    $h->test("checkout view $account leaves CPF blank", function () use ($h, $address): void {
        $h->seed(['role' => 'customer', 'address' => $address]);
        $response = $h->request('GET', '/checkout');
        $h->same(200, $response['status']);
        $h->same(1, preg_match('/<input\b[^>]*\bname="cpf"[^>]*>/s', $response['body'], $matches));
        $h->same(0, preg_match('/\bvalue\s*=\s*["\'][^"\']+/', $matches[0]));
        $h->same(true, str_contains($matches[0], 'autocomplete="off"'));
        $h->same(false, str_contains($response['body'], 'LEGACY-SYNTHETIC-CPF'));
        $h->same(true, str_contains($response['body'], 'name="save_address"'));
        $h->same(true, str_contains($response['body'], $address['address'] ?? 'Cliente Ficticio'));
        $h->same([], $h->events('saveUserDeliveryAddress'));
        $h->same($address, $h->state()['address']);
    });
}

foreach ([
    'unauthenticated' => [null, [], '/entrar'],
    'invalid CSRF' => ['customer', ['_token' => 'incorrect'], '/checkout'],
    'missing CSRF' => ['customer', ['_token' => ''], '/checkout'],
    'invalid CPF' => ['customer', ['cpf' => '00000000000'], '/checkout'],
    'missing required address' => ['customer', ['address' => ''], '/checkout'],
] as $name => [$role, $changes, $location]) {
    $h->test('checkout rejects ' . $name . ' without writes or PIX', function () use ($h, $role, $changes, $location, $legacyAddress): void {
        $h->seed(['role' => $role, 'address' => $legacyAddress]);
        $response = $h->form('/checkout', array_replace(checkoutFields(), ['save_address' => '1'], $changes));
        $h->same(303, $response['status']);
        $h->same($location, $response['location']);
        $h->same([], $h->events('saveUserDeliveryAddress'));
        $h->same([], $h->events('createOrder'));
        $h->same([], $h->events('createPix'));
        $h->same($legacyAddress, $h->state()['address']);
    });
}
