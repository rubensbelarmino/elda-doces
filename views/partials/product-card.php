<?php
// <article class="product-card" aria-label="<?= e($product['name']) ?>">
//
//     <a class="product-image" href="/produto/<?= e($product['slug']) ?>">
//         <img src="<?= versioned_asset('images/' . $product['image']) ?>"
//              data-base64-image="<?= e($product['image']) ?>"
//              alt="<?= e($product['name']) ?> — Doceria artesanal Elda Bolos e Doces"
//              loading="<?= !empty($imagePriority) ? 'eager' : 'lazy' ?>"
//              <?= !empty($imagePriority) ? ' fetchpriority="high" data-image-priority="high"' : ' decoding="async"' ?>
//              width="900"
//              height="700">
//
//         <?php if (!empty($product['compare_cents'])): ?>
//             <span class="product-badge">Oferta especial</span>
//         <?php elseif ((bool) $product['featured']): ?>
//             <span class="product-badge">Mais pedido</span>
//         <?php endif; ?>
//     </a>
//
//     <div class="product-info">
//         <div>
//
//             <span class="eyebrow"><?= e($product['category']) ?></span>
//
//             <a href="/produto/<?= e($product['slug']) ?>">
//                 <h3><?= e($product['name']) ?></h3>
//             </a>
//         </div>
//
//         <div class="product-price">
//             <strong><?= money((int) $product['price_cents']) ?></strong>
//             <?php if (!empty($product['compare_cents'])): ?>
//                 <del aria-label="Preço anterior"><?= money((int) $product['compare_cents']) ?></del>
//             <?php endif; ?>
//         </div>
//
//         <form method="post" action="/carrinho/adicionar">
//             <input type="hidden" name="_token" value="<?= csrf_token() ?>">
//             <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
//             <input type="hidden" name="quantity" value="1">
//             <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? '/cardapio') ?>">
//
//             <button class="add-button"
//                     type="submit"
//                     <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>
//                     aria-label="Adicionar <?= e($product['name']) ?> à sacola">
//                 <span><?= (int) $product['stock'] < 1 ? 'Esgotado' : 'Adicionar' ?></span>
//                 <span aria-hidden="true">＋</span>
//             </button>
//         </form>
//     </div>
// </article>
