<?php
//
// $statusLabels = [
//     'received' => 'Recebido pela cozinha',
//     'preparing' => 'Em preparo artesanal',
//     'shipping' => 'A caminho (saiu para entrega)',
//     'delivered' => 'Entregue com carinho',
//     'cancelled' => 'Cancelado'
// ];
//
// $paymentLabels = [
//     'pending' => 'Aguardando PIX',
//     'approved' => 'PIX Aprovado ✓',
//     'rejected' => 'Recusado',
//     'cancelled' => 'Cancelado',
//     'refunded' => 'Estornado'
// ];
// ?>
//
// <section class="account-header" aria-label="Cabeçalho da conta do cliente">
//     <div class="container">
//         <div>
//             <p class="overline">Área exclusiva do cliente</p>
//             <h1>Olá, <?= e(explode(' ', $user['name'])[0]) ?>.</h1>
//             <p>Seus pedidos e momentos especiais, todos reunidos aqui.</p>
//         </div>
//
//         <form method="post" action="/sair">
//             <input type="hidden" name="_token" value="<?= csrf_token() ?>">
//             <button class="button button-outline" type="submit">
//                 Sair da conta
//             </button>
//         </form>
//     </div>
// </section>
//
// <section class="section account-page" aria-label="Histórico de pedidos">
//     <div class="container">
//         <div class="account-grid">
//
//             <aside class="account-nav" aria-label="Navegação da conta">
//                 <a class="active" href="/minha-conta">
//                     Meus pedidos <span><?= count($orders) ?></span>
//                 </a>
//                 <a href="/cardapio">
//                     Explorar cardápio <span aria-hidden="true">→</span>
//                 </a>
//                 <?php if ($user['role'] === 'admin'): ?>
//                     <a href="/admin">
//                         Painel Administrativo <span aria-hidden="true">→</span>
//                     </a>
//                 <?php endif; ?>
//             </aside>
//
//             <div>
//                 <div class="section-heading small">
//                     <div>
//                         <p class="overline">Histórico</p>
//                         <h2>Meus <em>pedidos</em></h2>
//                     </div>
//                 </div>
//
//                 <?php if (!$orders): ?>
//
//                     <div class="empty-state compact">
//                         <span aria-hidden="true">◇</span>
//                         <h2>Seu primeiro pedido começa aqui</h2>
//                         <p>Ainda não encontramos pedidos anteriores nesta conta. Escolha algo especial no cardápio.</p>
//                         <a class="button button-primary" href="/cardapio">
//                             Escolher meus doces
//                         </a>
//                     </div>
//                 <?php else: ?>
//                     <div class="order-list">
//                         <?php foreach ($orders as $order): ?>
//                             <article class="order-card" aria-label="Pedido número <?= e($order['number']) ?>">
//                                 <header>
//                                     <div>
//                                         <span>Pedido <strong>#<?= e($order['number']) ?></strong></span>
//                                         <small><?= date('d/m/Y \à\s H:i', strtotime($order['created_at'])) ?></small>
//                                     </div>
//                                     <strong class="status status-<?= e($order['status']) ?>">
//                                         <?= e($statusLabels[$order['status']] ?? $order['status']) ?>
//                                     </strong>
//                                 </header>
//
//                                 <div class="order-items">
//                                     <?php foreach ($order['items'] as $item): ?>
//                                         <span><?= (int) $item['quantity'] ?>× <?= e($item['name']) ?></span>
//                                     <?php endforeach; ?>
//                                 </div>
//
//                                 <footer>
//                                     <span>
//                                         <b class="status payment-<?= e($order['payment_status'] ?? 'pending') ?>">
//                                             <?= e($paymentLabels[$order['payment_status'] ?? 'pending'] ?? 'Pendente') ?>
//                                         </b>
//                                         <?php if (($order['payment_status'] ?? 'pending') === 'pending' && !empty($order['pix_code'])): ?>
//                                             <a class="text-link" href="/pedido/sucesso/<?= e($order['number']) ?>">
//                                                 Abrir PIX →
//                                             </a>
//                                         <?php endif; ?>
//                                     </span>
//                                     <strong><?= money((int) $order['total_cents']) ?></strong>
//                                 </footer>
//                             </article>
//                         <?php endforeach; ?>
//                     </div>
//                 <?php endif; ?>
//             </div>
//         </div>
//     </div>
// </section>
