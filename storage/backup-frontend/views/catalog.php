<?php
/**
 * ============================================================================
 * CATÁLOGO / CARDÁPIO (LISTAGEM DE PRODUTOS) — ELDA BOLOS E DOCES
 * ============================================================================
 * Esta página atua como o hub central de produtos (Category/Archive Hub).
 * Para e-commerces, as páginas de categoria são frequentemente as mais
 * valiosas em volume de busca orgânica para termos genéricos como
 * "bolos recheados", "macarons gourmet" ou "doceria em Sorocaba".
 *
 * TÉCNICAS DE SEO APLICADAS NESTE ARQUIVO:
 * ----------------------------------------------------------------------------
 * 1. HIERARQUIA SEMÂNTICA DE CATEGORIA:
 *    - Tag <h1> dedicada ("Nosso cardápio"), sinalizando claramente o tema
 *      principal da página aos buscadores.
 *    - Filtros por categoria com URLs limpas e semânticas (?categoria=Bolos),
 *      permitindo que o Google rastreie e indexe subseções temáticas.
 *
 * 2. CORE WEB VITALS & PRIORIZAÇÃO DE IMAGENS:
 *    - O script avalia a posição do produto ($catalogIndex < 4):
 *      As 4 primeiras imagens recebem prioridade alta ($imagePriority = true),
 *      carregando imediatamente na renderização acima da dobra (LCP).
 *    - Os produtos subsequentes continuam usando lazy loading inteligente
 *      para não sobrecarregar a banda do usuário em dispositivos móveis.
 *
 * 3. USABILIDADE & BUSCA INTEGRADA:
 *    - Campo de busca semântico (<input name="busca">) com método GET,
 *      garantindo que os resultados possam ser compartilhados via URL.
 *    - Estado vazio amigável (Empty State) para não gerar frustração no usuário
 *      e incentivar a navegação contínua no site (reduzindo taxa de rejeição/bounce).
 * ============================================================================
 */
?>

<!-- ====================================================================== -->
<!-- 1. CABEÇALHO DO CARDÁPIO (HERO DA SEÇÃO)                               -->
<!-- ====================================================================== -->
<section class="page-hero compact" aria-label="Apresentação do cardápio">
    <div class="container">
        <p class="overline">Feito artesanalmente para você</p>
        <!-- H1 Principal da página de catálogo -->
        <h1>Nosso <em>cardápio</em></h1>
        <p>Escolha seu novo doce artesanal favorito em Sorocaba. Nós cuidamos do resto.</p>
    </div>
</section>

<!-- ====================================================================== -->
<!-- 2. BARRA DE FILTROS & BUSCA SEMÂNTICA                                  -->
<!-- ====================================================================== -->
<section class="section catalog-section" aria-label="Catálogo de produtos">
    <div class="container">
        <!-- Formulário de filtros com método GET para preservação de URLs e SEO -->
        <form class="catalog-toolbar" method="get" action="/cardapio" role="search" aria-label="Filtrar cardápio">
            <!-- Pílulas de categorias: links semânticos rastreáveis por robôs de busca -->
            <div class="category-pills" role="navigation" aria-label="Filtrar por categoria">
                <a class="<?= $category === '' ? 'active' : '' ?>" href="/cardapio">
                    Todos os doces
                </a>
                <?php foreach ($categories as $item): ?>
                    <a class="<?= $category === $item ? 'active' : '' ?>" 
                       href="/cardapio?categoria=<?= urlencode($item) ?>">
                        <?= e($item) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Campo de busca rápida com rótulo acessível e ícone SVG -->
            <label class="search-field">
                <span class="sr-only">Buscar doce por nome ou ingrediente</span>
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-4-4"/>
                </svg>
                <input name="busca" 
                       type="search" 
                       value="<?= e($search) ?>" 
                       placeholder="Buscar por morango, chocolate, bolo...">
            </label>
        </form>

        <!-- ============================================================== -->
        <!-- 3. GRADE DE PRODUTOS OU ESTADO VAZIO                           -->
        <!-- ============================================================== -->
        <?php if ($products): ?>
            <div class="product-grid catalog-grid">
                <?php 
                $catalogIndex = 0; 
                foreach ($products as $product) {
                    // Otimização de Core Web Vitals: Primeiros 4 produtos recebem prioridade alta de imagem
                    $imagePriority = $catalogIndex < 4; 
                    require __DIR__ . '/partials/product-card.php'; 
                    $catalogIndex++; 
                } 
                ?>
            </div>
        <?php else: ?>
            <!-- Feedback caso o termo buscado ou filtro não retorne resultados -->
            <div class="empty-state">
                <span aria-hidden="true">◇</span>
                <h2>Nenhum doce encontrado</h2>
                <p>Tente buscar por outro termo ou explore todas as delícias do nosso cardápio completo.</p>
                <a class="button button-primary" href="/cardapio">
                    Ver todos os doces
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
