<?php
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
//
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
//         <div class="order-number">
//             <span>Número do seu pedido</span>
//             <strong>#<?= e($order['number']) ?></strong>
//         </div>
//
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
