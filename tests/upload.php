<?php

declare(strict_types=1);

$fourMiB = 4 * 1024 * 1024;
$fiveMiB = 5 * 1024 * 1024;
$png = pngBytes($fourMiB);
$editId = '33333333-3333-4333-8333-333333333333';
$routes = ['new' => '/admin/produtos/novo', 'edit' => '/admin/produtos/' . $editId . '/editar'];

$h->test('upload fixture is a PNG of exactly 4 MiB and config allows multipart overhead', function () use ($h, $png, $fourMiB): void {
    $h->same($fourMiB, strlen($png));
    $h->same(IMAGETYPE_PNG, getimagesizefromstring($png)[2]);
    $h->same([1, 1], array_slice(getimagesizefromstring($png), 0, 2));
    $config = parse_ini_file(dirname(__DIR__) . '/docker/php.ini');
    $h->same('4M', $config['upload_max_filesize']);
    $h->same('5M', $config['post_max_size']);
});

foreach ($routes as $name => $route) {
    $h->test("upload $name accepts exact 4 MiB PNG plus multipart overhead", function () use ($h, $route, $png, $fourMiB, $fiveMiB, $name, $editId): void {
        $h->seed();
        [$body, $headers] = multipart($png);
        $h->same(true, strlen($body) > $fourMiB && strlen($body) < $fiveMiB);
        $response = $h->request('POST', $route, $body, $headers);
        $h->same(303, $response['status']);
        $h->same('/admin', $response['location']);
        $h->same(['error' => UPLOAD_ERR_OK, 'size' => $fourMiB], $h->state()['upload']);
        $h->same(1, count($h->events('saveProduct')));
        $product = $h->state()['products'][0];
        $h->same($name === 'edit' ? $editId : '', $product['id']);
        $h->same(1, preg_match('/^prod-doce-fixture-[0-9a-f]{8}\.png$/', $product['image']));
        $saved = $h->directory . '/public/assets/images/' . $product['image'];
        $h->same(true, is_file($saved), 'move_uploaded_file must have succeeded');
        $h->same($fourMiB, filesize($saved));
        $h->same(hash('sha256', $png), hash_file('sha256', $saved));
        $h->same([], glob($h->directory . '/uploads/*'));
    });

    $h->test("upload $name rejects 4 MiB + 1 byte without product writes", function () use ($h, $route, $fourMiB): void {
        $h->seed();
        $before = glob($h->directory . '/public/assets/images/*');
        [$body, $headers] = multipart(pngBytes($fourMiB + 1));
        $response = $h->request('POST', $route, $body, $headers);
        $h->same(303, $response['status']);
        $h->same($route, $response['location']);
        $h->same(UPLOAD_ERR_INI_SIZE, $h->state()['upload']['error']);
        $h->same(true, str_contains($h->state()['flash'][0]['message'] ?? '', '4MB'));
        $h->same([], $h->events('saveProduct'));
        $h->same($before, glob($h->directory . '/public/assets/images/*'));
        $h->same([], glob($h->directory . '/uploads/*'));
    });

    $h->test("upload $name rejects body over 5 MiB with 413", function () use ($h, $route, $png, $fiveMiB): void {
        $h->seed();
        [$body, $headers] = multipart($png, ['description' => str_repeat('x', 1024 * 1024)]);
        $h->same(true, strlen($body) > $fiveMiB);
        $response = $h->request('POST', $route, $body, $headers);
        $h->same(413, $response['status']);
        $h->same(413, json_decode($response['body'], true)['data']['code']);
        $h->same([], $h->events('saveProduct'));
    });

    $h->test("upload $name body exactly 5 MiB reaches CSRF (not 413)", function () use ($h, $route, $fiveMiB): void {
        $h->seed();
        $response = $h->request('POST', $route, str_repeat('x', $fiveMiB), ['Content-Type: application/octet-stream']);
        $h->same(303, $response['status']);
        $h->same($route, $response['location']);
        $h->same([], $h->events('saveProduct'));
    });
}

foreach ([
    ['POST', '/checkout'],
    ['POST', '/webhooks/mercado-pago'],
    ['POST', '/admin/produtos/not-a-uuid/editar'],
    ['POST', '/admin/produtos/novo/extra'],
    ['PUT', '/admin/produtos/novo'],
    ['GET', '/admin/produtos/novo'],
    ['PATCH', '/admin/produtos/' . $editId . '/editar'],
] as [$method, $route]) {
    $h->test("body guard retains 2,000,000 limit: $method $route", function () use ($h, $method, $route): void {
        $h->seed();
        $response = $h->request($method, $route, str_repeat('x', 2_000_001), ['Content-Type: application/octet-stream']);
        $h->same(413, $response['status']);
        $h->same([], $h->state()['events']);
    });
}
$h->test('ordinary route accepts body exactly 2,000,000 bytes through size guard', function () use ($h): void {
    $h->seed();
    $body = '{"data":{"id":123456}}';
    $body .= str_repeat(' ', 2_000_000 - strlen($body));
    $response = $h->request('POST', '/webhooks/mercado-pago', $body, signedHeaders());
    $h->same(200, $response['status']);
    $h->same(1, count($h->events('updateOrderPayment')));
});

foreach ($routes as $name => $route) {
    foreach ([
        'unauthenticated' => [null, 'fixture-csrf', 303, '/entrar'],
        'non-admin' => ['customer', 'fixture-csrf', 403, ''],
        'invalid CSRF' => ['admin', 'wrong-token', 303, $route],
    ] as $guard => [$role, $token, $status, $location]) {
        $h->test("upload $name $guard blocks writes", function () use ($h, $route, $role, $token, $status, $location): void {
            $h->seed(['role' => $role]);
            $before = glob($h->directory . '/public/assets/images/*');
            [$body, $headers] = multipart(pngBytes(200), ['_token' => $token]);
            $response = $h->request('POST', $route, $body, $headers);
            $h->same($status, $response['status']);
            $h->same($location, $response['location']);
            $h->same([], $h->events('saveProduct'));
            $h->same($before, glob($h->directory . '/public/assets/images/*'));
        });
    }
}
$h->test('upload rejects a fake PNG despite client MIME declaration', function () use ($h, $routes): void {
    $h->seed();
    $before = glob($h->directory . '/public/assets/images/*');
    [$body, $headers] = multipart('This is not a PNG image.');
    $response = $h->request('POST', $routes['new'], $body, $headers);
    $h->same(303, $response['status']);
    $h->same($routes['new'], $response['location']);
    $h->same(true, str_contains($h->state()['flash'][0]['message'] ?? '', 'Formato de imagem inválido'));
    $h->same([], $h->events('saveProduct'));
    $h->same($before, glob($h->directory . '/public/assets/images/*'));
});
