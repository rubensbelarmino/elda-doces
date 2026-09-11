<?php
/**
 * ============================================================================
 * SACOLA DE COMPRAS (CARRINHO / CART) — ELDA BOLOS E DOCES
 * ============================================================================
 * Página crítica do funil de conversão (Fundo de Funil / BOFU).
 *
 * TÉCNICAS DE UX, CRO E SEO TÉCNICO:
 * ----------------------------------------------------------------------------
 * 1. OTIMIZAÇÃO DE CONVERSÃO (CRO):
 *    - Barra de progresso interativa para incentivar o ticket médio:
 *      Indica visualmente quanto falta para o cliente atingir o frete grátis (R$ 150,00).
 *    - Selos de confiança e compra segura destacados para reduzir abandono de carrinho.
 *
 * 2. SEGURANÇA E ACESSIBILIDADE (A11Y):
 *    - Formulários isolados com tokens CSRF individuais para atualizar e remover itens.
 *    - Rótulos e botões com textos claros para leitores de tela e navegação por teclado.
 *
 * 3. DIRETIVAS DE SEO:
 *    - Esta rota é privada e bloqueada para indexação no robots.txt (Disallow: /carrinho),
 *      evitando que robôs de busca gastem crawl budget com páginas de estado transiente.
 * ============================================================================
 */
?>

<!-- ====================================================================== -->
<!-- 1. CABEÇALHO DA SACOLA                                                 -->
<!-- ====================================================================== -->
<section class="page-hero mini" aria-label="Cabeçalho da sacola de compras">
    <div class="container">
        <p class="overline">Seu momento doce</p>
        <h1>Sua <em>sacola</em></h1>
    </div>
</section>

<!-- ====================================================================== -->
<!-- 2. CONTEÚDO PRINCIPAL DO CARRINHO                                      -->
<!-- ====================================================================== -->
<section class="section cart-page" aria-label="Itens selecionados na sacola">
    <div class="container">
        <?php if ($cart['items'] === []): ?>
            <!-- Estado Vazio Amigável incentivando o retorno ao cardápio -->
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
                <!-- Coluna da Esquerda: Lista de Itens do Carrinho -->
                <div>
                    <form method="post" action="/carrinho/atualizar">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">

                        <div class="cart-heading">
                            <h2><?= $cart['count'] ?> <?= $cart['count'] === 1 ? 'item adicionado' : 'itens adicionados' ?></h2>
                            <span>Subtotal</span>
                        </div>

                        <!-- Lista dos Itens adicionados -->
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

                    <!-- Formulários independentes para remoção segura de itens via POST -->
                    <?php foreach ($cart['items'] as $line): ?>
                        <form id="remove-<?= e($line['product']['id']) ?>" method="post" action="/carrinho/remover">
                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="product_id" value="<?= e($line['product']['id']) ?>">
                        </form>
                    <?php endforeach; ?>
                </div>

                <!-- Coluna da Direita: Resumo do Pedido, Frete e CTA de Checkout -->
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

                    <!-- Gatilho de ticket médio: Progresso para Frete Grátis -->
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
