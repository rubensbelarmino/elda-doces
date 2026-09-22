<section class="page-hero mini" aria-label="Cabeçalho da sacola de compras">
    <div class="container">
        <p class="overline">Seu momento doce</p>
        <h1>Sua <em>sacola</em></h1>
    </div>
</section>

<section class="section cart-page" aria-label="Itens selecionados na sacola">
    <div class="container">
        <?php if ($cart['items'] === []): ?>

            <div class="empty-state">
                <span aria-hidden="true">♡</span>
                <h2>Sua sacola está vazia</h2>
                <p>Escolha uma delicadeza do nosso cardápio para deixar seu dia mais doce.</p>
                <a class="button button-primary" href="/cardapio">
                    Explorar cardápio completo
                </a>
            </div>
        <?php else: ?>
            <div class="cart-grid">

                <div>
                    <form method="post" action="/carrinho/atualizar">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">

                        <div class="cart-heading">
                            <h2><?= $cart['count'] ?> <?= $cart['count'] === 1 ? 'item adicionado' : 'itens adicionados' ?></h2>
                            <span>Subtotal</span>
                        </div>

                        <?php foreach ($cart['items'] as $line): ?>
                            <?php $product = $line['product']; ?>
                            <article class="cart-line">
                                <a href="/produto/<?= e($product['slug']) ?>" aria-label="Ver <?= e($product['name']) ?>">
                                    <img src="<?= image_placeholder() ?>"
                                         data-base64-image="<?= e($product['image']) ?>"
                                         alt="<?= e($product['name']) ?>"
                                         width="90"
                                         height="70">
                                </a>
                                <div>
                                    <span class="eyebrow"><?= e($product['category']) ?></span>
                                    <h3>
                                        <a href="/produto/<?= e($product['slug']) ?>"><?= e($product['name']) ?></a>
                                    </h3>
                                    <small><?= e($product['portion']) ?></small>
                                    <div class="line-actions">
                                        <label>
                                            Qtd.
                                            <input type="number"
                                                   name="quantity[<?= e($product['id']) ?>]"
                                                   min="0"
                                                   max="<?= (int) $product['stock'] ?>"
                                                   value="<?= (int) $line['quantity'] ?>"
                                                   aria-label="Quantidade de <?= e($product['name']) ?>">
                                        </label>
                                        <button class="link-button"
                                                type="submit"
                                                form="remove-<?= e($product['id']) ?>"
                                                aria-label="Remover <?= e($product['name']) ?> da sacola">
                                            Remover
                                        </button>
                                    </div>
                                </div>
                                <strong><?= money($line['line_cents']) ?></strong>
                            </article>
                        <?php endforeach; ?>

                        <button class="button button-outline" type="submit">
                            Atualizar quantidades
                        </button>
                    </form>

                    <?php foreach ($cart['items'] as $line): ?>
                        <form id="remove-<?= e($line['product']['id']) ?>" method="post" action="/carrinho/remover">
                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="product_id" value="<?= e($line['product']['id']) ?>">
                        </form>
                    <?php endforeach; ?>
                </div>

                <aside class="order-summary" aria-label="Resumo dos valores do pedido">
                    <p class="overline">Resumo</p>
                    <h2>Seu pedido</h2>

                    <dl>
                        <div>
                            <dt>Subtotal dos produtos</dt>
                            <dd><?= money($cart['subtotal_cents']) ?></dd>
                        </div>
                        <div>
                            <dt>Entrega em Sorocaba</dt>
                            <dd><?= $cart['shipping_cents'] === 0 ? 'Grátis' : money($cart['shipping_cents']) ?></dd>
                        </div>
                    </dl>

                    <?php if ($cart['shipping_cents'] > 0): ?>
                        <progress class="shipping-progress"
                                  max="15000"
                                  value="<?= $cart['subtotal_cents'] ?>"
                                  aria-label="Progresso para frete grátis">
                            <?= min(100, (int) round($cart['subtotal_cents'] / 15000 * 100)) ?>%
                        </progress>
                        <small>Faltam apenas <?= money(15000 - $cart['subtotal_cents']) ?> para ganhar frete grátis!</small>
                    <?php endif; ?>

                    <div class="summary-total">
                        <span>Total</span>
                        <strong><?= money($cart['total_cents']) ?></strong>
                    </div>

                    <a class="button button-primary button-block" href="/checkout">
                        Finalizar pedido <span aria-hidden="true">→</span>
                    </a>

                    <p class="summary-secure" aria-label="Segurança da compra">
                        ⌁ Compra 100% segura • Dados criptografados
                    </p>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>
