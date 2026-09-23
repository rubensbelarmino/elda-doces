<?php
// /**
//  * ============================================================================
//  * PÁGINA DE LOGIN / AUTENTICAÇÃO — ELDA BOLOS E DOCES
//  * ============================================================================
//  * Tela de acesso para clientes e administradores da doceria.
//  *
//  * TÉCNICAS DE SEGURANÇA, UX E DIRETIVAS DE BUSCA:
//  * ----------------------------------------------------------------------------
//  * 1. DIRETIVAS DE PRIVACIDADE E BUSCA:
//  *    - Páginas de login não contêm conteúdo de valor para busca orgânica e,
//  *      portanto, contêm cabeçalho Cache-Control: no-store, private.
//  *    - Protegida por rate limiting agressivo (2.000 requisições com bloqueio
//  *      automático por 5 minutos) contra ataques de força bruta.
//  *
//  * 2. ACESSIBILIDADE E PREENCHIMENTO AUTOMÁTICO:
//  *    - Atributos autocomplete="email" e autocomplete="current-password" permitem
//  *      integração perfeita com gerenciadores de senha (1Password, Bitwarden, Google).
//  *
//  * 3. FLUXO DE SEGURANÇA EM DUAS ETAPAS (2FA):
//  *    - A submissão bem-sucedida de credenciais válidas não abre a sessão
//  *      imediatamente, disparando o envio de um código de 6 dígitos para o e-mail.
//  * ============================================================================
//  */
// ?>
// <section class="auth-page" aria-label="Acesso à conta do cliente">
//     <!-- Coluna Artística / Institucional -->
//     <div class="auth-art">
//         <div>
//             <p class="overline">Seu cantinho doce</p>
//             <h1>Bom ter você<br><em>de volta.</em></h1>
//             <p>Acesse seus pedidos anteriores e acompanhe cada etapa do preparo da sua encomenda.</p>
//         </div>
//     </div>
//
//     <!-- Painel com Formulário de Login -->
//     <div class="auth-panel">
//         <div class="auth-box">
//             <p class="overline">Área do cliente</p>
//             <h2>Entre na sua conta</h2>
//             <p>Ainda não tem uma conta cadastrada? <a href="/criar-conta">Criar agora</a></p>
//
//             <form method="post" action="/entrar">
//                 <!-- Proteção criptográfica contra ataques CSRF -->
//                 <input type="hidden" name="_token" value="<?= csrf_token() ?>">
//
//                 <label>
//                     E-mail
//                     <input type="email" 
//                            name="email" 
//                            autocomplete="email" 
//                            maxlength="190" 
//                            required 
//                            placeholder="voce@exemplo.com">
//                 </label>
//
//                 <label>
//                     <span class="auth-label-row">
//                         <span>Senha</span>
//                         <a href="/esqueci-senha" class="auth-forgot-link">Esqueci a senha?</a>
//                     </span>
//                     <input type="password" 
//                            name="password" 
//                            autocomplete="current-password" 
//                            maxlength="72" 
//                            required 
//                            placeholder="Sua senha secreta">
//                 </label>
//
//                 <button class="button button-primary button-block" type="submit">
//                     Entrar <span aria-hidden="true">→</span>
//                 </button>
//
//                 <a href="/esqueci-senha" class="button button-outline button-block auth-forgot-btn">
//                     Esqueci a senha
//                 </a>
//             </form>
//         </div>
//     </div>
// </section>
