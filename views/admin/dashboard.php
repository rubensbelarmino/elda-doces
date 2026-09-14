<?php
/**
 * ============================================================================
 * PAINEL ADMINISTRATIVO (DASHBOARD) — ELDA BOLOS E DOCES
 * ============================================================================
 * Visão executiva para gestão da doceria, métricas de vendas em tempo real,
 * pedidos recentes e controle rápido do catálogo de produtos.
 *
 * TÉCNICAS DE SEGURANÇA E ENGENHARIA:
 * ----------------------------------------------------------------------------
 * 1. CONTROLE DE ACESSO BASEADO EM FUNÇÃO (RBAC):
 *    - Rota estritamente protegida por require_admin(), rejeitando com 403 Forbidden
 *      qualquer requisição sem credencial com perfil 'admin'.
 *    - Bloqueada de indexação de busca por diretiva Disallow: /admin no robots.txt.
 *
 * 2. ATUALIZAÇÃO REATIVA DE STATUS:
 *    - Seletor de status com submissão automática (data-auto-submit) e token CSRF,
 *      otimizando o fluxo de trabalho da equipe da cozinha.
 * ============================================================================
 */

$statusLabels = [
    'received' => 'Recebido',
    'preparing' => 'Em preparo',
    'shipping' => 'A caminho',
    'delivered' => 'Entregue',
    'cancelled' => 'Cancelado'
];
?>

<section class="admin-shell" aria-label="Painel de controle administrativo">
    <!-- Barra Lateral de Navegação do Administrador -->
    <aside class="admin-sidebar"> 
<!--          <a class="brand brand-light" href="/" aria-label="Voltar para a vitrine da loja">  -->
<!--             <span class="brand-mark" aria-hidden="true">E</span> -->
<!--             <span><strong>Elda</strong><small>BOLOS &amp; DOCES</small></span>  -->
<!--         </a>  -->
        <nav aria-label="Navegação administrativa">
            <a class="active" href="/admin"><span>⌂</span> Visão geral</a>
            <a href="/admin/pedidos"><span>◇</span> Pedidos</a>
            <a href="#produtos"><span>□</span> Produtos</a>
            <a href="/"><span>↗</span> Ver loja pública</a>
        </nav>
        <div class="admin-user">
            <span aria-hidden="true">A</span>
            <div>
                <strong>Administrador</strong>
                <small>Elda Bolos e Doces</small>
            </div>
        </div>
    </aside>

    <!-- Conteúdo Principal do Painel -->
    <div class="admin-content">
        <header>
            <div>
                <p class="overline">Visão geral do negócio</p>
                <h1>Bom trabalho hoje. <em>✦</em></h1>
                <p>Acompanhe o pulso da sua confeitaria artesanal em tempo real.</p>
            </div>
            <a class="button button-primary" href="/admin/produtos/novo">
                ＋ Novo produto
            </a>
        </header>

        <!-- Cartões de Métricas Financeiras e Operacionais -->
        <div class="metric-grid">
            <article>
                <span>Receita total</span>
                <strong><?= money($metrics['revenue_cents']) ?></strong>
                <small>Pedidos não cancelados</small>
                <i aria-hidden="true">↗</i>
            </article>
            <article>
                <span>Pedidos</span>
                <strong><?= $metrics['orders'] ?></strong>
                <small>Desde o início</small>
                <i aria-hidden="true">◇</i>
            </article>
            <article>
                <span>Clientes</span>
                <strong><?= $metrics['customers'] ?></strong>
                <small>Contas cadastradas</small>
                <i aria-hidden="true">♡</i>
            </article>
            <article>
                <span>Produtos ativos</span>
                <strong><?= $metrics['products'] ?></strong>
                <small>Na vitrine agora</small>
                <i aria-hidden="true">□</i>
            </article>
        </div>

        <!-- Tabela de Pedidos Recentes -->
        <section class="admin-panel" id="pedidos" aria-label="Pedidos recentes da doceria">
            <div class="admin-panel-heading">
                <div>
                    <p class="overline">Operação da cozinha</p>
                    <h2>Pedidos recentes</h2>
                </div>
                <a class="text-link" href="/admin/pedidos">Ver todos e endereços de entrega →</a>
            </div>

            <?php if (!$orders): ?>
                <div class="admin-empty">Nenhum pedido recebido ainda. Use uma conta de cliente para testar uma compra.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Cliente</th>
                                <th>Pagamento</th>
                                <th>Data</th>
                                <th>Total</th>
                                <th>Status da Cozinha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= e($order['number']) ?></strong>
                                        <small><?= count($order['items']) ?> itens</small>
                                    </td>
                                    <td><?= e($order['customer']['name'] ?? 'Cliente') ?></td>
                                    <td>
                                        <span class="status payment-<?= e($order['payment_status'] ?? 'pending') ?>">
                                            <?= e(($order['payment_status'] ?? 'pending') === 'approved' ? 'Pago' : 'Pendente') ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td><strong><?= money((int) $order['total_cents']) ?></strong></td>
                                    <td>
                                        <form class="status-form" method="post" action="/admin/pedidos/<?= e($order['id']) ?>/status">
                                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                            <select name="status" aria-label="Status do pedido #<?= e($order['number']) ?>" data-auto-submit>
                                                <?php foreach ($statusLabels as $value => $label): ?>
                                                    <option value="<?= e($value) ?>" <?= $order['status'] === $value ? 'selected' : '' ?>>
                                                        <?= e($label) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- Tabela de Gestão de Produtos do Catálogo -->
        <section class="admin-panel" id="produtos" aria-label="Gestão do catálogo de produtos">
            <div class="admin-panel-heading">
                <div>
                    <p class="overline">Gestão do cardápio</p>
                    <h2>Produtos na vitrine</h2>
                </div>
                <a class="text-link" href="/admin/produtos/novo">Adicionar doce artesanal →</a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Estoque</th>
                            <th>Preço</th>
                            <th>Situação</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="product-cell">
                                    <img src="<?= image_placeholder() ?>" 
                                         data-base64-image="<?= e($product['image']) ?>" 
                                         alt="<?= e($product['name']) ?>"
                                         width="48"
                                         height="40">
                                    <div>
                                        <strong><?= e($product['name']) ?></strong>
                                        <small><?= e($product['portion']) ?></small>
                                    </div>
                                </td>
                                <td><?= e($product['category']) ?></td>
                                <td><?= (int) $product['stock'] ?> un.</td>
                                <td><strong><?= money((int) $product['price_cents']) ?></strong></td>
                                <td>
                                    <span class="status <?= $product['active'] ? 'status-delivered' : 'status-cancelled' ?>">
                                        <?= $product['active'] ? 'Visível' : 'Oculto' ?>
                                    </span>
                                </td>
                                <td>
                                    <a class="table-action" href="/admin/produtos/<?= e($product['id']) ?>/editar">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</section>
