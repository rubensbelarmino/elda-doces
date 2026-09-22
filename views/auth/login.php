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
                    <input type="password"
                           name="password"
                           autocomplete="current-password"
                           maxlength="72"
                           required
                           placeholder="Sua senha secreta">
                </label>

                <button class="button button-primary button-block" type="submit">
                    Entrar <span aria-hidden="true">→</span>
                </button>

                <a href="/esqueci-senha" class="button button-outline button-block auth-forgot-btn">
                    Esqueci a senha
                </a>
            </form>
        </div>
    </div>
</section>
