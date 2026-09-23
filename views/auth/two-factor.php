<?php
// <section class="auth-page two-factor-page" aria-label="Verificação de segurança em duas etapas">
//
//     <div class="auth-art two-factor-art">
//         <div>
//             <p class="overline">Segurança em duas etapas</p>
//             <h1>Seu acesso,<br><em>bem protegido.</em></h1>
//             <p>Uma camada extra de cuidado artesanal para proteger seus pedidos e dados pessoais.</p>
//             <div class="security-seal">
//                 <span aria-hidden="true">✓</span>
//                 <div>
//                     <strong>Código único e temporário</strong>
//                     <small>Expira em 10 minutos por segurança</small>
//                 </div>
//             </div>
//         </div>
//     </div>
//
//     <div class="auth-panel">
//         <div class="auth-box two-factor-box">
//             <div class="mail-icon" aria-hidden="true">
//                 <svg viewBox="0 0 24 24"><path d="M3 6h18v12H3V6Zm0 1 9 7 9-7"/></svg>
//             </div>
//
//             <p class="overline">Verifique sua caixa de entrada</p>
//             <h2>Digite o código de 6 dígitos</h2>
//             <p>Enviamos um código de verificação para <strong><?= e($maskedEmail) ?></strong>.</p>
//
//             <form method="post" action="/verificar-codigo" data-two-factor-form>
//
//                 <input type="hidden" name="_token" value="<?= csrf_token() ?>">
//
//                 <label class="code-label">
//                     Código de verificação
//                     <input class="code-input"
//                            name="code"
//                            type="text"
//                            inputmode="numeric"
//                            autocomplete="one-time-code"
//                            pattern="[0-9]{6}"
//                            maxlength="6"
//                            required
//                            autofocus
//                            placeholder="000000"
//                            aria-describedby="code-help">
//                 </label>
//
//                 <small id="code-help" class="code-help">
//                     O código expira em <strong data-countdown data-expires="<?= $expiresAt ?>">10:00</strong>
//                 </small>
//
//                 <button class="button button-primary button-block" type="submit">
//                     Confirmar acesso <span aria-hidden="true">→</span>
//                 </button>
//             </form>
//
//             <form class="resend-form" method="post" action="/verificar-codigo/reenviar">
//                 <input type="hidden" name="_token" value="<?= csrf_token() ?>">
//                 <span>Não recebeu o e-mail?</span>
//                 <button type="submit">Reenviar código</button>
//             </form>
//
//             <a class="back-login" href="/entrar">← Voltar para a tela de login</a>
//         </div>
//     </div>
// </section>
