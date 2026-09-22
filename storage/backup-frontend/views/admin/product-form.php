<?php
/**
 * ============================================================================
 * FORMULÁRIO DE PRODUTO (CRUD ADMIN) — ELDA BOLOS E DOCES
 * ============================================================================
 * Tela de criação e edição de doces, bolos e tortas da vitrine.
 *
 * TÉCNICAS DE SEO APLICADAS NESTE ARQUIVO:
 * ----------------------------------------------------------------------------
 * 1. GERENCIAMENTO DE SLUGS AMIGÁVEIS (SEO-FRIENDLY URLS):
 *    - O formulário permite definir ou gerar automaticamente o "slug" do produto
 *      (ex: "bolo-caramelo-dourado"), que forma a URL canônica pública.
 *    - URLs semânticas com palavras-chave facilitam o rastreamento e indexação
 *      no Google muito mais do que identificadores opacos (ex: ?id=45).
 *
 * 2. METADADOS E DESCRIÇÃO PARA OTIMIZAÇÃO DE BUSCA:
 *    - Campo de descrição obrigatório com até 800 caracteres para alimentar
 *      o conteúdo textual e a tag <meta name="description"> da página de detalhe.
 * ============================================================================
 */
?>
<section class="admin-form-page" aria-label="Edição de produto">
    <div class="container narrow">
        <a class="back-link" href="/admin">← Voltar ao painel administrativo</a>

        <div class="admin-form-card">
            <div>
                <p class="overline">Gestão do cardápio</p>
                <h1><?= $product ? 'Editar doce artesanal' : 'Novo doce artesanal' ?></h1>
                <p>Atualize os detalhes, preços e imagens que aparecem para os clientes no cardápio.</p>
            </div>

            <form method="post" enctype="multipart/form-data">
                <!-- Proteção contra ataques CSRF -->
                <input type="hidden" name="_token" value="<?= csrf_token() ?>">

                <div class="form-grid">
                    <!-- Nome do Produto (H1 da página de produto) -->
                    <label class="wide">
                        Nome do produto
                        <input name="name" required value="<?= e($product['name'] ?? '') ?>" placeholder="Ex: Bolo de Nozes com Doce de Leite">
                    </label>

                    <!-- Slug da URL (Fator crítico para SEO On-Page) -->
                    <label class="wide">
                        Slug da URL <small>(opcional — gerado automaticamente se vazio)</small>
                        <input name="slug" value="<?= e($product['slug'] ?? '') ?>" placeholder="bolo-nozes-doce-de-leite">
                    </label>

                    <!-- Descrição rica em palavras-chave -->
                    <label class="full">
                        Descrição do produto
                        <textarea name="description" rows="4" required placeholder="Descreva os ingredientes nobres, sabor e texturas..."><?= e($product['description'] ?? '') ?></textarea>
                    </label>

                    <!-- Preço em Reais -->
                    <label>
                        Preço (R$)
                        <input name="price" inputmode="decimal" required value="<?= isset($product['price_cents']) ? number_format((int) $product['price_cents'] / 100, 2, ',', '') : '' ?>" placeholder="89,90">
                    </label>

                    <!-- Estoque disponível -->
                    <label>
                        Estoque disponível
                        <input type="number" name="stock" min="0" required value="<?= (int) ($product['stock'] ?? 0) ?>">
                    </label>

                    <!-- Categoria para agrupamento e SEO -->
                    <label>
                        Categoria
                        <input name="category" required value="<?= e($product['category'] ?? '') ?>" placeholder="Ex: Bolos, Brigadeiros, Tortinhas">
                    </label>

                    <!-- Descrição da porção para clareza do cliente -->
                    <label>
                        Porção / Rendimento
                        <input name="portion" required value="<?= e($product['portion'] ?? '') ?>" placeholder="Serve 8 a 10 pessoas">
                    </label>

                    <!-- Seletor e Upload de Imagem (JPG ou PNG) -->
                    <div class="full product-image-field">
                        <span class="field-label">Foto do doce artesanal (JPG ou PNG)</span>
                        <p class="field-hint">Envie uma foto nova do doce (JPG ou PNG até 4MB) ou selecione uma imagem da galeria existente.</p>

                        <div class="image-upload-container">
                            <!-- Dropzone e Seletor de Arquivo -->
                            <div class="image-upload-dropzone" id="product_dropzone">
                                <input type="file" 
                                       name="image_file" 
                                       id="product_image_file" 
                                       accept=".jpg,.jpeg,.png,image/jpeg,image/png" 
                                       class="sr-only-file-input">
                                <label for="product_image_file" class="dropzone-inner-label">
                                    <svg class="upload-icon" viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <strong class="upload-title">Clique para selecionar imagem do seu computador</strong>
                                    <span class="upload-types">Formatos aceitos: JPG, JPEG ou PNG (máx. 4MB)</span>
                                </label>
                            </div>

                            <!-- Prévia da Imagem -->
                            <div class="image-upload-preview-box">
                                <div class="preview-thumb-wrap">
                                    <img id="product_preview_img" 
                                         src="<?= !empty($product['image']) ? '/assets/images/' . e($product['image']) : '/assets/images/chocolate.jpg' ?>" 
                                         alt="Prévia da imagem do produto"
                                         class="is-loaded"
                                         width="200" 
                                         height="140">
                                </div>
                                <div class="preview-meta">
                                    <span id="product_preview_name" class="preview-filename"><?= !empty($product['image']) ? e($product['image']) : 'chocolate.jpg (padrão)' ?></span>
                                    <span id="product_preview_badge" class="preview-badge"><?= !empty($product['image']) ? 'Foto atual' : 'Padrão' ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Opção alternativa: Seleção da Galeria -->
                        <div class="image-preset-picker">
                            <label for="product_image_select">
                                <span>Ou prefere selecionar uma foto já cadastrada na vitrine?</span>
                                <select name="image" id="product_image_select">
                                    <option value="">-- Usar nova foto enviada acima --</option>
                                    <?php 
                                    $imageOptions = [
                                        'chocolate.jpg' => 'Chocolate nobre editorial',
                                        'morango.jpg' => 'Morango fresco editorial',
                                        'pistache.jpg' => 'Pistache e praliné editorial',
                                        'caramelo.jpg' => 'Caramelo dourado editorial',
                                        'elda-doces.jpg' => 'Elda — seleção de sobremesas',
                                        'elda-morango.jpg' => 'Elda — vitrine de morangos',
                                        'elda-chocolate.jpg' => 'Elda — vitrine de chocolate'
                                    ];
                                    if (!empty($product['image']) && !array_key_exists($product['image'], $imageOptions)):
                                        $imageOptions[$product['image']] = 'Imagem atual: ' . $product['image'];
                                    endif;

                                    foreach ($imageOptions as $file => $label): 
                                    ?>
                                        <option value="<?= e($file) ?>" <?= ($product['image'] ?? '') === $file ? 'selected' : '' ?>>
                                            <?= e($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                    </div>

                    <!-- Checkboxes de visibilidade e destaque na vitrine -->
                    <div class="check-group">
                        <label>
                            <input type="checkbox" name="featured" <?= !empty($product['featured']) ? 'checked' : '' ?>>
                            Produto em destaque na página inicial (Home)
                        </label>
                        <label>
                            <input type="checkbox" name="active" <?= $product === null || !empty($product['active']) ? 'checked' : '' ?>>
                            Visível publicamente no cardápio
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <a class="button button-outline" href="/admin">Cancelar</a>
                    <button class="button button-primary" type="submit">
                        Salvar produto <span aria-hidden="true">→</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
