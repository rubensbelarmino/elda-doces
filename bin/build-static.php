<?php
// /**
//  * ============================================================================
//  * GERADOR DE BUILD ESTÁTICO PARA NETLIFY — ELDA BOLOS E DOCES
//  * ============================================================================
//  * Converte as rotas e views do e-commerce PHP em arquivos HTML estáticos prontos
//  * para publicação instantânea na CDN global do Netlify (Jamstack).
//  * ============================================================================
//  */
//
// declare(strict_types=1);
//
// $projectDir = dirname(__DIR__);
// require $projectDir . '/src/bootstrap.php';
//
// $distDir = $projectDir . '/dist';
// if (is_dir($distDir)) {
//     exec("rm -rf " . escapeshellarg($distDir));
// }
// mkdir($distDir, 0755, true);
//
// // 1. Copiar arquivos de assets estáticos (CSS, JS, Imagens, Favicon)
// echo "→ Copiando assets para dist/assets/...\n";
// exec("mkdir -p " . escapeshellarg($distDir . '/assets'));
// exec("cp -r " . escapeshellarg($projectDir . '/public/assets') . "/* " . escapeshellarg($distDir . '/assets/'));
// exec("cp " . escapeshellarg($projectDir . '/public/assets/images/favicon.svg') . " " . escapeshellarg($distDir . '/favicon.ico'));
//
// // 2. Renderizar páginas públicas do site via HTTP local
// $routes = [
//     '/' => 'index.html',
//     '/cardapio' => 'cardapio/index.html',
//     '/carrinho' => 'carrinho/index.html',
//     '/entrar' => 'entrar/index.html',
//     '/criar-conta' => 'criar-conta/index.html',
//     '/status' => 'status/index.html',
//     '/robots.txt' => 'robots.txt',
//     '/sitemap.xml' => 'sitemap.xml',
// ];
//
// // Adiciona todas as páginas individuais de produtos
// foreach ($store->products() as $product) {
//     if (!empty($product['slug'])) {
//         $routes['/produto/' . $product['slug']] = 'produto/' . $product['slug'] . '/index.html';
//     }
// }
//
// echo "→ Renderizando " . count($routes) . " páginas e arquivos estáticos...\n";
//
// $localUrl = 'http://127.0.0.1:8128';
//
// foreach ($routes as $route => $destFile) {
//     $targetPath = $distDir . '/' . $destFile;
//     $targetSubdir = dirname($targetPath);
//     if (!is_dir($targetSubdir)) {
//         mkdir($targetSubdir, 0755, true);
//     }
//
//     $ch = curl_init($localUrl . $route);
//     curl_setopt_array($ch, [
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_FOLLOWLOCATION => true,
//         CURLOPT_TIMEOUT => 10,
//     ]);
//     $html = curl_exec($ch);
//     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     curl_close($ch);
//
//     if ($html === false || $httpCode >= 400) {
//         echo "  [AVISO] Rota {$route} retornou código {$httpCode}. Gerando fallback.\n";
//         continue;
//     }
//
//     // Para o build estático do Netlify, apontar src diretamente para /assets/images/filename
//     $adaptedHtml = preg_replace(
//         '#src="data:image/gif;base64,[^"]*"(\s+data-base64-image="([^"]+)")#s',
//         'src="/assets/images/$2"$1',
//         $html
//     );
//
//     file_put_contents($targetPath, $adaptedHtml);
//
//     // Cria também a versão flat nome.html para compatibilidade máxima com URLs sem barra final no Netlify
//     if (str_ends_with($destFile, '/index.html')) {
//         $flatName = substr($destFile, 0, -11) . '.html';
//         file_put_contents($distDir . '/' . $flatName, $adaptedHtml);
//     }
//
//     echo "  ✓ {$route} -> dist/{$destFile}\n";
// }
//
// // 3. Renderizar páginas autenticadas e administrativas para modo Jamstack / Netlify
// echo "→ Renderizando páginas interativas (Admin, 2FA, Minha Conta, Checkout) para Netlify...\n";
//
// // A. Painel Administrativo
// $adminHtml = (function() use ($store, $projectDir) {
//     $metrics = $store->dashboard();
//     $orders = array_slice($store->orders(), 0, 8);
//     $products = $store->products(true);
//     $title = 'Painel Administrativo';
//     $nonce = base64_encode(random_bytes(18));
//     $user = ['name' => 'Admin Doce', 'role' => 'admin'];
//
//     ob_start();
//     require $projectDir . '/views/admin/dashboard.php';
//     $content = (string) ob_get_clean();
//
//     ob_start();
//     require $projectDir . '/views/layout.php';
//     $fullHtml = (string) ob_get_clean();
//
//     return preg_replace(
//         '#src="data:image/gif;base64,[^"]*"(\s+data-base64-image="([^"]+)")#s',
//         'src="/assets/images/$2"$1',
//         $fullHtml
//     );
// })();
// if (!is_dir($distDir . '/admin')) mkdir($distDir . '/admin', 0755, true);
// file_put_contents($distDir . '/admin/index.html', $adminHtml);
// file_put_contents($distDir . '/admin.html', $adminHtml);
// echo "  ✓ /admin -> dist/admin/index.html & dist/admin.html\n";
//
// // B. Verificação 2FA
// $twoFactorHtml = (function() use ($projectDir) {
//     $maskedEmail = 'ce***@gmail.com';
//     $expiresAt = time() + 600;
//     $title = 'Verificar código de segurança';
//     $nonce = base64_encode(random_bytes(18));
//     $user = null;
//
//     ob_start();
//     require $projectDir . '/views/auth/two-factor.php';
//     $content = (string) ob_get_clean();
//
//     ob_start();
//     require $projectDir . '/views/layout.php';
//     $fullHtml = (string) ob_get_clean();
//
//     return preg_replace(
//         '#src="data:image/gif;base64,[^"]*"(\s+data-base64-image="([^"]+)")#s',
//         'src="/assets/images/$2"$1',
//         $fullHtml
//     );
// })();
// if (!is_dir($distDir . '/verificar-codigo')) mkdir($distDir . '/verificar-codigo', 0755, true);
// file_put_contents($distDir . '/verificar-codigo/index.html', $twoFactorHtml);
// file_put_contents($distDir . '/verificar-codigo.html', $twoFactorHtml);
// echo "  ✓ /verificar-codigo -> dist/verificar-codigo/index.html & dist/verificar-codigo.html\n";
//
// // C. Minha Conta
// $accountHtml = (function() use ($store, $projectDir) {
//     $user = ['id' => '1', 'name' => 'Cesar Augusto Bardelotti', 'email' => 'cesaraugustobardelotti@gmail.com', 'role' => 'customer'];
//     $orders = array_slice($store->orders(), 0, 4);
//     $title = 'Minha Conta';
//     $nonce = base64_encode(random_bytes(18));
//
//     ob_start();
//     require $projectDir . '/views/account.php';
//     $content = (string) ob_get_clean();
//
//     ob_start();
//     require $projectDir . '/views/layout.php';
//     $fullHtml = (string) ob_get_clean();
//
//     return preg_replace(
//         '#src="data:image/gif;base64,[^"]*"(\s+data-base64-image="([^"]+)")#s',
//         'src="/assets/images/$2"$1',
//         $fullHtml
//     );
// })();
// if (!is_dir($distDir . '/minha-conta')) mkdir($distDir . '/minha-conta', 0755, true);
// file_put_contents($distDir . '/minha-conta/index.html', $accountHtml);
// file_put_contents($distDir . '/minha-conta.html', $accountHtml);
// echo "  ✓ /minha-conta -> dist/minha-conta/index.html & dist/minha-conta.html\n";
//
// // D. Checkout
// $checkoutHtml = (function() use ($store, $projectDir) {
//     $user = ['id' => '1', 'name' => 'Cesar Augusto Bardelotti', 'email' => 'cesaraugustobardelotti@gmail.com', 'role' => 'customer'];
//     $products = $store->products();
//     $subtotal = (int) $products[0]['price_cents'] + (int) $products[1]['price_cents'];
//     $shippingCostCents = 1200;
//     $cart = [
//         'count' => 2,
//         'subtotal_cents' => $subtotal,
//         'shipping_cents' => $shippingCostCents,
//         'total_cents' => $subtotal + $shippingCostCents,
//         'items' => [
//             ['product' => $products[0], 'quantity' => 1, 'line_cents' => (int) $products[0]['price_cents']],
//             ['product' => $products[1], 'quantity' => 1, 'line_cents' => (int) $products[1]['price_cents']]
//         ]
//     ];
//     $finalTotalCents = (int) $cart['total_cents'];
//     $hasMercadoPagoPix = true;
//     $title = 'Finalizar Pedido';
//     $nonce = base64_encode(random_bytes(18));
//
//     ob_start();
//     require $projectDir . '/views/checkout.php';
//     $content = (string) ob_get_clean();
//
//     ob_start();
//     require $projectDir . '/views/layout.php';
//     $fullHtml = (string) ob_get_clean();
//
//     return preg_replace(
//         '#src="data:image/gif;base64,[^"]*"(\s+data-base64-image="([^"]+)")#s',
//         'src="/assets/images/$2"$1',
//         $fullHtml
//     );
// })();
// if (!is_dir($distDir . '/checkout')) mkdir($distDir . '/checkout', 0755, true);
// file_put_contents($distDir . '/checkout/index.html', $checkoutHtml);
// file_put_contents($distDir . '/checkout.html', $checkoutHtml);
// echo "  ✓ /checkout -> dist/checkout/index.html & dist/checkout.html\n";
//
// // 4. Criar arquivo de redirecionamentos do Netlify (_redirects) com suporte total a barras e sem barras
// $redirectsContent = <<<REDIRECTS
// # Netlify Redirects & Header Rules para Elda Bolos e Doces
// /sitemap.xml          /sitemap.xml            200
// /robots.txt           /robots.txt             200
// /favicon.ico          /assets/images/favicon.svg 200
//
// # Roteamento estático limpo (com e sem barra final)
// /entrar               /entrar.html            200
// /entrar/              /entrar/index.html      200
// /cardapio             /cardapio.html          200
// /cardapio/            /cardapio/index.html    200
// /carrinho             /carrinho.html          200
// /carrinho/            /carrinho/index.html    200
// /checkout             /checkout.html          200
// /checkout/            /checkout/index.html    200
// /criar-conta          /criar-conta.html       200
// /criar-conta/         /criar-conta/index.html 200
// /verificar-codigo     /verificar-codigo.html  200
// /verificar-codigo/    /verificar-codigo/index.html 200
// /minha-conta          /minha-conta.html       200
// /minha-conta/         /minha-conta/index.html 200
// /admin                /admin.html             200
// /admin/               /admin/index.html       200
// /status               /status.html            200
// /status/              /status/index.html      200
//
// # Fallback 404
// /*                    /index.html             200
// REDIRECTS;
// file_put_contents($distDir . '/_redirects', $redirectsContent);
//
// // 5. Criar netlify.toml com headers de segurança e SEO
// $netlifyToml = <<<TOML
// [build]
//   publish = "dist"
//   command = "chmod +x ./bin/build-static.php && php ./bin/build-static.php"
//
// [[headers]]
//   for = "/*"
//   [headers.values]
//     X-Frame-Options = "DENY"
//     X-Content-Type-Options = "nosniff"
//     Referrer-Policy = "strict-origin-when-cross-origin"
//     Permissions-Policy = "camera=(), microphone=(), geolocation=()"
//
// [[headers]]
//   for = "/assets/*"
//   [headers.values]
//     Cache-Control = "public, max-age=31536000, immutable"
//
// [[headers]]
//   for = "/sitemap.xml"
//   [headers.values]
//     Content-Type = "application/xml; charset=utf-8"
//
// [[headers]]
//   for = "/robots.txt"
//   [headers.values]
//     Content-Type = "text/plain; charset=utf-8"
// TOML;
// file_put_contents($projectDir . '/netlify.toml', $netlifyToml);
//
// echo "\n✓ Build estático gerado com sucesso em: dist/\n";
// echo "→ Total de páginas prontas para o Netlify: " . (count($routes) + 1) . "\n";
