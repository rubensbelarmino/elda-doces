<?php
/**
 * ============================================================================
 * CHECKOUT & PAGAMENTO PIX — ELDA BOLOS E DOCES
 * ============================================================================
 * Etapa final de conversão da loja onde o cliente insere os dados de entrega
 * e confirma o pagamento instantâneo via PIX do Mercado Pago.
 *
 * TÉCNICAS DE UX, SEGURANÇA E ACESSIBILIDADE:
 * ----------------------------------------------------------------------------
 * 1. ATRIBUTOS SEMÂNTICOS DE AUTOCOMPLETE & INPUTMODE:
 *    - autocomplete="name", "tel", "postal-code", "street-address", "address-level2":
 *      Permite que navegadores modernos e smartphones preencham os campos com 1 clique,
 *      aumentando drasticamente a taxa de conversão em dispositivos móveis.
 *    - inputmode="numeric" e inputmode="tel": Abrem o teclado numérico automaticamente
 *      no celular para CPF, CEP e Telefone.
 *
 * 2. MÁSCARAS JAVASCRIPT NÃO INVASIVAS:
 *    - Validação de CPF e máscaras em tempo real formatam visualmente sem quebrar
 *      a submissão dos dados limpos para o backend.
 *
 * 3. SEGURANÇA DA INFORMAÇÃO & LGPD:
 *    - Rota protegida por autenticação obrigatória (require_auth).
 *    - Bloqueada de indexação de robôs de busca no robots.txt (Disallow: /checkout).
 *    - Proteção rigorosa contra CSRF via token criptográfico.
 * ============================================================================
 */
?>

<!-- ====================================================================== -->
<!-- 1. CABEÇALHO DO CHECKOUT                                               -->
<!-- ====================================================================== -->
<section class="page-hero mini" aria-label="Finalização do pedido">
    <div class="container">
        <p class="overline">Último detalhe antes do preparo</p>
        <h1>Finalizar <em>pedido</em></h1>
    </div>
</section>

<!-- ====================================================================== -->
<!-- 2. FORMULÁRIO DE ENTREGA E PAGAMENTO PIX                               -->
<!-- ====================================================================== -->
<section class="section checkout-page" aria-label="Dados do pedido e entrega">
    <div class="container">
        <form class="checkout-grid" method="post" action="/checkout">
            <!-- Token anti-CSRF para validação da requisição POST -->
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <div class="checkout-form">
                <!-- Etapa 1: Endereço de Entrega em Sorocaba -->
                <div class="form-section">
                    <span class="step-number" aria-hidden="true">01</span>
                    <div>
                        <h2>Dados de entrega</h2>
                        <p>Informe onde nossas delícias artesanais devem ser entregues.</p>
                    </div>
                </div>

                <div class="form-grid">
                    <!-- Nome do Destinatário -->
                    <label class="full">
                        Nome de quem recebe
                        <input name="name" 
                               type="text"
                               maxlength="100" 
                               autocomplete="name" 
                               value="<?= e($user['name']) ?>" 
                               required>
                    </label>

                    <!-- E-mail (somente leitura para integridade da conta) -->
                    <label>
                        E-mail cadastrado
                        <input type="email" 
                               value="<?= e($user['email']) ?>" 
                               readonly 
                               aria-readonly="true">
                    </label>

                    <!-- CPF para emissão da cobrança no Mercado Pago -->
                    <label>
                        CPF do pagador
                        <input name="cpf" 
                               type="text"
                               inputmode="numeric" 
                               autocomplete="off" 
                               maxlength="14" 
                               placeholder="000.000.000-00" 
                               required>
                    </label>

                    <!-- Telefone/WhatsApp para contato do entregador -->
                    <label>
                        Telefone / WhatsApp
                        <input name="phone" 
                               type="tel"
                               inputmode="tel" 
                               autocomplete="tel" 
                               maxlength="20" 
                               placeholder="(15) 99745-1766" 
                               required>
                    </label>

                    <!-- CEP de Sorocaba ou região -->
                    <label>
                        CEP
                        <input name="zip" 
                               type="text"
                               inputmode="numeric" 
                               autocomplete="postal-code" 
                               maxlength="9" 
                               placeholder="18000-000" 
                               required>
                    </label>

                    <!-- Endereço / Logradouro -->
                    <label class="wide">
                        Endereço (Rua, Avenida)
                        <input name="address" 
                               type="text"
                               maxlength="180" 
                               autocomplete="street-address" 
                               placeholder="Ex: Av. Dr. Afonso Vergueiro" 
                               required>
                    </label>

                    <!-- Número -->
                    <label>
                        Número
                        <input name="number" 
                               type="text"
                               maxlength="20" 
                               placeholder="123" 
                               required>
                    </label>

                    <!-- Complemento -->
                    <label>
                        Complemento
                        <input name="complement" 
                               type="text"
                               maxlength="80" 
                               placeholder="Apto, Bloco, Casa 2">
                    </label>

                    <!-- Cidade / UF -->
                    <label class="wide">
                        Cidade / UF
                        <input name="city" 
                               type="text"
                               maxlength="80" 
                               autocomplete="address-level2" 
                               value="Sorocaba / SP" 
                               required>
                    </label>
                </div>

                <!-- Etapa 2: Forma de Pagamento PIX -->
                <div class="form-section second">
                    <span class="step-number" aria-hidden="true">02</span>
                    <div>
                        <h2>Pagamento via PIX</h2>
                        <p>O QR Code e código copia e cola são gerados de forma segura pelo Mercado Pago.</p>
                    </div>
                </div>

                <div class="pix-option">
                    <span aria-hidden="true">◇</span>
                    <div>
                        <strong>PIX Oficial Mercado Pago</strong>
                        <small>Aprovação imediata e sem taxas extras</small>
                    </div>
                    <b>Selecionado</b>
                </div>
            </div>

            <!-- Resumo Lateral do Pedido (Order Summary) -->
            <aside class="order-summary checkout-summary" aria-label="Itens do pedido e valor final">
                <p class="overline">Sua seleção</p>
                <h2><?= $cart['count'] ?> <?= $cart['count'] === 1 ? 'doce escolhido' : 'doces escolhidos' ?></h2>

                <!-- Detalhamento dos itens com pequenas imagens -->
                <?php foreach ($cart['items'] as $line): ?>
                    <div class="checkout-line">
                        <img src="<?= image_placeholder() ?>" 
                             data-base64-image="<?= e($line['product']['image']) ?>" 
                             alt="<?= e($line['product']['name']) ?>"
                             width="60" 
                             height="50">
                        <div>
                            <strong><?= e($line['product']['name']) ?></strong>
                            <small><?= (int) $line['quantity'] ?> × <?= money((int) $line['product']['price_cents']) ?></small>
                        </div>
                        <span><?= money($line['line_cents']) ?></span>
                    </div>
                <?php endforeach; ?>

                <!-- Valores totais com frete -->
                <dl>
                    <div>
                        <dt>Subtotal</dt>
                        <dd><?= money($cart['subtotal_cents']) ?></dd>
                    </div>
                    <div>
                        <dt>Taxa de Entrega</dt>
                        <dd><?= $cart['shipping_cents'] === 0 ? 'Grátis' : money($cart['shipping_cents']) ?></dd>
                    </div>
                </dl>

                <div class="summary-total">
                    <span>Total a Pagar</span>
                    <strong><?= money($cart['total_cents']) ?></strong>
                </div>

                <!-- Botão de submissão do checkout -->
                <button class="button button-primary button-block" type="submit">
                    Gerar PIX seguro <span aria-hidden="true">→</span>
                </button>

                <p class="summary-secure">
                    ⌁ Transação criptografada pelo Mercado Pago
                </p>
            </aside>
        </form>
    </div>
</section>
