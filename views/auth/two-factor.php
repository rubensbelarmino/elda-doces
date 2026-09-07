<?php
/**
 * ============================================================================
 * VERIFICAÇÃO EM DUAS ETAPAS (2FA VIA E-MAIL) — ELDA BOLOS E DOCES
 * ============================================================================
 * Interface onde o usuário digita o código numérico de 6 dígitos recebido por e-mail.
 *
 * TÉCNICAS DE SEGURANÇA E EXPERIÊNCIA DO USUÁRIO (UX):
 * ----------------------------------------------------------------------------
 * 1. CAMADA AVANÇADA DE PROTEÇÃO DA CONTA:
 *    - Código de 6 dígitos numéricos aleatórios com validade de 10 minutos.
 *    - Assinatura criptográfica com HMAC-SHA256 utilizando chave de cluster (APP_KEY),
 *      garantindo que o código não possa ser adulterado na sessão.
 *    - Limite de 5 tentativas incorretas antes de invalidar a sessão por segurança.
 *
 * 2. RECURSOS MODERNOS DE USABILIDADE MOBILE:
 *    - autocomplete="one-time-code": Permite que iOS e Android sugiram o código
 *      automaticamente a partir da notificação do sistema.
 *    - inputmode="numeric" e pattern="[0-9]{6}": Aciona o teclado numérico
 *      sem caracteres desnecessários no smartphone.
 *    - Contador regressivo via JavaScript (data-countdown) informando o tempo restante.
 * ============================================================================
 */
?>
<section class="auth-page two-factor-page" aria-label="Verificação de segurança em duas etapas">
    <!-- Coluna Visual da Segurança -->
    <div class="auth-art two-factor-art">
        <div>
            <p class="overline">Segurança em duas etapas</p>
            <h1>Seu acesso,<br><em>bem protegido.</em></h1>
            <p>Uma camada extra de cuidado artesanal para proteger seus pedidos e dados pessoais.</p>
            <div class="security-seal">
                <span aria-hidden="true">✓</span>
                <div>
                    <strong>Código único e temporário</strong>
                    <small>Expira em 10 minutos por segurança</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Painel de Inserção do Código -->
    <div class="auth-panel">
        <div class="auth-box two-factor-box">
            <div class="mail-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M3 6h18v12H3V6Zm0 1 9 7 9-7"/></svg>
            </div>

            <p class="overline">Verifique sua caixa de entrada</p>
            <h2>Digite o código de 6 dígitos</h2>
            <p>Enviamos um código de verificação para <strong><?= e($maskedEmail) ?></strong>.</p>

            <form method="post" action="/verificar-codigo" data-two-factor-form>
                <!-- Token anti-CSRF -->
                <input type="hidden" name="_token" value="<?= csrf_token() ?>">

                <label class="code-label">
                    Código de verificação
                    <input class="code-input" 
                           name="code" 
                           type="text"
                           inputmode="numeric" 
                           autocomplete="one-time-code" 
                           pattern="[0-9]{6}" 
                           maxlength="6" 
                           required 
                           autofocus 
                           placeholder="000000" 
                           aria-describedby="code-help">
                </label>

                <!-- Contador regressivo dinâmico controlado por data attributes no app.js -->
                <small id="code-help" class="code-help">
                    O código expira em <strong data-countdown data-expires="<?= $expiresAt ?>">10:00</strong>
                </small>

                <button class="button button-primary button-block" type="submit">
                    Confirmar acesso <span aria-hidden="true">→</span>
                </button>
            </form>

            <!-- Reenvio com proteção de cooldown (60s entre envios) -->
            <form class="resend-form" method="post" action="/verificar-codigo/reenviar">
                <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                <span>Não recebeu o e-mail?</span>
                <button type="submit">Reenviar código</button>
            </form>

            <a class="back-login" href="/entrar">← Voltar para a tela de login</a>
        </div>
    </div>
</section>

<!-- 2FA Sec: token lifecycle & strict validation -->
