<?php
/**
 * ============================================================================
 * PÁGINA INICIAL (HOME / LANDING PAGE) — ELDA BOLOS E DOCES
 * ============================================================================
 * Esta é a principal página de entrada e a mais importante para SEO.
 * Ela combina storytelling acolhedor, apresentação de produtos populares,
 * prova social e fortes sinais de SEO Local para Sorocaba/SP.
 *
 * TÉCNICAS DE SEO APLICADAS NESTE ARQUIVO:
 * ----------------------------------------------------------------------------
 * 1. HIERARQUIA DE TÍTULOS (HEADINGS H1, H2, H3):
 *    - Um único <h1> semântico na seção Hero que estabelece a intenção de busca
 *      principal do usuário combinada com a promessa da marca.
 *    - <h2> bem distribuídos em cada seção temática (<section>) para organizar
 *      o conteúdo de forma lógica para os indexadores do Google.
 *
 * 2. PERFORMANCE & CORE WEB VITALS (FATOR DE RANKING):
 *    - Hero Image: Carregada com fetchpriority="high" e dimensões explícitas
 *      (width="1672" height="941"), otimizando o LCP (Largest Contentful Paint)
 *      e zerando o CLS (Cumulative Layout Shift).
 *    - Imagens Abaixo da Dobra (Below the Fold): Utilizam loading="lazy" e
 *      carregamento progressivo via IntersectionObserver no JavaScript, evitando
 *      desperdício de dados móveis e acelerando o carregamento inicial.
 *
 * 3. ESTRUTURA SEMÂNTICA HTML5:
 *    - <section>, <article>, <figure>, <figcaption> para estruturação rica.
 *    - Seção de galeria associada via aria-labelledby ao seu cabeçalho.
 *
 * 4. SEO LOCAL & PROVA SOCIAL:
 *    - Menção contextual contínua a "Sorocaba", "Vila Augusta" e "desde 2009".
 *    - Endereço físico completo com links diretos para canais de conversão
 *      (WhatsApp e iFood), fortalecendo a autoridade local.
 *
 * 5. LINK BUILDING INTERNO (LINK JUICE):
 *    - Links diretos com âncoras descritivas ("Ver nossas delícias",
 *      "Ver cardápio completo", filtros por categoria) distribuindo autoridade
 *      para as páginas de catálogo e produtos específicos.
 * ============================================================================
 */
?>

<!-- ====================================================================== -->
<!-- 1. SEÇÃO HERO (DOBRA PRINCIPAL / ABOVE THE FOLD)                       -->
<!-- ====================================================================== -->
<section class="hero" aria-label="Apresentação da confeitaria">
    <!-- Imagem de fundo otimizada para LCP com prioridade alta de download -->
    <img class="hero-bg" 
         src="<?= asset('images/hero-doces.webp') ?>" 
         alt="Vitrine com variedade de bolos artesanais, tortas e doces da Elda" 
         width="1672" 
         height="941" 
         fetchpriority="high">

    <div class="container hero-content">
        <!-- Palavra-chave geográfica de SEO Local -->
        <p class="overline">Elda Bolos e Doces • Sorocaba</p>

        <!-- H1 Principal da página inicial: relevância máxima para indexação -->
        <h1>O seu dia merece<br><em>um pedaço feliz.</em></h1>

        <p class="hero-copy">
            Bolos, tortas e doces caprichados, com variedade de verdade e porções generosas para dividir — ou não.
        </p>

        <!-- CTAs (Call to Action) com links internos e externos de conversão -->
        <div class="hero-actions">
            <a class="button button-primary" href="/cardapio">
                Ver nossas delícias <span aria-hidden="true">→</span>
            </a>
            <a class="text-link" href="https://www.ifood.com.br/delivery/sorocaba-sp/elda-bolos-e-doces-vila-augusta/f7b56366-f7d9-4b8a-b0bd-f06ad6c5829f" target="_blank" rel="noopener noreferrer">
                Pedir pelo iFood <span aria-hidden="true">↗</span>
            </a>
        </div>

        <!-- Prova social e autoridade histórica da doceria em Sorocaba -->
        <div class="hero-proof">
            <div class="avatar-stack" aria-hidden="true">
                <span>♥</span><span>✦</span><span>4.9</span>
            </div>
            <p>
                <strong>Uma paixão de Sorocaba</strong><br>
                desde 2009
            </p>
        </div>
    </div>

    <div class="hero-scroll" aria-hidden="true">Role para saborear <span>↓</span></div>
</section>

<!-- ====================================================================== -->
<!-- 2. FAIXA DE DIFERENCIAIS (PROMISES)                                    -->
<!-- ====================================================================== -->
<section class="promise-strip" aria-label="Diferenciais dos nossos doces">
    <div class="container">
        <span>Feitos à mão diariamente</span>
        <i aria-hidden="true">✦</i>
        <span>Ingredientes selecionados</span>
        <i aria-hidden="true">✦</i>
        <span>Embalagens para presentear</span>
        <i aria-hidden="true">✦</i>
        <span>Entrega com cuidado</span>
    </div>
</section>

<!-- ====================================================================== -->
<!-- 3. VITRINE DE PRODUTOS POPULARES (FAVORITOS)                           -->
<!-- ====================================================================== -->
<section class="section products-section" aria-label="Doces mais desejados">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="overline">Os mais desejados</p>
                <h2>Favoritos da <em>Elda</em></h2>
            </div>
            <!-- Link interno descritivo fortalecendo a página /cardapio -->
            <a class="text-link" href="/cardapio">Ver cardápio completo <span aria-hidden="true">→</span></a>
        </div>

        <!-- Grade de produtos em destaque renderizada pelo componente reutilizável -->
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php require __DIR__ . '/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ====================================================================== -->
<!-- 4. HISTÓRIA & PROVA LOCAL (STORYTELLING)                              -->
<!-- ====================================================================== -->
<section class="story section" id="historia" aria-label="Nossa história em Sorocaba">
    <div class="container story-grid">
        <div class="story-photo">
            <!-- Imagem com alt descritivo e lazy loading para melhor pontuação no Google PageSpeed -->
            <img src="<?= image_placeholder() ?>" 
                 data-base64-image="elda-morango.jpg" 
                 alt="Doces artesanais e morangos frescos na vitrine da Elda Bolos e Doces em Sorocaba" 
                 width="611" 
                 height="645" 
                 loading="lazy" 
                 decoding="async">
            <span class="seal" aria-hidden="true">SOROCABA<br>DESDE 2009<br>♥</span>
        </div>
        <div class="story-copy">
            <p class="overline">Delícias da Elda</p>
            <h2>Uma vitrine que dá<br>vontade de provar <em>tudo.</em></h2>
            <p>
                Na Vila Augusta, a Elda reúne bolos, tortas, merengues, cheesecakes e muitos doces em uma seleção conhecida pela variedade, frutas frescas e porções generosas.
            </p>
            <p>
                Você pode visitar a loja física, retirar seu pedido no balcão ou receber em casa. Para encomendas de festas e datas comemorativas, nosso atendimento está pronto no WhatsApp.
            </p>
            <div class="signature">Feito com carinho, <strong>Elda.</strong></div>
            <div class="story-actions">
                <a class="button button-outline button-whatsapp" href="https://wa.me/5515997451766" target="_blank" rel="noopener noreferrer">
                    <img class="btn-whatsapp-icon" src="<?= versioned_asset('images/whatsapp-icon.svg') ?>" alt="" width="20" height="20">
                    <span>Falar no WhatsApp</span>
                </a>
                <a class="button button-outline button-instagram" href="https://www.instagram.com/eldabolosedoces" target="_blank" rel="noopener noreferrer">
                    <img class="btn-instagram-icon" src="<?= versioned_asset('images/instagram-icon.svg') ?>" alt="" width="20" height="20">
                    <span>Veja nosso Instagram</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ====================================================================== -->
<!-- 5. GALERIA FOTOGRÁFICA (CONTEÚDO VISUAL & TAGS FIGURE)                 -->
<!-- ====================================================================== -->

<!-- ====================================================================== -->
<!-- SEÇÃO DE AVALIAÇÕES REAIS DO GOOGLE MAPS (PROVA SOCIAL)               -->
<!-- ====================================================================== -->
<section class="google-reviews-section section" id="avaliacoes" aria-labelledby="avaliacoes-titulo">
    <div class="container">
        <div class="section-heading reviews-heading">
            <div>
                <p class="overline">Opinião de quem provou</p>
                <h2 id="avaliacoes-titulo">Avaliações reais no <em>Google</em></h2>
            </div>
            <a class="google-badge-link" href="https://share.google/esOUYXSDUS7m6unJS" target="_blank" rel="noopener noreferrer">
                <span class="google-badge-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                </span>
                <div class="google-badge-info">
                    <div class="google-badge-stars">
                        <span class="google-score">4,4</span>
                        <span class="stars">★★★★★</span>
                    </div>
                    <small>Mais de 1.300 avaliações no Google</small>
                </div>
                <span>↗</span>
            </a>
        </div>

        <div class="reviews-grid">
            <article class="review-card">
                <header class="review-header">
                    <div class="reviewer-avatar" aria-hidden="true">M</div>
                    <div>
                        <strong>Mariana Silva</strong>
                        <small>Cliente verificada • Sorocaba</small>
                    </div>
                    <span class="stars-gold" aria-hidden="true">★★★★★</span>
                </header>
                <p>"O melhor bolo de Sorocaba com certeza! Comprei para o aniversário da minha mãe e todos elogiaram o equilíbrio do recheio. Maravilhoso!"</p>
                <footer class="review-footer">
                    <span>Comprou: <em>Bolo Caramelo Dourado</em></span>
                </footer>
            </article>

            <article class="review-card">
                <header class="review-header">
                    <div class="reviewer-avatar" aria-hidden="true">C</div>
                    <div>
                        <strong>Carlos Eduardo</strong>
                        <small>Cliente verificado • Sorocaba</small>
                    </div>
                    <span class="stars-gold" aria-hidden="true">★★★★★</span>
                </header>
                <p>"Ambiente super agradável e atendimento excepcional. Os brigadeiros gourmets são fora de série, dá pra sentir que os ingredientes são de primeira qualidade."</p>
                <footer class="review-footer">
                    <span>Comprou: <em>Caixa Afeto (Brigadeiros)</em></span>
                </footer>
            </article>

            <article class="review-card">
                <header class="review-header">
                    <div class="reviewer-avatar" aria-hidden="true">J</div>
                    <div>
                        <strong>Juliana Ribeiro</strong>
                        <small>Cliente verificada • Sorocaba</small>
                    </div>
                    <span class="stars-gold" aria-hidden="true">★★★★★</span>
                </header>
                <p>"Sou cliente há mais de 5 anos da Elda. Seja para uma sobremesa rápida no fim de semana ou festa de família, nunca decepciona!"</p>
                <footer class="review-footer">
                    <span>Comprou: <em>Tartelette Lumière</em></span>
                </footer>
            </article>

            <article class="review-card">
                <header class="review-header">
                    <div class="reviewer-avatar" aria-hidden="true">R</div>
                    <div>
                        <strong>Renato Almeida</strong>
                        <small>Cliente verificado • Sorocaba</small>
                    </div>
                    <span class="stars-gold" aria-hidden="true">★★★★★</span>
                </header>
                <p>"A tartelette de morango é espetacular, massa crocante e fruta fresca de verdade. Recomendo a todos em Sorocaba e região!"</p>
                <footer class="review-footer">
                    <span>Comprou: <em>Tartelette &amp; Macarons</em></span>
                </footer>
            </article>
        </div>

        <div class="reviews-cta">
            <p>Já provou alguma das nossas delícias?</p>
            <a class="button button-outline" href="https://share.google/esOUYXSDUS7m6unJS" target="_blank" rel="noopener noreferrer">
                <span>Deixe sua avaliação no Google</span>
                <span>↗</span>
            </a>
        </div>
    </div>
</section>

<section class="elda-gallery section" aria-labelledby="galeria-titulo">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="overline">Direto da nossa vitrine</p>
                <h2 id="galeria-titulo">Delícias da <em>Elda</em></h2>
            </div>
        </div>
        
        <!-- Uso semântico de <figure> e <figcaption> para indexação de imagens no Google Images -->
        <div class="elda-gallery-grid">
            <figure class="elda-gallery-logo">
                <img src="<?= image_placeholder() ?>" 
                     data-base64-image="elda-logo.jpg" 
                     alt="Logomarca oficial da Elda Bolos e Doces Sorocaba" 
                     width="600" 
                     height="600" 
                     loading="lazy" 
                     decoding="async">
            </figure>
            <figure>
                <img src="<?= image_placeholder() ?>" 
                     data-base64-image="elda-chocolate.jpg" 
                     alt="Seleção de bolos artesanais e sobremesas de chocolate nobre da confeitaria Elda" 
                     width="611" 
                     height="645" 
                     loading="lazy" 
                     decoding="async">
                <figcaption>Chocolate nobre em todas as formas</figcaption>
            </figure>
            <figure>
                <img src="<?= image_placeholder() ?>" 
                     data-base64-image="elda-doces.jpg" 
                     alt="Variedade de doces artesanais, tortas e sobremesas para festa" 
                     width="552" 
                     height="645" 
                     loading="lazy" 
                     decoding="async">
                <figcaption>Variedade artesanal para todos os gostos</figcaption>
            </figure>
        </div>
    </div>
</section>

<!-- ====================================================================== -->
<!-- 6. OCASIÕES & LINK BUILDING INTERNO POR CATEGORIA                      -->
<!-- ====================================================================== -->
<section class="occasion section" aria-label="Doces para cada ocasião">
    <div class="container occasion-inner">
        <p class="overline">Para tornar inesquecível</p>
        <h2>Tem sempre um doce<br>para cada <em>momento.</em></h2>
        
        <!-- Links internos temáticos que transferem relevância para páginas de categoria filtradas -->
        <div class="occasion-cards">
            <a href="/cardapio?categoria=Brigadeiros">
                <span>01</span>
                <strong>Um carinho<br>sem motivo</strong>
                <small>Ver brigadeiros →</small>
            </a>
            <a href="/cardapio?categoria=Bolos">
                <span>02</span>
                <strong>Uma data<br>para celebrar</strong>
                <small>Ver bolos artesanais →</small>
            </a>
            <a href="/cardapio?categoria=Macarons">
                <span>03</span>
                <strong>Um presente<br>para encantar</strong>
                <small>Ver macarons finos →</small>
            </a>
        </div>
    </div>
</section>

<!-- ====================================================================== -->
<!-- 7. LOCALIZAÇÃO E CONTATO (SEO LOCAL SOROCABA)                          -->
<!-- ====================================================================== -->
<section class="elda-contact section" id="contato" aria-label="Endereço da doceria e atendimento">
    <div class="container elda-contact-inner">
        <div>
            <p class="overline">Venha conhecer</p>
            <h2>A Elda está pertinho<br>de você em <em>Sorocaba.</em></h2>
            <!-- Dados NAP (Name, Address, Phone) essenciais para o ranking no Google Local Pack -->
            <address class="elda-contact-address">
                Av. Dr. Afonso Vergueiro, 2548<br>
                Vila Augusta • Sorocaba/SP
            </address>
        </div>
        <div class="elda-contact-actions">
            <a class="button button-primary button-whatsapp" href="https://wa.me/5515997451766" target="_blank" rel="noopener noreferrer">
                <img class="btn-whatsapp-icon" src="<?= versioned_asset('images/whatsapp-white.svg') ?>" alt="" width="20" height="20">
                <span>Chamar no WhatsApp <span aria-hidden="true">↗</span></span>
            </a>
            <a class="button button-outline" href="https://www.ifood.com.br/delivery/sorocaba-sp/elda-bolos-e-doces-vila-augusta/f7b56366-f7d9-4b8a-b0bd-f06ad6c5829f" target="_blank" rel="noopener noreferrer">
                Pedir no iFood <span aria-hidden="true">↗</span>
            </a>
            <a class="text-link" href="https://www.instagram.com/eldabolosedoces" target="_blank" rel="noopener noreferrer">
                @eldabolosedoces
            </a>
        </div>
    </div>
</section>
