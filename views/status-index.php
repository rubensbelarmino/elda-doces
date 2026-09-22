<?php

$statusCodes = [
    400 => 'Requisição inválida (Bad Request)',
    401 => 'Não autenticado (Unauthorized)',
    403 => 'Acesso proibido (Forbidden)',
    404 => 'Página não encontrada (Not Found)',
    429 => 'Muitas requisições / Rate Limit (Too Many Requests)',
    500 => 'Erro interno do servidor (Internal Server Error)',
    503 => 'Serviço temporariamente indisponível (Service Unavailable)'
];
?>

<section class="page-hero compact" aria-label="Status do sistema">
    <div class="container">
        <p class="overline">Observabilidade &amp; Confiabilidade</p>
        <h1>Status do <em>sistema</em></h1>
        <p>Páginas personalizadas e respostas HTTP semanticamente corretas para cada cenário da aplicação.</p>
    </div>
</section>

<section class="section status-page" aria-label="Verificação dos sistemas">
    <div class="container">

        <div class="system-ok">
            <span aria-hidden="true"></span>
            <div>
                <strong>Todos os sistemas operacionais</strong>
                <small>Loja, catálogo, carrinho, autenticação 2FA e banco de dados respondendo normalmente.</small>
            </div>
            <code>200 OK</code>
        </div>

        <div class="status-grid">
            <?php foreach ($statusCodes as $itemCode => $label): ?>
                <a href="/status/<?= $itemCode ?>" aria-label="Visualizar página personalizada para o status HTTP <?= $itemCode ?>">
                    <strong><?= $itemCode ?></strong>
                    <span><?= e($label) ?></span>
                    <i aria-hidden="true">Visualizar resposta →</i>
                </a>
            <?php endforeach; ?>
        </div>

        <p class="status-note">
            Nota técnica: Os links acima retornam intencionalmente o código HTTP indicado nos cabeçalhos da resposta, permitindo auditar a experiência do usuário e o comportamento de crawlers.
        </p>
    </div>
</section>
