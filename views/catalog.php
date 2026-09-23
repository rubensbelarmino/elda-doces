<?php
// <section class="page-hero compact" aria-label="Apresentação do cardápio">
//     <div class="container">
//         <p class="overline">Feito artesanalmente para você</p>
//
//         <h1>Nosso <em>cardápio</em></h1>
//         <p>Escolha seu novo doce artesanal favorito em Sorocaba. Nós cuidamos do resto.</p>
//     </div>
// </section>
//
// <section class="section catalog-section" aria-label="Catálogo de produtos">
//     <div class="container">
//
//         <form class="catalog-toolbar" method="get" action="/cardapio" role="search" aria-label="Filtrar cardápio">
//
//             <div class="category-pills" role="navigation" aria-label="Filtrar por categoria">
//                 <a class="<?= $category === '' ? 'active' : '' ?>" href="/cardapio">
//                     Todos os doces
//                 </a>
//                 <?php foreach ($categories as $item): ?>
//                     <a class="<?= $category === $item ? 'active' : '' ?>"
//                        href="/cardapio?categoria=<?= urlencode($item) ?>">
//                         <?= e($item) ?>
//                     </a>
//                 <?php endforeach; ?>
//             </div>
//
//             <label class="search-field">
//                 <span class="sr-only">Buscar doce por nome ou ingrediente</span>
//                 <svg viewBox="0 0 24 24" aria-hidden="true">
//                     <circle cx="11" cy="11" r="7"/>
//                     <path d="m20 20-4-4"/>
//                 </svg>
//                 <input name="busca"
//                        type="search"
//                        value="<?= e($search) ?>"
//                        placeholder="Buscar por morango, chocolate, bolo...">
//             </label>
//         </form>
//
//         <?php if ($products): ?>
//             <div class="product-grid catalog-grid">
//                 <?php
//                 $catalogIndex = 0;
//                 foreach ($products as $product) {
//
//                     $imagePriority = $catalogIndex < 6;
//                     require __DIR__ . '/partials/product-card.php';
//                     $catalogIndex++;
//                 }
//                 ?>
//             </div>
//         <?php else: ?>
//
//             <div class="empty-state">
//                 <span aria-hidden="true">◇</span>
//                 <h2>Nenhum doce encontrado</h2>
//                 <p>Tente buscar por outro termo ou explore todas as delícias do nosso cardápio completo.</p>
//                 <a class="button button-primary" href="/cardapio">
//                     Ver todos os doces
//                 </a>
//             </div>
//         <?php endif; ?>
//     </div>
// </section>
