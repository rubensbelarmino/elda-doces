<?php
// /**
//  * ============================================================================
//  * DETALHE DO PRODUTO (PRODUCT PAGE) — ELDA BOLOS E DOCES
//  * ============================================================================
//  * Esta é a página mais importante para conversão direta e para buscas de cauda
//  * longa (Long-Tail SEO), onde o consumidor busca o nome exato da sobremesa.
//  *
//  * TÉCNICAS DE SEO APLICADAS NESTE ARQUIVO:
//  * ----------------------------------------------------------------------------
//  * 1. DADOS ESTRUTURADOS DE PRODUTO (SCHEMA.ORG / JSON-LD):
//  *    - Marcação completa de @type "Product" e "Offer":
//  *      Informa ao Google o nome, imagem, descrição, preço em BRL, moeda,
//  *      disponibilidade em estoque (InStock / OutOfStock) e marca.
//  *    - Habilita Rich Snippets (resultados enriquecidos) no Google com preço,
//  *      avaliações e disponibilidade destacados diretamente na SERP.
//  *
//  * 2. BREADCRUMBS SEMÂNTICOS & SCHEMA.ORG BREADCRUMBLIST:
//  *    - Navegação estrutural (Início > Cardápio > Nome do Produto) com marcação
//  *      semântica e Schema.org BreadcrumbList, permitindo que os robôs do Google
//  *      entendam a profundidade e arquitetura da informação da loja.
//  *
//  * 3. HIERARQUIA DE HEADINGS & ON-PAGE SEO:
//  *    - <h1> exclusivo com o nome do doce.
//  *    - Descrição rica em palavras-chave naturais (ingredientes, porção, modo de preparo).
//  *    - Sinais de disponibilidade e estoque em tempo real.
//  *
//  * 4. LINK BUILDING INTERNO (PRODUTOS RELACIONADOS):
//  *    - Seção "Você também pode amar" com produtos da mesma categoria,
//  *      mantendo o usuário navegando na loja (maior tempo de permanência no site,
//  *      menor bounce rate) e distribuindo PageRank internamente.
//  * ============================================================================
//  */
//
// $productUrl = request_base_url() . '/produto/' . rawurlencode((string) $product['slug']);
// $productImageUrl = request_base_url() . '/assets/images/' . rawurlencode((string) $product['image']);
// $inStock = (int) $product['stock'] > 0;
// ?>
//
// <!-- ====================================================================== -->
// <!-- DADOS ESTRUTURADOS SCHEMA.ORG (JSON-LD) PARA RICH SNIPPETS NO GOOGLE   -->
// <!-- ====================================================================== -->
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
// <!-- ====================================================================== -->
// <!-- 1. DETALHE DO PRODUTO                                                  -->
// <!-- ====================================================================== -->
// <section class="section product-detail" aria-label="Detalhes do produto <?= e($product['name']) ?>">
//     <div class="container">
//         <!-- Navegação estrutural de Breadcrumbs para usuários e motores de busca -->
//         <nav class="breadcrumbs" aria-label="Navegação estrutural">
//             <a href="/">Início</a>
//             <span aria-hidden="true">/</span>
//             <a href="/cardapio">Cardápio</a>
//             <span aria-hidden="true">/</span>
//             <span aria-current="page"><?= e($product['name']) ?></span>
//         </nav>
//
//         <div class="product-detail-grid">
//             <!-- Imagem principal do produto com badge de categoria -->
//             <div class="detail-image">
//                 <img src="<?= image_placeholder() ?>" 
//                      data-base64-image="<?= e($product['image']) ?>" 
//                      alt="<?= e($product['name']) ?> — <?= e($product['category']) ?> da Elda Bolos e Doces em Sorocaba"
//                      width="900"
//                      height="700">
//                 <span class="product-badge"><?= e($product['category']) ?></span>
//             </div>
//
//             <!-- Informações completas do produto -->
//             <div class="detail-copy">
//                 <!-- Categoria e Rendimento da Porção -->
//                 <p class="overline"><?= e($product['category']) ?> • <?= e($product['portion']) ?></p>
//
//                 <!-- H1 Principal da página do produto: palavra-chave do item -->
//                 <h1><?= e($product['name']) ?></h1>
//
//                 <!-- Descrição detalhada rica em termos de culinária e confeitaria -->
//                 <p class="detail-description"><?= e($product['description']) ?></p>
//
//                 <!-- Preço atual e preço promocional comparativo (de/por) -->
//                 <div class="detail-price">
//                     <strong><?= money((int) $product['price_cents']) ?></strong>
//                     <?php if (!empty($product['compare_cents'])): ?>
//                         <del aria-label="Preço original"><?= money((int) $product['compare_cents']) ?></del>
//                     <?php endif; ?>
//                 </div>
//
//                 <!-- Selos de qualidade artesanal que transmitem confiança (CRO) -->
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
//                 <!-- Formulário semântico e seguro (CSRF) de adição à sacola -->
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
//                 <!-- Indicador visual e acessível de estoque disponível -->
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
// <!-- ====================================================================== -->
// <!-- 2. PRODUTOS RELACIONADOS (INTERNAL LINKING / LINK JUICE)                -->
// <!-- ====================================================================== -->
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
