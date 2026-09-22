<section class="error-page" aria-label="Aviso do sistema com código HTTP <?= (int) $code ?>">

    <div class="error-art" aria-hidden="true">
        <span class="error-code"><?= (int) $code ?></span>
        <div class="error-dessert">
            <i></i><i></i><i></i><b>✦</b>
        </div>
    </div>

    <div class="error-copy">
        <p class="overline">Status do servidor: Erro <?= (int) $code ?></p>
        <h1><?= e($title) ?>.</h1>
        <p><?= e($message) ?></p>

        <div>
            <a class="button button-primary" href="/">
                Voltar à página inicial <span aria-hidden="true">→</span>
            </a>
            <a class="text-link" href="/cardapio">
                Explorar nosso cardápio
            </a>
        </div>

        <small>
            Precisa de ajuda imediata? Chame a gente no
            <a href="https://wa.me/5515997451766" target="_blank" rel="noopener noreferrer">
                WhatsApp: (15) 99745-1766
            </a>
        </small>
    </div>
</section>
