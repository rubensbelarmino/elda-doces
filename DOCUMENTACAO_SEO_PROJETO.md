# SEO e apresentação do projeto — Elda Bolos e Doces

Este documento registra o que o código implementa e o que ainda precisa ser verificado na implantação. Não é um relatório de auditoria externa, de posições no Google ou de métricas coletadas em produção. Para instalação, configuração, rotas e persistência, consulte o [README](README.md).

## 1. Arquitetura que gera as páginas

A aplicação usa PHP e templates renderizados no servidor. `public\index.php` é um **front controller procedural**; `src\bootstrap.php` inicializa os serviços e define funções globais. Não há framework MVC nem namespace `App`. `Store` é uma **interface**, com implementações `JsonStore` e `MysqlStore` em `src\Store.php`.

Sem `DB_DSN`, o conteúdo vem de `storage\data.json`. Com DSN, vem de SQL/MySQL nas tabelas `products`, `users`, `orders` e `order_items`. O catálogo aceita os parâmetros `categoria` e `busca` no servidor. Busca e filtros não são uma API de pesquisa instantânea. As páginas públicas principais são `/`, `/cardapio` e `/produto/{slug}`.

| Arquivo | Responsabilidade relacionada a SEO/apresentação |
| --- | --- |
| `public\index.php` | Roteamento, títulos/descrições passados às views, redirecionamentos, robots e sitemap |
| `src\bootstrap.php` | `request_base_url()`, `versioned_asset()`, renderização e helpers |
| `views\layout.php` | HTML comum, título, metas, canonical, Open Graph/Twitter, JSON-LD institucional e analytics |
| `views\home.php` | Hero, conteúdo institucional, vitrine e nota decorativa |
| `views\catalog.php` | Listagem e filtros de produtos |
| `views\product.php` | Detalhe, breadcrumbs e JSON-LD de produto/oferta |
| `views\partials\product-card.php` | Cards e links de produto |
| `public\assets\app.css` e `app.min.css` | Fonte e CSS servido pelo layout |
| `public\assets\app.js` e `app.min.js` | Fonte e JavaScript servido pelo layout |
| `bin\build-css.cjs` | Geração/verificação reproduzível do CSS minificado |
| `bin\build-static.php` | Exportação experimental, com restrições descritas abaixo |

As cópias em `storage\backup-frontend\` e `storage\backup-src\` são históricas, não a implementação ativa. Um arquivo ou uma regra preservada nessas cópias não comprova comportamento atual.

## 2. Metadados e URLs

### Implementação no layout

- Documento com idioma `pt-BR`, viewport e metadados de tema.
- `<title>` derivado do título fornecido pela página, com sufixo da marca quando necessário; há título e descrição padrão quando a página não os fornece. Não há garantia de tamanho máximo de 60/155 caracteres.
- Canonical formado por `request_base_url()` e pelo caminho da requisição, **sem query string**. Filtros `?busca=` e `?categoria=` do cardápio apontam, portanto, para a mesma URL canônica de `/cardapio`.
- Alternates `pt-BR`, `pt` e `x-default` apontam para essa mesma URL; não significam que existam versões traduzidas.
- Open Graph com locale, tipo, nome do site, título, descrição, URL e imagem; Twitter Card do tipo `summary_large_image`. A imagem padrão é `hero-doces.webp`, salvo imagem específica fornecida pela página.

Essas tags fornecem informações aos consumidores. Não garantem snippets específicos, cards em todos os aplicativos, eliminação de conteúdo duplicado ou aumento de cliques. A apresentação final depende do crawler, da disponibilidade das URLs e de seus caches.

### Configuração de domínio

`APP_URL` é usado por `request_base_url()` para compor URLs absolutas. Entretanto, o redirecionamento HTTP/`www` de hosts externos em `public\index.php` tem destino fixo **`https://elda-doces.com`**, independente dessa variável. `localhost` e `127.0.0.1` são exceções para desenvolvimento.

Ao hospedar em outro domínio, revise também esse redirecionamento e o proxy. Rotas e assets assumem a raiz do domínio, não instalação em subpasta. `.env.example` contém um domínio de exemplo, que precisa ser substituído. O parser de `.env` sobrescreve variáveis de ambiente existentes.

## 3. Rastreamento e status HTTP

### Sitemap e robots

`/sitemap.xml` é gerado dinamicamente com a home, o cardápio e produtos ativos. Ele facilita descoberta de URLs; não garante inclusão no índice nem indexação imediata.

`/robots.txt` declara o sitemap e contém restrições para:

```text
/admin
/minha-conta
/checkout
/pedido/
/webhooks/
```

Robots é uma orientação para crawlers, **não controle de acesso nem garantia de desindexação**. O layout comum emite meta robots `index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1`; não existe nessa meta uma política específica `noindex` para cada área sensível. Login, permissões e propriedade do pedido continuam sendo verificados pelo backend.

### Respostas e assets

O controlador possui tratamento de páginas inexistentes e templates para 400, 401, 403, 404, 429, 500 e 503; rotas de leitura aceitam GET/HEAD. Verifique os status no ambiente implantado, pois proxy e exportação podem alterar o resultado. Em particular, a exportação estática contém catch-all com 200, diferente de uma resposta 404 do PHP.

`versioned_asset()` adiciona a data de modificação ao endereço de assets. Nginx contém configuração de cache/compressão; o comportamento depende do servidor utilizado e não deve ser presumido para todo HTML ou para o servidor embutido do PHP. O versionamento não dispensa verificação de cache após a implantação.

`/health` expõe `status`, `service`, `node` e `time` após o bootstrap; não é teste de SMTP, pagamento, SEO ou disponibilidade completa. `/status` apenas apresenta as telas HTTP disponíveis.

## 4. Dados estruturados e informação comercial

### JSON-LD realmente presente

`views\layout.php` emite um `@graph` com:

- **`Bakery`**: identidade da loja, URL, logotipo, imagem, descrição, telefone, faixa de preço, endereço, coordenadas, horário e links externos. Esses valores estão definidos no template.
- **`WebSite`**: nome, URL, idioma e `potentialAction` do tipo **`SearchAction`**, cujo alvo é `/cardapio?busca={search_term_string}`.

`views\product.php` emite outro grafo com:

- **`Product` / `Offer`**: nome, descrição, imagem, categoria, marca, preço em BRL, condição e disponibilidade derivada do estoque cadastrado.
- **`BreadcrumbList`**: Início → Cardápio → Produto, também apresentado na navegação HTML.

Não há `aggregateRating` no JSON-LD ativo. O `SearchAction` ainda existe no markup, mas sua presença **não garante caixa de busca nos resultados**. Os schemas tampouco garantem rich results, Google Shopping ou atualização de cadastro no Google Maps. Valide o JSON efetivamente renderizado e sua correspondência com o conteúdo visível antes de solicitar indexação.

### Nota e avaliações

A home contém **uma única nota numérica, 4.9**, em um elemento decorativo do hero com `aria-hidden`. É texto estático, não integração com API de avaliações nem nota comprovada por esta documentação. Não há base para apresentar uma contagem de avaliações verificadas ou tratar os depoimentos da página como registros autenticados por um serviço externo.

O template contém dados comerciais de Sorocaba/SP, incluindo endereço, telefone, horários e links para Instagram, iFood e WhatsApp. Sua presença no código não confirma que continuem atuais. A operação deve revisar esses dados e autorizar o uso das imagens e dos depoimentos. Links externos não sincronizam estoque, avaliações ou pedidos.

A disponibilidade em `Offer` reflete o estoque armazenado. O carrinho limita quantidades, mas **a criação de pedido não decrementa estoque automaticamente**; a informação comercial exige manutenção operacional.

## 5. Frontend, acessibilidade e desempenho

O layout serve `app.min.css` e `app.min.js`. CSS usa variáveis, Grid/Flexbox, media queries, foco visível e regras de redução de movimento. As pilhas serif/sans usam fontes locais/de sistema; a menção a Inter não comprova carregamento de fonte externa. Há tags de antecipação de conexão no layout, mas isso não comprova uso de Google Fonts.

A fonte CSS inclui os estilos ativos, inclusive `save-address` e `password-toggle`. Sua manutenção usa Node.js/npm e `clean-css` 5.3.3; não há dependência de Node no runtime da loja:

```powershell
npm ci
npm run build:css
npm run check:css
npm test
```

O gerador normaliza finais de linha para reprodução em Windows/Linux. O projeto não fornece pipeline equivalente para minificar JavaScript; fonte e arquivo servido precisam ser mantidos coerentes.

O JavaScript implementa prefetch de login/cadastro/carrinho, máscaras, toast com transição após 4,8 segundos, cópia PIX, foco no botão após seis dígitos, preview de upload e exibição de senha. O menu atualiza `aria-expanded` e fecha por Escape/clique fora, mas não transfere nem restaura automaticamente o foco.

As imagens usam URLs diretas com API Base64 como alternativa. Há `IntersectionObserver` com margem de 1.500 px, prioridades e uso de `requestIdleCallback`. **Não há cache de imagens em `sessionStorage`.** O hero usa `hero-doces.webp`; alguns elementos incluem dimensões explícitas e prioridade de carregamento.

Essas técnicas não comprovam economia de banda, navegação instantânea, CLS zero, LCP abaixo de um limiar ou conformidade WCAG. São necessárias medições representativas e testes de teclado, leitores de tela, contraste, zoom e movimento reduzido. A dimensão de uma imagem ou o uso de WebP isoladamente não determina o desempenho da página.

## 6. Analytics

O layout lê:

- `GOOGLE_ANALYTICS_ID`, com padrão `G-ELDADOCES0`;
- `GOOGLE_TAG_MANAGER_ID`, ou `GTM_ID`, com padrão `GTM-T98GCPGN`.

Ele inicializa `dataLayer`, injeta `/gtm.js`, inclui configuração de analytics e iframe `noscript` de Google Tag Manager. IDs padrão e scripts presentes **não comprovam coleta ativa**, propriedade da conta, consentimento ou relatórios de conversão. Antes de publicar, confirme quais tags realmente executam e revise configuração, privacidade e consentimento. Não publique dados de clientes ou credenciais em capturas usadas para validar analytics.

## 7. Aplicação dinâmica versus exportação estática

A loja dinâmica depende de PHP, sessão, armazenamento, autenticação, e-mail e Mercado Pago. O Compose fornece dois nós PHP-FPM, MySQL, Nginx HTTP na porta 8080 e Mailpit; não inclui TLS pronto nem e-mail de produção. O volume `product_images` é compartilhado com escrita pelos nós PHP e somente leitura pelo Nginx. Upload JPG/PNG tem limite de 4 MiB, com corpo de até 5 MiB nos POST administrativos de produto para acomodar multipart.

O checkout grava/atualiza endereço somente quando `save_address=1`; desmarcado preserva o anterior. CPF segue para o `payer` do Mercado Pago, não para o endereço salvo ou os dados de cliente dos novos pedidos. Não há limpeza retroativa de registros ou backups. O webhook exige segredo e assinatura antes da consulta do pagamento; não implementa janela de idade/replay. O contador PIX não cancela pedidos automaticamente, e o dashboard não se atualiza em tempo real.

### Restrições do exportador

`bin\build-static.php` requer PHP/bootstrap, cURL, shell POSIX e um servidor já ativo em `127.0.0.1:8128`. Ele **apaga `dist` e sobrescreve `netlify.toml`**. Esses caminhos são saídas geradas, não uma distribuição pronta versionada.

Além de capturar páginas HTTP, o script renderiza manualmente telas de conta, checkout, administração e 2FA com dados do `Store` e identidades de demonstração. **Há risco de expor pedidos, métricas e dados pessoais.** Execute apenas contra base descartável e revise todos os arquivos antes de disponibilizá-los.

A saída estática:

- Não oferece autenticação, POST, carrinho persistente, PIX, API, webhook ou administração protegida.
- Pode ficar incompleta se requisições HTTP falharem; o processo pode continuar com páginas ausentes.
- Não garante cópia completa de GTM, manifest e ícones.
- Inclui `_redirects` com catch-all para `index.html` e status 200, sujeito a soft 404.

Não há deploy/URL Netlify ou pacotes `site-netlify.zip` e `entrega-canvas-elda-doces.zip` verificados. Mostrar um formulário exportado não torna login ou checkout funcionais. Qualquer entrega estática deve ser identificada como demonstração visual, sem credenciais reais e sem dados privados.

## 8. Roteiro de verificação antes de publicar

1. **Ambiente:** confirmar domínio, HTTPS/proxy, raiz pública `public`, `APP_URL` e redirecionamento fixo. Não expor `.env`, `storage`, logs ou backups.
2. **Conteúdo:** revisar endereço, telefone, horário, imagens, produtos, estoque e alegações de avaliações. Manter a nota decorativa distinta de avaliações verificadas.
3. **URLs:** conferir home, cardápio, produto ativo/inativo, página inexistente e respostas GET/HEAD. Verificar canonical com e sem parâmetros e destino de todos os links.
4. **Rastreamento:** inspecionar robots e sitemap no domínio correto. Revisar a política de indexação de páginas de login, conta e operação; robots não substitui autorização.
5. **Schemas:** validar o JSON-LD renderizado com diferentes nomes/descrições de produto, comparar preço e disponibilidade com a página e corrigir inconsistências antes de publicação.
6. **Frontend:** executar verificação CSS/testes, inspecionar assets carregados e testar navegação móvel, teclado, foco, contraste e redução de movimento.
7. **Medição:** medir LCP, CLS e INP em condições representativas; validar tags e consentimento sem dados reais. Registrar resultados e contexto, em vez de presumir metas atingidas.
8. **Exportação, se necessária:** usar somente base descartável, revisar arquivos, retirar conteúdo privado e testar status/rotas. Não publicar automaticamente a saída do script.

Os testes isolados do repositório, validação de configuração e inspeção de fontes apoiam manutenção, mas não demonstram ranking, indexação, métricas reais, acessibilidade completa ou funcionamento de infraestrutura e integrações em produção.
