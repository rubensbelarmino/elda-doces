<?php
/**
 * ============================================================================
 * OPERAÇÃO DE PEDIDOS & ENTREGAS (ADMIN ORDERS) — ELDA BOLOS E DOCES
 * ============================================================================
 * Detalhamento completo de cada pedido para despacho e logística da confeitaria:
 * nome do cliente, dados de contato, endereço físico de entrega em Sorocaba,
 * itens selecionados, ID da transação no Mercado Pago e alteração de status.
 *
 * TÉCNICAS DE SEGURANÇA E ACESSO RESTRITO:
 * ----------------------------------------------------------------------------
 * 1. PRIVACIDADE E CONFORMIDADE (LGPD):
 *    - Contém dados sensíveis de clientes (endereço, telefone, e-mail).
 *    - Requer privilégios de administrador (require_admin()).
 *    - Protegida contra visualização por motores de busca (robots.txt: Disallow: /admin).
 * ============================================================================
 */

$statusLabels = [
    'received' => 'Recebido',
    'preparing' => 'Em preparo',
    'shipping' => 'A caminho',
    'delivered' => 'Entregue',
    'cancelled' => 'Cancelado'
];

$paymentLabels = [
    'pending' => 'Aguardando PIX',
    'approved' => 'Pago com sucesso',
    'rejected' => 'Recusado',
    'cancelled' => 'Cancelado',
    'refunded' => 'Estornado'
];

$admin = current_user();
?>
<section class="admin-shell" aria-label="Gerenciamento operacional de pedidos">
    <!-- Barra Lateral da Administração -->
    <aside class="admin-sidebar"> 
<!--        <a class="brand brand-light" href="/" aria-label="Voltar para a vitrine pública"> -->
<!--            <span class="brand-mark" aria-hidden="true">E</span> -->
<!--            <span><strong>Elda</strong><small>BOLOS &amp; DOCES</small></span> -->
<!--        </a> -->
        <nav aria-label="Menu administrativo">
            <a href="/admin"><span>⌂</span> Visão geral</a>
            <a class="active" href="/admin/pedidos"><span>◇</span> Pedidos</a>
            <a href="/admin#produtos"><span>□</span> Produtos</a>
            <a href="/"><span>↗</span> Ver loja pública</a>
        </nav>
        <div class="admin-user">
            <span aria-hidden="true"><?= e(strtoupper(substr((string) ($admin['name'] ?? 'A'), 0, 1))) ?></span>
            <div>
                <strong><?= e($admin['name'] ?? 'Administrador') ?></strong>
                <small>Administrador da loja</small>
            </div>
        </div>
    </aside>

    <!-- Lista Operacional de Pedidos -->
    <div class="admin-content admin-orders">
        <header>
            <div>
                <p class="overline">Logística e Despacho</p>
                <h1>Pedidos realizados</h1>
                <p>Cliente, itens encomendados, endereço de entrega e status do pagamento em um só lugar.</p>
            </div>
            <a class="button button-outline" href="/admin">← Voltar à visão geral</a>
        </header>

        <?php if ($orders === []): ?>
            <div class="admin-panel admin-empty">Nenhum pedido realizado até o momento.</div>
        <?php else: ?>
            <div class="admin-order-list">
                <?php foreach ($orders as $order): 
                    $customer = $order['customer'] ?? [];
                    $address = implode(', ', array_filter([
                        (string) ($customer['address'] ?? ''),
                        (string) ($customer['number'] ?? ''),
                        (string) ($customer['complement'] ?? '')
                    ]));
                ?>
                <article class="admin-order-card" aria-label="Detalhe do pedido #<?= e($order['number']) ?>">
                    <header>
                        <div>
                            <p class="overline">Pedido</p>
                            <h2>#<?= e($order['number']) ?></h2>
                            <small><?= date('d/m/Y \à\s H:i', strtotime((string) $order['created_at'])) ?></small>
                        </div>
                        <div class="admin-order-total">
                            <span>Total</span>
                            <strong><?= money((int) $order['total_cents']) ?></strong>
                        </div>
                    </header>

                    <div class="admin-order-body">
                        <!-- Dados do Destinatário -->
                        <section>
                            <h3>Cliente</h3>
                            <strong><?= e($customer['name'] ?? 'Não informado') ?></strong>
                            <a href="mailto:<?= e($customer['email'] ?? '') ?>"><?= e($customer['email'] ?? 'E-mail não informado') ?></a>
                            <span><?= e($customer['phone'] ?? 'Telefone não informado') ?></span>
                        </section>

                        <!-- Local de Entrega em Sorocaba -->
                        <section>
                            <h3>Entrega</h3>
                            <strong><?= e($address !== '' ? $address : 'Endereço não informado') ?></strong>
                            <span><?= e($customer['city'] ?? 'Sorocaba / SP') ?></span>
                            <span>CEP: <?= e($customer['zip'] ?? '—') ?></span>
                        </section>

                        <!-- Itens Selecionados -->
                        <section class="admin-order-items">
                            <h3>Itens do pedido</h3>
                            <?php foreach ($order['items'] as $item): ?>
                                <div>
                                    <span><?= (int) $item['quantity'] ?>× <?= e($item['name']) ?></span>
                                    <strong><?= money((int) $item['unit_cents'] * (int) $item['quantity']) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </section>
                    </div>

                    <!-- Rodapé do Card com Status de Pagamento e Seletor de Entrega -->
                    <footer>
                        <div>
                            <span>Situação do pagamento</span>
                            <strong class="status payment-<?= e($order['payment_status'] ?? 'pending') ?>">
                                <?= e($paymentLabels[$order['payment_status'] ?? 'pending'] ?? 'Pendente') ?>
                            </strong>
                            <?php if (!empty($order['payment_id'])): ?>
                                <small>ID Mercado Pago: <?= e($order['payment_id']) ?></small>
                            <?php endif; ?>
                        </div>

                        <form class="status-form" method="post" action="/admin/pedidos/<?= e($order['id']) ?>/status">
                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                            <label>
                                Status da entrega
                                <select name="status" data-auto-submit aria-label="Atualizar status da entrega">
                                    <?php foreach ($statusLabels as $value => $label): ?>
                                        <option value="<?= e($value) ?>" <?= $order['status'] === $value ? 'selected' : '' ?>>
                                            <?= e($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </form>
                    </footer>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
