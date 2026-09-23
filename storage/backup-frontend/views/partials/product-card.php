<?php
// /**
//  * ============================================================================
//  * COMPONENTE: CARD DE PRODUTO (PARTIAL) — ELDA BOLOS E DOCES
//  * ============================================================================
//  * Componente reutilizado na vitrine da Home, no Cardápio e em Produtos Relacionados.
//  *
//  * TÉCNICAS DE SEO & PERFORMANCE APLICADAS NESTE ARQUIVO:
//  * ----------------------------------------------------------------------------
//  * 1. ESTRUTURA SEMÂNTICA HTML5:
//  *    - <article class="product-card">: Define cada doce como um item independente
//  *      e autocontido, facilitando o entendimento da estrutura pelos indexadores.
//  *    - Heading <h3>: Mantém a hierarquia correta abaixo das seções (h1 -> h2 -> h3).
//  *
//  * 2. PERFORMANCE & CORE WEB VITALS:
//  *    - Dimensões explícitas (width="900" height="700"): Evita CLS (Cumulative Layout Shift)
//  *      ao reservar o espaço exato antes do download da imagem.
//  *    - loading="lazy" vs loading="eager" dinâmico com fetchpriority="high":
//  *      Controlado via variável $imagePriority para garantir que os primeiros itens
//  *      carreguem instantaneamente sem prejudicar o LCP (Largest Contentful Paint).
//  *
//  * 3. ACESSIBILIDADE (A11Y) & SEO DE IMAGENS:
//  *    - Alt descritivo e contextual contendo o nome exato do produto.
//  *    - Links internos amigáveis com slugs claros (/produto/nome-do-produto).
//  * ============================================================================
//  */
// ?>
// <article class="product-card" aria-label="<?= e($product['name']) ?>">
//     <!-- Link da imagem com URL canônica e atributos de performance -->
//     <a class="product-image" href="/produto/<?= e($product['slug']) ?>">
//         <img src="<?= image_placeholder() ?>" 
//              data-base64-image="<?= e($product['image']) ?>" 
//              alt="<?= e($product['name']) ?> — Doceria artesanal Elda Bolos e Doces" 
//              loading="<?= !empty($imagePriority) ? 'eager' : 'lazy' ?>"
//              <?= !empty($imagePriority) ? ' fetchpriority="high" data-image-priority="high"' : '' ?> 
//              width="900" 
//              height="700">
//
//         <!-- Badges visuais de destaque comercial -->
//         <?php if (!empty($product['compare_cents'])): ?>
//             <span class="product-badge">Oferta especial</span>
//         <?php elseif ((bool) $product['featured']): ?>
//             <span class="product-badge">Mais pedido</span>
//         <?php endif; ?>
//     </a>
//
//     <div class="product-info">
//         <div>
//             <!-- Categoria como rótulo contextual -->
//             <span class="eyebrow"><?= e($product['category']) ?></span>
//             <!-- H3 com link interno de alta relevância -->
//             <a href="/produto/<?= e($product['slug']) ?>">
//                 <h3><?= e($product['name']) ?></h3>
//             </a>
//         </div>
//
//         <!-- Preço atual e promocional com semântica <del> -->
//         <div class="product-price">
//             <strong><?= money((int) $product['price_cents']) ?></strong>
//             <?php if (!empty($product['compare_cents'])): ?>
//                 <del aria-label="Preço anterior"><?= money((int) $product['compare_cents']) ?></del>
//             <?php endif; ?>
//         </div>
//
//         <!-- Formulário de adição rápida protegido contra CSRF -->
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
