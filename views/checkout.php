<section class="page-hero mini" aria-label="Finalização do pedido">
    <div class="container">
        <p class="overline">Último detalhe antes do preparo</p>
        <h1>Finalizar <em>pedido</em></h1>
    </div>
</section>

<section class="section checkout-page" aria-label="Dados do pedido e entrega">
    <div class="container">
        <form class="checkout-grid" method="post" action="/checkout">

            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <div class="checkout-form">

                <div class="form-section">
                    <span class="step-number" aria-hidden="true">01</span>
                    <div>
                        <h2>Dados de entrega</h2>
                        <p>Informe onde nossas delícias artesanais devem ser entregues.</p>
                    </div>
                </div>

                <div class="form-grid">

                    <label class="full">
                        Nome de quem recebe
                        <input name="name"
                               type="text"
                               maxlength="100"
                               autocomplete="name"
                               value="<?= e(!empty($savedAddress['name']) ? $savedAddress['name'] : $user['name']) ?>"
                               required>
                    </label>

                    <label>
                        E-mail cadastrado
                        <input type="email"
                               value="<?= e($user['email']) ?>"
                               readonly
                               aria-readonly="true">
                    </label>

                    <label>
                        CPF do pagador
                        <input name="cpf"
                               type="text"
                               inputmode="numeric"
                               autocomplete="off"
                               maxlength="14"
                               placeholder="000.000.000-00"
                               value="<?= e($savedAddress['cpf'] ?? '') ?>"
                               required>
                    </label>

                    <label>
                        Telefone / WhatsApp
                        <input name="phone"
                               type="tel"
                               inputmode="tel"
                               autocomplete="tel"
                               maxlength="20"
                               placeholder="(15) 99745-1766"
                               value="<?= e($savedAddress['phone'] ?? '') ?>"
                               required>
                    </label>

                    <label>
                        CEP
                        <input name="zip"
                               type="text"
                               inputmode="numeric"
                               autocomplete="postal-code"
                               maxlength="9"
                               placeholder="18000-000"
                               value="<?= e($savedAddress['zip'] ?? '') ?>"
                               required>
                    </label>

                    <label class="wide">
                        Endereço (Rua, Avenida)
                        <input name="address"
                               type="text"
                               maxlength="180"
                               autocomplete="street-address"
                               placeholder="Ex: Av. Dr. Afonso Vergueiro"
                               value="<?= e($savedAddress['address'] ?? '') ?>"
                               required>
                    </label>

                    <label>
                        Número
                        <input name="number"
                               type="text"
                               maxlength="20"
                               placeholder="123"
                               value="<?= e($savedAddress['number'] ?? '') ?>"
                               required>
                    </label>

                    <label>
                        Complemento
                        <input name="complement"
                               type="text"
                               maxlength="80"
                               placeholder="Apto, Bloco, Casa 2"
                               value="<?= e($savedAddress['complement'] ?? '') ?>">
                    </label>

                    <label class="wide">
                        Cidade / UF
                        <input name="city"
                               type="text"
                               maxlength="80"
                               autocomplete="address-level2"
                               value="<?= e(!empty($savedAddress['city']) ? $savedAddress['city'] : 'Sorocaba / SP') ?>"
                               required>
                    </label>
                </div>

                <div class="save-address-wrapper">
                    <label class="save-address-label" for="save_address">
                        <input type="checkbox"
                               name="save_address"
                               id="save_address"
                               value="1"
                               <?= (!empty($savedAddress) || !isset($savedAddress)) ? 'checked' : '' ?>>
                        <span class="save-address-text">Salvar estes dados de entrega para as próximas compras</span>
                    </label>
                </div>

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

            <aside class="order-summary checkout-summary" aria-label="Itens do pedido e valor final">
                <p class="overline">Sua seleção</p>
                <h2><?= $cart['count'] ?> <?= $cart['count'] === 1 ? 'doce escolhido' : 'doces escolhidos' ?></h2>

                <?php foreach ($cart['items'] as $line): ?>
                    <div class="checkout-line">
                        <img src="<?= versioned_asset('images/' . $line['product']['image']) ?>"
                             data-base64-image="<?= e($line['product']['image']) ?>"
                             alt="<?= e($line['product']['name']) ?>"
                             width="60"
                             height="50"
                             loading="lazy"
                             decoding="async">
                        <div>
                            <strong><?= e($line['product']['name']) ?></strong>
                            <small><?= (int) $line['quantity'] ?> × <?= money((int) $line['product']['price_cents']) ?></small>
                        </div>
                        <span><?= money($line['line_cents']) ?></span>
                    </div>
                <?php endforeach; ?>

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
