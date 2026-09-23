<?php
// /**
//  * ============================================================================
//  * CONFIRMAÇÃO DO PEDIDO & QR CODE PIX (THANK YOU PAGE) — ELDA BOLOS E DOCES
//  * ============================================================================
//  * Página exibida imediatamente após o checkout, contendo o QR Code dinâmico
//  * do PIX emitido via Mercado Pago e o botão de copiar código.
//  *
//  * TÉCNICAS DE UX, SEGURANÇA E CONVERSÃO:
//  * ----------------------------------------------------------------------------
//  * 1. CLAREZA VISUAL & COPIAR COM UM CLIQUE:
//  *    - QR Code gerado em alta resolução para escaneamento na tela do celular.
//  *    - Botão "Copiar código" com suporte à moderna Clipboard API do navegador
//  *      e fallback automático com execCommand para navegadores legados.
//  *
//  * 2. SEGURANÇA E NÃO-INDEXAÇÃO:
//  *    - Esta rota contém parâmetros sensíveis e de uso exclusivo do comprador.
//  *    - Bloqueada de indexação no robots.txt (Disallow: /pedido/).
//  * ============================================================================
//  */
//
// $paymentStatus = (string) ($order['payment_status'] ?? 'pending');
// $paymentLabels = [
//     'pending' => 'Aguardando pagamento via PIX',
//     'approved' => 'Pagamento aprovado com sucesso!',
//     'rejected' => 'Pagamento recusado',
//     'cancelled' => 'Pagamento cancelado',
//     'refunded' => 'Pagamento estornado'
// ];
// ?>
//
// <section class="success-page" aria-label="Confirmação e pagamento do pedido">
//     <div class="success-card pix-success" data-order-number="<?= e($order['number']) ?>" data-status="<?= e($paymentStatus) ?>">
//         <!-- Ícone dinâmico baseado no status do pagamento -->
//         <div class="success-mark" aria-hidden="true">
//             <?= $paymentStatus === 'approved' ? '✓' : '◇' ?>
//         </div>
//
//         <p class="overline"><?= e($paymentLabels[$paymentStatus] ?? 'Pagamento pendente') ?></p>
//
//         <h1>
//             <?= $paymentStatus === 'approved' 
//                 ? 'Pagamento confirmado!' 
//                 : 'Seu PIX está pronto para pagamento.' ?>
//         </h1>
//
//         <p>
//             <?= $paymentStatus === 'approved' 
//                 ? 'Recebemos a confirmação do seu pagamento e nossa cozinha artesanal já foi notificada.' 
//                 : 'Escaneie o QR Code abaixo com o aplicativo do seu banco ou copie a chave para concluir a compra.' ?>
//         </p>
//
//         <!-- Bloco do QR Code e Copia e Cola (apenas se o pagamento estiver pendente) -->
//         <?php if ($paymentStatus !== 'approved' && !empty($order['pix_code'])): ?>
//             <div class="pix-box">
//                 <?php if (!empty($order['pix_qr_base64'])): ?>
//                     <img class="pix-qr" 
//                          src="data:image/png;base64,<?= e($order['pix_qr_base64']) ?>" 
//                          alt="QR Code PIX para o pedido número <?= e($order['number']) ?>"
//                          width="220"
//                          height="220">
//                 <?php endif; ?>
//
//                 <label for="pix-code">Código PIX Copia e Cola</label>
//                 <div class="pix-copy">
//                     <input id="pix-code" 
//                            type="text"
//                            value="<?= e($order['pix_code']) ?>" 
//                            readonly 
//                            aria-label="Código PIX copia e cola">
//                     <button type="button" data-copy-pix aria-label="Copiar código PIX para a área de transferência">
//                         Copiar código
//                     </button>
//                 </div>
//
//                 <?php if (!empty($order['payment_expires_at'])): ?>
//                     <small>Este código PIX é válido até <?= date('d/m/Y \à\s H:i', strtotime((string) $order['payment_expires_at'])) ?></small>
//                 <?php endif; ?>
//             </div>
//         <?php endif; ?>
//
//         <!-- Identificador do Pedido para rastreamento -->
//         <div class="order-number">
//             <span>Número do seu pedido</span>
//             <strong>#<?= e($order['number']) ?></strong>
//         </div>
//
//         <!-- Links de continuidade de navegação -->
//         <div class="success-actions">
//             <a class="button button-primary" href="/minha-conta">
//                 Acompanhar na Minha Conta
//             </a>
//             <a class="text-link" href="/cardapio">
//                 Continuar explorando o cardápio <span aria-hidden="true">→</span>
//             </a>
//         </div>
//     </div>
// </section>
