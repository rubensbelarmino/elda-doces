<?php
/**
 * ============================================================================
 * LAYOUT PRINCIPAL (MASTER TEMPLATE) — ELDA BOLOS E DOCES
 * ============================================================================
 * Este arquivo atua como o esqueleto HTML5 padrão para todas as páginas da loja.
 * Ele centraliza cabeçalho, rodapé, navegação, scripts e todas as diretivas de
 * SEO (Search Engine Optimization), Acessibilidade (a11y) e Segurança (CSP).
 *
 * TÉCNICAS DE SEO APLICADAS NESTE ARQUIVO:
 * ----------------------------------------------------------------------------
 * 1. SEO ON-PAGE & TAGS DE METADADOS:
 *    - <title>: Título dinâmico e otimizado com palavras-chave de cauda longa
 *      e nome da marca ("Elda Bolos e Doces").
 *    - <meta name="description">: Descrição atraente e concisa (até 160 caracteres)
 *      para melhorar a taxa de clique (CTR - Click-Through Rate) nas SERPs do Google.
 *    - <link rel="canonical">: Evita problemas de conteúdo duplicado decorrentes
 *      de parâmetros de URL, trailing slashes ou protocolos diferentes.
 *    - <meta name="robots">: Por padrão permite indexação completa (index, follow).
 *
 * 2. OPEN GRAPH & SOCIAL SEO (REDES SOCIAIS E WHATSAPP):
 *    - og:title, og:description, og:image, og:url, og:site_name, og:locale:
 *      Garantem que ao compartilhar qualquer link no WhatsApp, Facebook, LinkedIn
 *      ou Instagram, um card visual profissional seja gerado com imagem e resumo.
 *    - twitter:card, twitter:title, twitter:image: Suporte a cards ricos no X/Twitter.
 *
 * 3. DADOS ESTRUTURADOS (SCHEMA.ORG / JSON-LD) — SEO LOCAL:
 *    - Bloco JSON-LD com tipo "Bakery" / "LocalBusiness": Informa diretamente aos
 *      robôs do Google o nome da empresa, endereço físico em Sorocaba/SP, telefone,
 *      horário de atendimento e coordenadas, aumentando as chances de destaque
 *      no "Google Meu Negócio" e Google Maps (Local Pack).
 *    - Bloco JSON-LD com "WebSite" e "potentialAction" para sitelinks de busca.
 *
 * 4. PERFORMANCE & CORE WEB VITALS (FATOR DIRETO DE RANKING):
 *    - Versão em assets (Cache Busting via versioned_asset()): Permite cache longo
 *      no navegador sem risco de servir arquivos desatualizados.
 *    - Scripts com atributo 'defer': Não bloqueiam a renderização da árvore DOM
 *      (reduzindo o First Contentful Paint - FCP).
 *    - Preconnect / Resource Hints para otimizar conexões críticas.
 *
 * 5. ACESSIBILIDADE & ESTRUTURA SEMÂNTICA HTML5:
 *    - Skip link ("Pular para o conteúdo"): Essencial para leitores de tela e WCAG.
 *    - Tags semânticas <header>, <nav>, <main id="conteudo">, <footer>.
 *    - Atributos ARIA (aria-label, aria-current="page", aria-expanded).
 * ============================================================================
 */

// Recupera informações da sessão atual
$user = current_user();
$cart = cart_details();
$flashes = consume_flashes();

// Configurações dinâmicas de SEO da página
$pageTitle = isset($title) ? $title . ' — Elda Bolos e Doces' : 'Elda Bolos e Doces — Doceria Artesanal em Sorocaba';
$pageDescription = isset($description) 
    ? $description 
    : 'Elda Bolos e Doces em Sorocaba: bolos artesanais, tortas finas, brigadeiros e doces feitos com carinho. Peça pelo site ou delivery.';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$canonicalUrl = request_base_url() . ($currentPath === '/' ? '' : $currentPath);
$ogImage = isset($metaImage) ? $metaImage : request_base_url() . versioned_asset('images/hero-doces.webp');
?>
<!doctype html>
<html lang="pt-BR" dir="ltr">
<head>
    <!-- [SEO & Codificação] Charset UTF-8 garante correta exibição de caracteres acentuados -->
    <meta charset="utf-8">

    <!-- [SEO Mobile-First] Viewport responsivo obrigatório para a indexação Mobile-First do Google -->
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    
    <!-- [Branding & UX] Cor de destaque na barra do navegador em dispositivos móveis -->
    <meta name="theme-color" content="#8d2949">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!-- [SEO On-Page] Título da página exibido na aba e nos resultados de busca (SERP) -->
    <title><?= e($pageTitle) ?></title>

    <!-- [SEO On-Page] Meta descrição para otimização de snippets nos motores de busca -->
    <meta name="description" content="<?= e($pageDescription) ?>">

    <!-- [SEO Indexação] Diretiva padrão para indexação e rastreamento de links -->
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

    <!-- [SEO Técnico] URL Canônica: previne penalizações por conteúdo duplicado -->
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <!-- [Social SEO - Open Graph] Metadados para compartilhamento rico (WhatsApp, Facebook, LinkedIn) -->
    <meta property="og:locale" content="pt_BR">
    <meta property="og:type" content="<?= isset($product) ? 'product' : 'website' ?>">
    <meta property="og:site_name" content="Elda Bolos e Doces">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:image:alt" content="Elda Bolos e Doces — Doceria Artesanal em Sorocaba">

    <!-- [Social SEO - Twitter Cards] Otimização para compartilhamento no X/Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <!-- [SEO & Performance] Preload do CSS para eliminar bloqueio de renderização -->
    <link rel="preload" href="<?= versioned_asset('app.css') ?>" as="style">
    <link rel="stylesheet" href="<?= versioned_asset('app.css') ?>">

    <!-- [SEO & Identidade Visual] Favicon & Ícones em múltiplos formatos para Google e Apple -->
    <link rel="icon" href="<?= versioned_asset('images/favicon.svg') ?>" type="image/svg+xml">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- [SEO & Analytics] Google Analytics 4 (GA4) -->
    <?php $gaId = trim((string) (getenv('GOOGLE_ANALYTICS_ID') ?: 'G-ELDADOCES0')); ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
    <script nonce="<?= e($nonce) ?>">
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= e($gaId) ?>', { send_page_view: true });
    </script>

    <!-- [SEO Local & Rich Snippets] Dados Estruturados Schema.org em JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Bakery",
          "@id": "<?= e(request_base_url()) ?>/#bakery",
          "name": "Elda Bolos e Doces",
          "alternateName": "Elda Bolos & Doces Sorocaba",
          "url": "<?= e(request_base_url()) ?>",
          "logo": "<?= e(request_base_url()) ?>/assets/images/elda-logo.jpg",
          "image": "<?= e(request_base_url()) ?>/assets/images/hero-doces.webp",
          "description": "Doceria artesanal tradicional em Sorocaba/SP especializada em bolos recheados, tortas finas, brigadeiros gourmet e sobremesas para ocasiões especiais.",
          "telephone": "+55-15-99745-1766",
          "priceRange": "$$",
          "servesCuisine": "Confeitaria Brasileira e Sobremesas",
          "address": {
            "@type": "PostalAddress",
            "streetAddress": "Av. Dr. Afonso Vergueiro, 2548",
            "addressLocality": "Sorocaba",
            "addressRegion": "SP",
            "postalCode": "18040-000",
            "addressCountry": "BR"
          },
          "geo": {
            "@type": "GeoCoordinates",
            "latitude": -23.4975,
            "longitude": -47.4583
          },
          "openingHoursSpecification": [
            {
              "@type": "OpeningHoursSpecification",
              "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
              "opens": "09:00",
              "closes": "19:00"
            }
          ],
          "sameAs": [
            "https://www.instagram.com/eldabolosedoces",
            "https://www.ifood.com.br/delivery/sorocaba-sp/elda-bolos-e-doces-vila-augusta/f7b56366-f7d9-4b8a-b0bd-f06ad6c5829f"
          ]
        },
        {
          "@type": "WebSite",
          "@id": "<?= e(request_base_url()) ?>/#website",
          "url": "<?= e(request_base_url()) ?>",
          "name": "Elda Bolos e Doces",
          "description": "Loja online oficial da Elda Bolos e Doces — Encomendas e pronta entrega em Sorocaba.",
          "inLanguage": "pt-BR",
          "potentialAction": {
            "@type": "SearchAction",
            "target": "<?= e(request_base_url()) ?>/cardapio?busca={search_term_string}",
            "query-input": "required name=search_term_string"
          }
        }
      ]
    }
    </script>
</head>
<body>

<!-- [Acessibilidade / a11y] Atalho para navegação por teclado e leitores de tela -->
<a class="skip-link" href="#conteudo">Pular para o conteúdo principal</a>

<!-- [Banner Superior de Conversão] Anúncio de frete grátis e localização (gatilho de SEO local) -->
<div class="announcement">
    <span>✦ Frete grátis acima de R$ 150</span>
    <span class="announcement-extra">Sorocaba • Delivery, retirada e loja física</span>
</div>

<!-- [Estrutura Semântica] Cabeçalho institucional com logomarca e menu principal -->
<header class="site-header" data-header>
    <div class="container nav-wrap">
        <!-- Logomarca com texto real (evita penalidade de imagem pura sem texto para SEO) -->
        <a class="brand" href="/" aria-label="Elda Bolos e Doces — Página inicial">
            <span class="brand-mark" aria-hidden="true">E</span>
            <span><strong>Elda</strong><small>BOLOS &amp; DOCES</small></span>
        </a>

        <!-- Botão hambúrguer acessível para dispositivos móveis -->
        <button class="menu-toggle" type="button" data-menu-toggle aria-label="Abrir menu de navegação" aria-expanded="false">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>

        <!-- Navegação semântica com indicador de página ativa (aria-current) -->
        <nav class="main-nav" data-menu aria-label="Navegação principal">
            <a href="/"<?= $currentPath === '/' ? ' aria-current="page"' : '' ?>>Início</a>
            <a href="/cardapio"<?= str_starts_with($currentPath, '/cardapio') || str_starts_with($currentPath, '/produto/') ? ' aria-current="page"' : '' ?>>Cardápio</a>
            <a href="/#historia">Nossa história</a>
            <a href="/#contato">Contato</a>
            <?php if ($user && $user['role'] === 'admin'): ?>
                <a href="/admin">Painel Admin</a>
            <?php endif; ?>
            <div class="mobile-nav-cta">
                <a class="button button-primary button-block" href="<?= $user ? '/minha-conta' : '/entrar' ?>">
                    <svg aria-hidden="true" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"/></svg>
                    <span><?= $user ? 'Minha Conta' : 'Entrar na Conta' ?></span>
                </a>
            </div>
        </nav>

        <!-- Ações do usuário: Perfil e Sacola de compras -->
        <div class="nav-actions">
            <a class="icon-link nav-user-link" href="<?= $user ? '/minha-conta' : '/entrar' ?>" aria-label="<?= $user ? 'Minha conta' : 'Entrar na minha conta' ?>">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"/></svg>
            </a>
            <a class="icon-link bag-link" href="/carrinho" aria-label="Sacola de compras com <?= $cart['count'] ?> itens">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 8h12l1 13H5L6 8Zm3 0V6a3 3 0 0 1 6 0v2"/></svg>
                <?php if ($cart['count'] > 0): ?>
                    <span aria-hidden="true"><?= $cart['count'] ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>

<!-- Notificações Toast para feedback instantâneo ao usuário -->
<?php if ($flashes): ?>
<div class="toast-stack" aria-live="polite">
    <?php foreach ($flashes as $flash): ?>
        <div class="toast toast-<?= e($flash['type']) ?>">
            <span aria-hidden="true"><?= $flash['type'] === 'success' ? '✓' : ($flash['type'] === 'error' ? '!' : 'i') ?></span>
            <?= e($flash['message']) ?>
            <button type="button" aria-label="Fechar notificação">×</button>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- [Estrutura Semântica] Conteúdo Principal dinâmico da página -->
<main id="conteudo">
    <?= $content ?>
</main>

<!-- [Estrutura Semântica] Rodapé com links de autoridade, dados de contato e SEO Local -->
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="brand brand-light" href="/" aria-label="Elda Bolos e Doces — Início">
                <span class="brand-mark" aria-hidden="true">E</span>
                <span><strong>Elda</strong><small>BOLOS &amp; DOCES</small></span>
            </a>
            <p>Os melhores bolos e sobremesas para adoçar sua vida.<br>Feitos artesanalmente com carinho em Sorocaba.</p>
        </div>
        <div>
            <h3>Explore</h3>
            <a href="/cardapio">Cardápio completo</a>
            <a href="/#historia">Nossa história</a>
            <a href="/status">Status do sistema</a>
        </div>
        <div>
            <!-- Links com sinal de SEO Local e autoridade de marca -->
            <h3>Atendimento</h3>
            <a class="footer-whatsapp-link" href="https://wa.me/5515997451766" target="_blank" rel="noopener noreferrer">
                <img src="<?= versioned_asset('images/whatsapp-icon.svg') ?>" alt="" width="18" height="18">
                <span>WhatsApp: (15) 99745-1766</span>
            </a>
            <a href="https://www.instagram.com/eldabolosedoces" target="_blank" rel="noopener noreferrer">Instagram: @eldabolosedoces</a>
            <span>Seg–Sáb • 9h às 19h</span>
        </div>
        <div>
            <!-- Links externos com rel="noopener noreferrer" para segurança e controle de PageRank -->
            <h3>Peça agora</h3>
            <p>Delivery pelo iFood ou atendimento direto no WhatsApp.</p>
            <a class="footer-order-link" href="https://www.ifood.com.br/delivery/sorocaba-sp/elda-bolos-e-doces-vila-augusta/f7b56366-f7d9-4b8a-b0bd-f06ad6c5829f" target="_blank" rel="noopener noreferrer">Abrir no iFood →</a>
        </div>
    </div>
    
    <!-- Endereço físico completo: Forte sinal de relevância para SEO Local no Google Maps -->
    <div class="container footer-bottom">
        <span>© <?= date('Y') ?> Elda Bolos e Doces. Todos os direitos reservados.</span>
        <span>Av. Dr. Afonso Vergueiro, 2548 • Vila Augusta, Sorocaba/SP</span>
    </div>
</footer>

<!-- Botão flutuante de WhatsApp para CRO (Conversion Rate Optimization) -->
<a class="whatsapp-float" href="https://wa.me/5515997451766?text=Ol%C3%A1%2C%20vim%20pelo%20site%20da%20Elda%20Bolos%20e%20Doces" target="_blank" rel="noopener noreferrer" aria-label="Falar com a Elda Bolos e Doces no WhatsApp">
    <span class="whatsapp-float-icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="#ffffff" aria-hidden="true">
            <path d="M17.472 14.382c-.301-.15-1.782-.879-2.057-.978-.276-.1-.476-.15-.676.15-.2.3-.775.978-.95 1.178-.176.2-.351.226-.652.075-.301-.15-1.272-.469-2.423-1.496-.897-.8-1.503-1.789-1.678-2.09-.176-.301-.019-.464.132-.614.136-.135.301-.351.452-.527.15-.176.2-.301.301-.501.101-.2.05-.376-.025-.526-.075-.15-.676-1.63-.927-2.233-.244-.588-.493-.509-.676-.518-.175-.01-.375-.01-.575-.01-.2 0-.526.075-.802.376-.276.3-1.052 1.028-1.052 2.508 0 1.48 1.077 2.909 1.228 3.109.15.2 2.119 3.236 5.133 4.54.717.31 1.277.495 1.713.633.72.228 1.375.196 1.893.118.577-.087 1.782-.728 2.033-1.431.25-.703.25-1.305.175-1.431-.075-.126-.276-.201-.577-.351zm-5.464 7.606c-1.893 0-3.65-.512-5.172-1.403l-.371-.219-3.844 1.008 1.026-3.747-.24-.383c-.977-1.554-1.493-3.364-1.493-5.235 0-5.518 4.488-10.006 10.007-10.006 2.673 0 5.186 1.041 7.076 2.932 1.89 1.89 2.93 4.403 2.93 7.076.001 5.519-4.486 10.008-10.005 10.008zm8.491-18.499c-2.268-2.27-5.284-3.52-8.497-3.52-6.618 0-12.004 5.385-12.004 12.003 0 2.113.553 4.177 1.604 5.998l-1.705 6.227 6.371-1.671c1.75 0.954 3.722 1.458 5.728 1.458h.005c6.617 0 12.003-5.386 12.003-12.004 0-3.21-1.25-6.224-3.52-8.491z"/>
        </svg>
    </span>
    <span class="whatsapp-float-text">WhatsApp</span>
</a>

<!-- [Performance / SEO] Script carregado com defer e protegido por Nonce CSP -->
<script nonce="<?= e($nonce) ?>" src="<?= versioned_asset('app.js') ?>" defer></script>
</body>
</html>
