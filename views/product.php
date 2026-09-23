<?php
//
// $productUrl = request_base_url() . '/produto/' . rawurlencode((string) $product['slug']);
// $productImageUrl = request_base_url() . '/assets/images/' . rawurlencode((string) $product['image']);
// $inStock = (int) $product['stock'] > 0;
// ?>
//
// <script type="application/ld+json">
// {
//   "@context": "https://schema.org",
//   "@graph": [
//     {
//       "@type": "Product",
//       "@id": "<?= e($productUrl) ?>/#product",
//       "url": "<?= e($productUrl) ?>",
//       "name": "<?= e($product['name']) ?>",
//       "description": "<?= e($product['description']) ?>",
//       "image": "<?= e($productImageUrl) ?>",
//       "category": "<?= e($product['category']) ?>",
//       "brand": {
//         "@type": "Brand",
//         "name": "Elda Bolos e Doces"
//       },
//       "offers": {
//         "@type": "Offer",
//         "url": "<?= e($productUrl) ?>",
//         "priceCurrency": "BRL",
//         "price": "<?= number_format((int) $product['price_cents'] / 100, 2, '.', '') ?>",
//         "itemCondition": "https://schema.org/NewCondition",
//         "availability": "<?= $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' ?>",
//         "seller": {
//           "@type": "Organization",
//           "name": "Elda Bolos e Doces"
//         }
//       }
//     },
//     {
//       "@type": "BreadcrumbList",
//       "@id": "<?= e($productUrl) ?>/#breadcrumbs",
//       "itemListElement": [
//         {
//           "@type": "ListItem",
//           "position": 1,
//           "name": "Início",
//           "item": "<?= e(request_base_url()) ?>/"
//         },
//         {
//           "@type": "ListItem",
//           "position": 2,
//           "name": "Cardápio",
//           "item": "<?= e(request_base_url()) ?>/cardapio"
//         },
//         {
//           "@type": "ListItem",
//           "position": 3,
//           "name": "<?= e($product['name']) ?>",
//           "item": "<?= e($productUrl) ?>"
//         }
//       ]
//     }
//   ]
// }
// </script>
//
// <section class="section product-detail" aria-label="Detalhes do produto <?= e($product['name']) ?>">
//     <div class="container">
//
//         <nav class="breadcrumbs" aria-label="Navegação estrutural">
//             <a href="/">Início</a>
//             <span aria-hidden="true">/</span>
//             <a href="/cardapio">Cardápio</a>
//             <span aria-hidden="true">/</span>
//             <span aria-current="page"><?= e($product['name']) ?></span>
//         </nav>
//
//         <div class="product-detail-grid">
//
//             <div class="detail-image">
//                 <img src="<?= versioned_asset('images/' . $product['image']) ?>"
//                      data-base64-image="<?= e($product['image']) ?>"
//                      alt="<?= e($product['name']) ?> — <?= e($product['category']) ?> da Elda Bolos e Doces em Sorocaba"
//                      loading="eager"
//                      fetchpriority="high"
//                      width="900"
//                      height="700">
//                 <span class="product-badge"><?= e($product['category']) ?></span>
//             </div>
//
//             <div class="detail-copy">
//
//                 <p class="overline"><?= e($product['category']) ?> • <?= e($product['portion']) ?></p>
//
//                 <h1><?= e($product['name']) ?></h1>
//
//                 <p class="detail-description"><?= e($product['description']) ?></p>
//
//                 <div class="detail-price">
//                     <strong><?= money((int) $product['price_cents']) ?></strong>
//                     <?php if (!empty($product['compare_cents'])): ?>
//                         <del aria-label="Preço original"><?= money((int) $product['compare_cents']) ?></del>
//                     <?php endif; ?>
//                 </div>
//
//                 <div class="detail-notes">
//                     <div>
//                         <span aria-hidden="true">✦</span>
//                         <p><strong>Feito hoje</strong><br>Produção artesanal em pequenos lotes</p>
//                     </div>
//                     <div>
//                         <span aria-hidden="true">♧</span>
//                         <p><strong>Ingredientes nobres</strong><br>Selecionados com carinho um a um</p>
//                     </div>
//                 </div>
//
//                 <form class="detail-form" method="post" action="/carrinho/adicionar">
//                     <input type="hidden" name="_token" value="<?= csrf_token() ?>">
//                     <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
//                     <input type="hidden" name="redirect_to" value="/carrinho">
//
//                     <label>
//                         Quantidade
//                         <input type="number"
//                                name="quantity"
//                                min="1"
//                                max="<?= max(1, (int) $product['stock']) ?>"
//                                value="1"
//                                <?= !$inStock ? 'disabled' : '' ?>>
//                     </label>
//
//                     <button class="button button-primary" type="submit" <?= !$inStock ? 'disabled' : '' ?>>
//                         <?= $inStock ? 'Adicionar à sacola' : 'Esgotado' ?>
//                         <span aria-hidden="true">→</span>
//                     </button>
//                 </form>
//
//                 <p class="stock-note">
//                     <?= $inStock
//                         ? '● Em estoque — ' . (int) $product['stock'] . ' unidades disponíveis'
//                         : '○ Esgotado no momento — fale conosco para encomendas' ?>
//                 </p>
//             </div>
//         </div>
//     </div>
// </section>
//
// <?php if ($related): ?>
// <section class="section related" aria-label="Doces relacionados que você também pode gostar">
//     <div class="container">
//         <div class="section-heading">
//             <div>
//                 <p class="overline">Você também pode amar</p>
//                 <h2>Mais <em>delicadezas da mesma categoria</em></h2>
//             </div>
//         </div>
//         <div class="product-grid">
//             <?php foreach (array_slice($related, 0, 4) as $product): ?>
//                 <?php require __DIR__ . '/partials/product-card.php'; ?>
//             <?php endforeach; ?>
//         </div>
//     </div>
// </section>
// <?php endif; ?>
