<?php
/**
 * ============================================================================
 * CATÁLOGO DE STATUS & OBSERVABILIDADE HTTP — ELDA BOLOS E DOCES
 * ============================================================================
 * Esta página serve para auditoria técnica e demonstração das respostas HTTP
 * personalizadas tratadas pela aplicação (400, 401, 403, 404, 429, 500, 503).
 *
 * TÉCNICAS DE SEO & ENGENHARIA DE SOFTWARE:
 * ----------------------------------------------------------------------------
 * 1. RESPOSTAS HTTP CORRETAS PARA OS MOTORES DE BUSCA:
 *    - Páginas de erro que retornam 200 OK acidentalmente criam o chamado
 *      "Soft 404", que confunde os robôs do Google e prejudica o ranqueamento.
 *    - Nosso sistema garante que cada página de erro emita o código de status
 *      HTTP correspondente real (ex: http_response_code(404)).
 *
 * 2. USER EXPERIENCE (UX) EM CENÁRIOS DE FALHA:
 *    - Mensagens acolhedoras alinhadas ao tom de voz artesanal da confeitaria.
 *    - Links visíveis para retornar à Home ou falar no WhatsApp, evitando que
 *      o visitante abandone o site caso encontre uma URL quebrada.
 * ============================================================================
 */

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

<!-- ====================================================================== -->
<!-- 1. CABEÇALHO DO STATUS                                                 -->
<!-- ====================================================================== -->
<section class="page-hero compact" aria-label="Status do sistema">
    <div class="container">
        <p class="overline">Observabilidade &amp; Confiabilidade</p>
        <h1>Status do <em>sistema</em></h1>
        <p>Páginas personalizadas e respostas HTTP semanticamente corretas para cada cenário da aplicação.</p>
    </div>
</section>

<!-- ====================================================================== -->
<!-- 2. PAINEL DE MONITORAMENTO E LINKS DE TESTE                            -->
<!-- ====================================================================== -->
<section class="section status-page" aria-label="Verificação dos sistemas">
    <div class="container">
        <!-- Indicador Geral de Saúde dos Serviços -->
        <div class="system-ok">
            <span aria-hidden="true"></span>
            <div>
                <strong>Todos os sistemas operacionais</strong>
                <small>Loja, catálogo, carrinho, autenticação 2FA e banco de dados respondendo normalmente.</small>
            </div>
            <code>200 OK</code>
        </div>

        <!-- Grade de Códigos de Status HTTP para Validação -->
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
