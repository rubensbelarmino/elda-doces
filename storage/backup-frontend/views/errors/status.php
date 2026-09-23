<?php
// /**
//  * ============================================================================
//  * VIEW DE ERROS HTTP PERSONALIZADA — ELDA BOLOS E DOCES
//  * ============================================================================
//  * Template único e versátil utilizado para renderizar erros 400, 401, 403, 404,
//  * 413, 429, 500 e 503 com identidade visual aconchegante e amigável.
//  *
//  * TÉCNICAS DE SEO & UX APLICADAS NESTE ARQUIVO:
//  * ----------------------------------------------------------------------------
//  * 1. PREVENÇÃO DE PERDA DE VISITANTES (REDUÇÃO DE CHURN):
//  *    - Quando um usuário digita uma URL incorreta ou acessa um produto esgotado,
//  *      uma página 404 genérica do servidor gera abandono imediato.
//  *    - Este template oferece rotas claras de recuperação: botão para a Home,
//  *      link para o cardápio e canal de atendimento direto via WhatsApp.
//  *
//  * 2. SEGURANÇA E HIGIENE DE CABEÇALHOS:
//  *    - Respostas com código 4xx ou 5xx são configuradas com cabeçalhos adequados
//  *      pelo backend antes de invocar esta view, garantindo que o Googlebot não
//  *      armazene conteúdo de erro no índice de pesquisa.
//  * ============================================================================
//  */
// ?>
// <section class="error-page" aria-label="Aviso do sistema com código HTTP <?= (int) $code ?>">
//     <!-- Ilustração visual estilizada com código de erro em destaque -->
//     <div class="error-art" aria-hidden="true">
//         <span class="error-code"><?= (int) $code ?></span>
//         <div class="error-dessert">
//             <i></i><i></i><i></i><b>✦</b>
//         </div>
//     </div>
//
//     <!-- Mensagem explicativa em linguagem humana e acolhedora -->
//     <div class="error-copy">
//         <p class="overline">Status do servidor: Erro <?= (int) $code ?></p>
//         <h1><?= e($title) ?>.</h1>
//         <p><?= e($message) ?></p>
//
//         <!-- Ações claras de saída e continuidade de navegação -->
//         <div>
//             <a class="button button-primary" href="/">
//                 Voltar à página inicial <span aria-hidden="true">→</span>
//             </a>
//             <a class="text-link" href="/cardapio">
//                 Explorar nosso cardápio
//             </a>
//         </div>
//
//         <small>
//             Precisa de ajuda imediata? Chame a gente no 
//             <a href="https://wa.me/5515997451766" target="_blank" rel="noopener noreferrer">
//                 WhatsApp: (15) 99745-1766
//             </a>
//         </small>
//     </div>
// </section>
