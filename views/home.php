<section class="hero" aria-label="Apresentação da confeitaria">

    <img class="hero-bg"
         src="<?= asset('images/hero-doces.webp') ?>"
         alt="Vitrine com variedade de bolos artesanais, tortas e doces da Elda"
         width="1672"
         height="941"
         fetchpriority="high">

    <div class="container hero-content">

        <p class="overline">Elda Bolos e Doces • Sorocaba</p>

        <h1>O seu dia merece<br><em>um pedaço feliz.</em></h1>

        <p class="hero-copy">
            Bolos, tortas e doces caprichados, com variedade de verdade e porções generosas para dividir — ou não.
        </p>

        <div class="hero-actions">
            <a class="button button-primary" href="/cardapio">
                Ver nossas delícias <span aria-hidden="true">→</span>
            </a>
            <a class="text-link" href="https://www.ifood.com.br/delivery/sorocaba-sp/elda-bolos-e-doces-vila-augusta/f7b56366-f7d9-4b8a-b0bd-f06ad6c5829f" target="_blank" rel="noopener noreferrer">
                Pedir pelo iFood <span aria-hidden="true">↗</span>
            </a>
        </div>

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

<section class="section products-section" aria-label="Doces mais desejados">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="overline">Os mais desejados</p>
                <h2>Favoritos da <em>Elda</em></h2>
            </div>

            <a class="text-link" href="/cardapio">Ver cardápio completo <span aria-hidden="true">→</span></a>
        </div>

        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php require __DIR__ . '/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="story section" id="historia" aria-label="Nossa história em Sorocaba">
    <div class="container story-grid">
        <div class="story-photo">

            <img src="<?= image_placeholder() ?>"
                 data-base64-image="elda-morango.webp"
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
                    <img class="btn-whatsapp-icon" src="<?= versioned_asset('images/whatsapp-icon.svg') ?>" alt="Ícone do WhatsApp" role="presentation" aria-hidden="true" width="20" height="20">
                    <span>Falar no WhatsApp</span>
                </a>
                <a class="button button-outline button-instagram" href="https://www.instagram.com/eldabolosedoces" target="_blank" rel="noopener noreferrer">
                    <img class="btn-instagram-icon" src="<?= versioned_asset('images/instagram-icon.svg') ?>" alt="Ícone do Instagram" role="presentation" aria-hidden="true" width="20" height="20">
                    <span>Veja nosso Instagram</span>
                </a>
            </div>
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

        <div class="elda-gallery-grid">
            <figure class="elda-gallery-logo">
                <img src="<?= image_placeholder() ?>"
                     data-base64-image="elda-logo.webp"
                     alt="Logomarca oficial da Elda Bolos e Doces Sorocaba"
                     width="600"
                     height="600"
                     loading="lazy"
                     decoding="async">
            </figure>
            <figure>
                <img src="<?= image_placeholder() ?>"
                     data-base64-image="elda-chocolate.webp"
                     alt="Seleção de bolos artesanais e sobremesas de chocolate nobre da confeitaria Elda"
                     width="611"
                     height="645"
                     loading="lazy"
                     decoding="async">
                <figcaption>Chocolate nobre em todas as formas</figcaption>
            </figure>
            <figure>
                <img src="<?= image_placeholder() ?>"
                     data-base64-image="elda-doces.webp"
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

<section class="occasion section" aria-label="Doces para cada ocasião">
    <div class="container occasion-inner">
        <p class="overline">Para tornar inesquecível</p>
        <h2>Tem sempre um doce<br>para cada <em>momento.</em></h2>

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

<section class="elda-contact section" id="contato" aria-label="Endereço da doceria e atendimento">
    <div class="container elda-contact-inner">
        <div>
            <p class="overline">Venha conhecer</p>
            <h2>A Elda está pertinho<br>de você em <em>Sorocaba.</em></h2>

            <address class="elda-contact-address">
                Av. Dr. Afonso Vergueiro, 2548<br>
                Vila Augusta • Sorocaba/SP
            </address>
        </div>
        <div class="elda-contact-actions">
            <a class="button button-primary button-whatsapp" href="https://wa.me/5515997451766" target="_blank" rel="noopener noreferrer">
                <img class="btn-whatsapp-icon" src="<?= versioned_asset('images/whatsapp-white.svg') ?>" alt="Ícone do WhatsApp" role="presentation" aria-hidden="true" width="20" height="20">
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
