<section class="auth-page" aria-label="Acesso à conta do cliente">

    <div class="auth-art">
        <div>
            <p class="overline">Seu cantinho doce</p>
            <h1>Bom ter você<br><em>de volta.</em></h1>
            <p>Acesse seus pedidos anteriores e acompanhe cada etapa do preparo da sua encomenda.</p>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-box">
            <p class="overline">Área do cliente</p>
            <h2>Entre na sua conta</h2>
            <p>Ainda não tem uma conta cadastrada? <a href="/criar-conta">Criar agora</a></p>

            <form method="post" action="/entrar">

                <input type="hidden" name="_token" value="<?= csrf_token() ?>">

                <label>
                    E-mail
                    <input type="email"
                           name="email"
                           autocomplete="email"
                           maxlength="190"
                           required
                           placeholder="voce@exemplo.com">
                </label>

                <label>
                    <span class="auth-label-row">
                        <span>Senha</span>
                        <a href="/esqueci-senha" class="auth-forgot-link">Esqueci a senha?</a>
                    </span>
                    <div class="password-wrapper">
                        <input type="password"
                               name="password"
                               autocomplete="current-password"
                               maxlength="72"
                               required
                               placeholder="Sua senha secreta">
                        <button type="button"
                                class="password-toggle"
                                aria-label="Mostrar ou ocultar senha"
                                title="Mostrar ou ocultar senha"
                                tabindex="-1">
                            <svg class="eye-open" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </label>

                <button class="button button-primary button-block" type="submit">
                    Entrar <span aria-hidden="true">→</span>
                </button>
            </form>
        </div>
    </div>
</section>
