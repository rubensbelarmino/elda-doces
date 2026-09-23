<?php
// <section class="auth-page" aria-label="Criação de nova conta">
//
//     <div class="auth-art register-art">
//         <div>
//             <p class="overline">Uma conta só sua</p>
//             <h1>Mais perto<br>dos seus <em>favoritos.</em></h1>
//             <p>Salve seus endereços de entrega e acompanhe todas as suas encomendas em Sorocaba.</p>
//         </div>
//     </div>
//
//     <div class="auth-panel">
//         <div class="auth-box">
//             <p class="overline">Bem-vindo à Elda</p>
//             <h2>Crie sua conta</h2>
//             <p>Já possui cadastro conosco? <a href="/entrar">Entrar agora</a></p>
//
//             <form method="post" action="/criar-conta">
//
//                 <input type="hidden" name="_token" value="<?= csrf_token() ?>">
//
//                 <label>
//                     Nome completo
//                     <input type="text"
//                            name="name"
//                            autocomplete="name"
//                            maxlength="100"
//                            required
//                            placeholder="Como podemos chamar você?">
//                 </label>
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
//                     Senha de acesso
//                     <input type="password"
//                            name="password"
//                            autocomplete="new-password"
//                            minlength="8"
//                            maxlength="72"
//                            required
//                            placeholder="8+ caracteres com letras e números">
//                 </label>
//
//                 <button class="button button-primary button-block" type="submit">
//                     Criar minha conta <span aria-hidden="true">→</span>
//                 </button>
//             </form>
//         </div>
//     </div>
// </section>
