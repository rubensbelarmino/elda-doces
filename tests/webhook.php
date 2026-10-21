<?php

declare(strict_types=1);

foreach ([
    'empty secret' => [['secret' => ''], signedHeaders(), '?data.id=123456'],
    'whitespace secret' => [['secret' => '   '], signedHeaders(), '?data.id=123456'],
    'missing signature' => [[], ['X-Request-Id: fixture-request'], '?data.id=123456'],
    'wrong signature' => [[], signedHeaders(secret: 'wrong-secret'), '?data.id=123456'],
    'missing request id' => [[], [signedHeaders()[2]], '?data.id=123456'],
    'altered request id' => [[], ['X-Request-Id: altered-request', signedHeaders()[2]], '?data.id=123456'],
    'altered data id' => [[], signedHeaders(), '?data.id=654321'],
    'missing timestamp' => [[], ['X-Request-Id: fixture-request', 'X-Signature: v1=abc'], '?data.id=123456'],
    'missing v1' => [[], ['X-Request-Id: fixture-request', 'X-Signature: ts=1792600000'], '?data.id=123456'],
] as $name => [$state, $headers, $query]) {
    $h->test('webhook rejects before payment: ' . $name, function () use ($h, $state, $headers, $query): void {
        $h->seed($state);
        $response = $h->request('POST', '/webhooks/mercado-pago' . $query, '{}', $headers);
        $h->same(401, $response['status']);
        $h->same(['error' => 'invalid_signature'], json_decode($response['body'], true));
        $h->same(1, count($h->events('validateWebhook')));
        $h->same([], $h->events('payment'));
        $h->same([], $h->events('updateOrderPayment'));
    });
}

foreach ([
    'query data.id normalized by PHP' => ['?data.id=123456', '{}', '123456'],
    'query data_id' => ['?data_id=123456', '{}', '123456'],
    'query id' => ['?id=123456', '{}', '123456'],
    'body string fallback' => ['', '{"data":{"id":"123456"}}', '123456'],
    'body integer fallback' => ['', '{"data":{"id":123456}}', '123456'],
    'query wins over conflicting body' => ['?data.id=123456', '{"data":{"id":"654321"}}', '123456'],
    'query data_id wins over id' => ['?data_id=123456&id=654321', '{}', '123456'],
    '30 digit query id preserved as string' => ['?data.id=123456789012345678901234567890', '{}', '123456789012345678901234567890'],
] as $name => [$query, $body, $id]) {
    $h->test('webhook valid: ' . $name, function () use ($h, $query, $body, $id): void {
        $h->seed();
        $response = $h->request('POST', '/webhooks/mercado-pago' . $query, $body, signedHeaders($id));
        $h->same(200, $response['status']);
        $h->same(['ok' => true], json_decode($response['body'], true));
        $h->same(['validateWebhook', 'payment', 'orderById', 'updateOrderPayment'], array_column($h->state()['events'], 0));
        $h->same($id, $h->events('validateWebhook')[0][3]);
        $h->same([['payment', $id]], $h->events('payment'));
        $h->same([['updateOrderPayment', '11111111-1111-4111-8111-111111111111', 'approved', '123456', []]], $h->events('updateOrderPayment'));
    });
}

foreach ([
    'missing id' => ['', '{}'],
    'malformed JSON' => ['', '{'],
    'query array' => ['?data_id[]=123456', '{}'],
    'body array id' => ['', '{"data":{"id":[123456]}}'],
    'body object id' => ['', '{"data":{"id":{"value":123456}}}'],
    'body boolean id' => ['', '{"data":{"id":true}}'],
    'body null id' => ['', '{"data":{"id":null}}'],
    'body float id' => ['', '{"data":{"id":123456.5}}'],
    'negative id' => ['?data.id=-123456', '{}'],
    'nonnumeric id' => ['?data.id=invalid', '{}'],
    'overlong id' => ['?data.id=1234567890123456789012345678901', '{}'],
    'empty query does not fall back to body' => ['?data_id=', '{"data":{"id":123456}}'],
] as $name => [$query, $body]) {
    $h->test('webhook invalid id: ' . $name, function () use ($h, $query, $body): void {
        $h->seed();
        $response = $h->request('POST', '/webhooks/mercado-pago' . $query, $body, signedHeaders());
        $h->same(400, $response['status']);
        $h->same(['error' => 'invalid_data_id'], json_decode($response['body'], true));
        $h->same([], $h->state()['events']);
    });
}

foreach ([
    'missing order' => ['order' => null],
    'different order reference' => ['reference' => '44444444-4444-4444-8444-444444444444'],
    'invalid external reference cannot use payment lookup to bypass match' => ['reference' => 'not-an-order'],
    'missing external reference' => ['reference' => null],
    'wrong paid amount' => ['amount' => 34.89],
    'simulated provider failure' => ['api_error' => true],
] as $name => $changes) {
    $h->test('webhook refuses update: ' . $name, function () use ($h, $changes): void {
        $state = $h->seed();
        if (array_key_exists('order', $changes)) {
            $state['order'] = $changes['order'];
        }
        if (array_key_exists('reference', $changes)) {
            $state['payment']['external_reference'] = $changes['reference'];
        }
        if (isset($changes['amount'])) {
            $state['payment']['transaction_amount'] = $changes['amount'];
        }
        $state['api_error'] = $changes['api_error'] ?? false;
        $h->seed($state);
        $response = $h->request('POST', '/webhooks/mercado-pago?data.id=123456', '{}', signedHeaders());
        $h->same(422, $response['status']);
        $h->same(['error' => 'payment_not_processed'], json_decode($response['body'], true));
        $h->same(1, count($h->events('validateWebhook')));
        $h->same([['payment', '123456']], $h->events('payment'));
        $h->same([], $h->events('updateOrderPayment'));
        if ($state['api_error']) {
            $h->same([], $h->events('orderById'));
        }
    });
}
