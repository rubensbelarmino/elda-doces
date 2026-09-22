<?php
/**
 * ============================================================================
 * REDEFINIÇÃO DE SENHA (NOVA SENHA) — ELDA BOLOS E DOCES
 * ============================================================================
 * Formulário para validação do código aleatório recebido por e-mail e criação
 * de uma nova senha segura criptografada com Bcrypt cost 12.
 * ============================================================================
 */
?>
<section class="auth-page" aria-label="Redefinição de senha">
    <!-- Coluna Artística / Institucional -->
    <div class="auth-art">
        <div>
            <p class="overline">Nova credencial</p>
            <h1>Crie sua nova<br><em>senha segura.</em></h1>
            <p>Informe o código aleatório que enviamos para o seu e-mail e defina uma nova senha para acessar sua conta.</p>
        </div>
    </div>

    <!-- Painel com Formulário de Redefinição -->
    <div class="auth-panel">
        <div class="auth-box">
            <p class="overline">Confirmação de segurança</p>
            <h2>Redefinir senha</h2>
            <p>Não recebeu o código? <a href="/esqueci-senha">Solicitar novamente</a></p>

            <form method="post" action="/redefinir-senha">
                <!-- Proteção criptográfica contra ataques CSRF -->
                <input type="hidden" name="_token" value="<?= csrf_token() ?>">

                <label>
                    E-mail cadastrado
                    <input type="email" 
                           name="email" 
                           value="<?= e($email ?? '') ?>" 
                           autocomplete="email" 
                           maxlength="190" 
                           required 
                           placeholder="voce@exemplo.com">
                </label>

                <label>
                    Código recebido por e-mail
                    <input type="text" 
                           name="code" 
                           class="code-input-reset"
                           autocomplete="off" 
                           spellcheck="false"
                           maxlength="32" 
                           required 
                           autofocus
                           placeholder="Ex: W9#k7@mB!4-x">
                    <small class="code-reset-hint">O código contém letras maiúsculas, minúsculas, números e símbolos do teclado.</small>
                </label>

                <label>
                    Nova senha
                    <input type="password" 
                           name="password" 
                           autocomplete="new-password" 
                           minlength="8" 
                           maxlength="72" 
                           required 
                           placeholder="Mínimo 8 caracteres">
                </label>

                <label>
                    Confirmar nova senha
                    <input type="password" 
                           name="password_confirmation" 
                           autocomplete="new-password" 
                           minlength="8" 
                           maxlength="72" 
                           required 
                           placeholder="Repita a nova senha">
                </label>

                <button class="button button-primary button-block" type="submit">
                    Redefinir senha e entrar <span aria-hidden="true">→</span>
                </button>

                <a href="/entrar" class="button button-outline button-block auth-forgot-btn">
                    Voltar ao login
                </a>
            </form>
        </div>
    </div>
</section>
