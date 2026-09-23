<?php
// /**
//  * ============================================================================
//  * RECUPERAÇÃO DE SENHA (ESQUECI A SENHA) — ELDA BOLOS E DOCES
//  * ============================================================================
//  * Permite ao usuário solicitar um código de redefinição de senha enviado
//  * para o e-mail cadastrado na plataforma.
//  * ============================================================================
//  */
// ?>
// <section class="auth-page" aria-label="Recuperação de acesso">
//     <!-- Coluna Artística / Institucional -->
//     <div class="auth-art">
//         <div>
//             <p class="overline">Recuperação de acesso</p>
//             <h1>Esqueceu sua<br><em>senha?</em></h1>
//             <p>Não se preocupe! Informe seu e-mail cadastrado e enviaremos um código aleatório com caracteres do teclado para você criar uma nova senha.</p>
//         </div>
//     </div>
//
//     <!-- Painel com Formulário de Esqueci a Senha -->
//     <div class="auth-panel">
//         <div class="auth-box">
//             <p class="overline">Recuperar conta</p>
//             <h2>Esqueci a senha</h2>
//             <p>Lembrou da sua senha? <a href="/entrar">Voltar ao login</a></p>
//
//             <form method="post" action="/esqueci-senha">
//                 <!-- Proteção criptográfica contra ataques CSRF -->
//                 <input type="hidden" name="_token" value="<?= csrf_token() ?>">
//
//                 <label>
//                     E-mail cadastrado
//                     <input type="email" 
//                            name="email" 
//                            value="<?= e($email ?? '') ?>" 
//                            autocomplete="email" 
//                            maxlength="190" 
//                            required 
//                            autofocus
//                            placeholder="voce@exemplo.com">
//                 </label>
//
//                 <button class="button button-primary button-block" type="submit">
//                     Enviar código de recuperação <span aria-hidden="true">→</span>
//                 </button>
//
//                 <a href="/entrar" class="button button-outline button-block auth-forgot-btn">
//                     Voltar ao login
//                 </a>
//             </form>
//         </div>
//     </div>
// </section>
