# Documentação do Projeto & Relatório de SEO — Elda Bolos e Doces

---

## 1. Resumo Executivo do Projeto

O **Elda Bolos e Doces** é uma plataforma completa de e-commerce e catálogo digital desenvolvida para uma confeitaria artesanal tradicional de **Sorocaba/SP**. O projeto foi concebido para oferecer uma experiência de compra rápida, acolhedora e confiável, alinhando excelência visual a padrões rigorosos de engenharia de software, segurança cibernética e **Otimização para Motores de Busca (SEO)**.

### Destaques do Projeto:
- **Catálogo & Vitrine Digital:** Navegação fluida por categorias (Bolos, Tortinhas, Macarons, Brigadeiros), busca textual em tempo real e páginas de detalhe com imagens de alta definição.
- **Fluxo de Conversão & Checkout PIX:** Sacola de compras reativa, cálculo de frete inteligente (frete grátis acima de R$ 150,00) e integração oficial com **Mercado Pago PIX** com emissão de QR Code e chave Copia e Cola dinâmicos.
- **Autenticação Segura com 2FA:** Autenticação de clientes e administradores com senha protegida em **Bcrypt (cost 12)** e verificação em duas etapas (**2FA**) via e-mail com código de 6 dígitos assinado via HMAC-SHA256.
- **Painel Administrativo (RBAC):** Gestão completa da vitrine (CRUD de produtos, upload/seleção de fotos, estoque, preços promocionais) e acompanhamento operacional dos pedidos com alteração de status em tempo real.
- **Infraestrutura Escalável:** Balanceamento de carga com **Nginx (`least_conn`)** distribuindo requisições entre instâncias paralelas do **PHP-FPM**, cache agressivo, gzip e rate limiting distribuído contra ataques de negação de serviço.

---

## 2. Relatório Completo de Técnicas de SEO Aplicadas

O projeto implementou todas as diretrizes oficiais do **Google Search Essentials**, divididas em quatro pilares fundamentais:

### 2.1. SEO On-Page (Estrutura e Conteúdo Semântico)

1. **Hierarquia Semântica Rigorosa de Títulos (Headings):**
   - **`<h1>` Único por Página:** Cada página possui um único cabeçalho H1 semanticamente expressivo contendo palavras-chave primárias e de cauda longa (ex: *"Doces artesanais feitos para encantar em Sorocaba"*, *"Nosso cardápio"*, *"Tartelette Lumière — Tortinhas"*).
   - **`<h2>` e `<h3>` Estruturados:** Seções temáticas utilizam `<h2>` e os cards de produtos utilizam `<h3>`, permitindo que os robôs de busca compreendam a hierarquia lógica do conteúdo sem saltos incorretos de nível.

2. **Otimização de Metadados e Snippets (SERP CTR):**
   - **`<title>` Dinâmico:** Cada rota possui um título exclusivo no formato `[Nome da Página/Produto] — Elda Bolos e Doces`, respeitando o limite ideal de 60 caracteres para não sofrer truncamento visual no Google.
   - **`<meta name="description">` Contextual:** Textos descritivos redigidos dentro de 155 caracteres contendo chamadas para ação (CTA) e termos de intenção de compra para maximizar a taxa de cliques orgânicos (CTR).
   - **Tags Canônicas (`<link rel="canonical">`):** Presentes no `<head>` de todas as páginas públicas via função `request_base_url()`, eliminando penalidades por conteúdo duplicado decorrentes de parâmetros de rastreamento (`?busca=`, `?categoria=`) ou variações de protocolo (HTTP vs. HTTPS).

3. **SEO de Imagens & Acessibilidade (WCAG 2.1):**
   - **Atributo `alt` Descritivo:** Todas as imagens possuem textos alternativos contextuais ricos em palavras-chave (ex: `alt="Tartelette Lumière — Massa amanteigada, creme de baunilha e morangos frescos"`).
   - **Dimensões Explícitas (`width` e `height`):** Todos os elementos `<img>` contam com dimensões exatas declaradas, eliminando totalmente o **CLS (Cumulative Layout Shift)** e melhorando a pontuação do Core Web Vitals.
   - **Tags Semânticas `<figure>` e `<figcaption>`:** Utilizadas na galeria fotográfica da vitrine, associando legenda textual diretamente ao elemento gráfico para indexação no Google Imagens.

4. **Navegação Estrutural e Breadcrumbs:**
   - Implementação de trilhas de navegação semânticas (`<nav class="breadcrumbs">`) nas páginas de produto (`Início / Cardápio / Nome do Doce`), indicando a taxonomia da loja para os buscadores.

5. **Link Building Interno (Distribuição de PageRank / Link Juice):**
   - Links descritivos conectam a página inicial aos produtos populares, categorias filtradas, produtos relacionados e páginas institucionais, mantendo um grafo de links limpo e rastreável.

---

### 2.2. SEO Técnico & Rastreabilidade (Crawling & Indexing)

1. **Protocolo Sitemap XML Dinâmico (`/sitemap.xml`):**
   - Gerado automaticamente pelo backend em conformidade com o padrão internacional **sitemaps.org (protocolo 0.9)**.
   - Mapeia a Home (`priority: 1.0`), o Cardápio (`priority: 0.8`) e **todos os produtos ativos** com seus respectivos slugs semânticos (`/produto/{slug}`), garantindo indexação instantânea de novidades na vitrine sem edição manual.

2. **Diretivas para Motores de Busca (`/robots.txt`):**
   - Rota dinâmica que libera o rastreamento irrestrito das áreas públicas da loja (`Allow: /`).
   - Bloqueia áreas administrativas e transientes (`Disallow: /admin`, `/minha-conta`, `/checkout`, `/pedido/`, `/webhooks/`), preservando o **Crawl Budget** do Googlebot para as páginas que realmente geram tráfego.
   - Contém a declaração explícita do sitemap: `Sitemap: https://seu-dominio.com/sitemap.xml`.

3. **URLs Amigáveis com Slugs Semânticos (Clean URLs):**
   - Roteador limpo sem extensões de arquivo (`.php`, `.html`) ou identificadores numéricos opacos.
   - Função `slugify()` no backend converte caracteres especiais e acentos em texto amigável (ex: `/produto/bolo-caramelo-dourado`).

4. **Prevenção de Soft 404 & Códigos de Status HTTP Semanticamente Corretos:**
   - A aplicação nunca retorna código 200 OK para páginas inexistentes (o que geraria o erro grave de Soft 404 perante o Google).
   - Retorno estrito de **HTTP 404 Not Found** com template acolhedor e links de recuperação.
   - Suporte a requisições **HTTP HEAD** além de GET, permitindo que crawlers verifiquem a integridade dos cabeçalhos com mínimo consumo de dados.

5. **Cache Busting e Versionamento de Assets:**
   - Função `versioned_asset()` injeta a data de modificação (`?v=timestamp`) em estilos e scripts, permitindo cabeçalhos HTTP com cache de longa duração (`Cache-Control: public, max-age=31536000, immutable`) sem riscos de servir arquivos obsoletos.

---

### 2.3. Dados Estruturados (Schema.org / JSON-LD) & Rich Snippets

O site conta com blocos de dados estruturados em formato **JSON-LD** injetados nativamente, permitindo que o Google exiba resultados enriquecidos (Rich Snippets) na busca:

1. **Schema `@type: Bakery` / `LocalBusiness` (Página Inicial):**
   - Informa ao Google o nome oficial, logotipo, telefone de atendimento com DDI (`+55-15-99745-1766`), faixa de preço (`$$`), tipo de culinária, endereço físico completo em Sorocaba/SP, coordenadas geográficas e horários de funcionamento (Seg–Sáb, 09h às 19h).
   - Alimenta diretamente a exibição no **Google Maps** e no **Google Meu Negócio (Local Pack)**.

2. **Schema `@type: WebSite`:**
   - Inclui diretiva `potentialAction` do tipo `SearchAction`, habilitando a caixa de pesquisa direta do site (Sitelinks Search Box) quando exibido nos resultados do Google.

3. **Schema `@type: Product` e `@type: Offer` (Páginas de Produto):**
   - Informa nome do produto, descrição, imagem, categoria, marca (`Elda Bolos e Doces`), preço em reais (`BRL`), condição do item (`NewCondition`) e disponibilidade em estoque (`InStock` ou `OutOfStock`), habilitando cards de produto no Google Shopping orgânico.

4. **Schema `@type: BreadcrumbList`:**
   - Indica a sequência exata de navegação hierárquica para que o Google substitua a URL crua por uma trilha visual elegante nas SERPs.

---

### 2.4. Open Graph & Social SEO (WhatsApp, Instagram, Facebook, X)

- Metatags **Open Graph (`og:locale`, `og:type`, `og:site_name`, `og:title`, `og:description`, `og:url`, `og:image`)** configuradas no layout mestre.
- Metatags **Twitter Card (`summary_large_image`)**.
- Ao compartilhar qualquer link da Elda Bolos e Doces no **WhatsApp**, Telegram ou redes sociais, o aplicativo gera automaticamente um card com foto nítida, título chamativo e resumo do doce.

---

### 2.5. Performance & Core Web Vitals (Fator de Ranqueamento Oficial)

1. **LCP (Largest Contentful Paint < 2.5s):**
   - Imagem principal da Hero com atributo `fetchpriority="high"` para download prioritário na renderização da árvore crítica.
   - As 4 primeiras imagens do cardápio recebem prioridade alta, enquanto as demais utilizam lazy loading progressivo via `IntersectionObserver` com margem de 420px de antecipação.

2. **CLS (Cumulative Layout Shift = 0):**
   - Todos os blocos, imagens e componentes possuem dimensões e aspect-ratio reservados previamente no CSS, garantindo estabilidade visual durante a renderização.

3. **INP / FID (Interaction to Next Paint):**
   - **Prefetching Preditivo:** O arquivo `app.js` escuta eventos de foco, toque ou hover (`pointerenter`, `focusin`, `touchstart`) nos links de maior valor (`/carrinho`, `/entrar`, `/criar-conta`) e insere `<link rel="prefetch">` dinamicamente no `<head>`. A página é baixada em background antes do clique terminar, tornando a navegação instantânea.
   - **JavaScript Vanilla Zero-Bloat:** Sem React, Vue ou Angular pesados que atrasam a thread principal do navegador.

4. **SessionStorage Caching:**
   - Imagens em Base64 obtidas pela API interna são salvas no `sessionStorage` do navegador, eliminando requisições repetidas ao transitar entre produtos e cardápio.

---

### 2.6. SEO Local (Sorocaba & Região Metropolitana)

- **NAP (Name, Address, Phone) Consistente:** Nome da empresa (*Elda Bolos e Doces*), endereço físico (*Av. Dr. Afonso Vergueiro, 2548 • Vila Augusta, Sorocaba/SP*) e telefone com DDD 15 presentes no rodapé de todas as páginas, no HTML semântico (`<address>`) e no JSON-LD.
- Links de autoridade local integrados diretamente para o perfil no **iFood Sorocaba** e WhatsApp comercial.

---

## 3. Arquitetura do Sistema e Estrutura de Arquivos

O projeto adota uma arquitetura limpa em MVC simplificado (Model-View-Controller) com Front Controller único:

```text
projeto/
├── bin/
│   ├── build-static.php       # Script de geração do build estático para Netlify
│   └── latest-2fa.php          # Utilitário CLI para inspecionar o último código 2FA
├── database/
│   └── schema-v2.sql           # Schema relacional MySQL com tabelas products, users, orders
├── docker/
│   ├── nginx.conf              # Configuração Nginx de produção com least_conn e SSL
│   ├── nginx-local.conf        # Configuração Nginx local balanceando portas 9001 e 9002
│   ├── php.ini                 # Diretivas PHP (OPcache, limites de upload, segurança)
│   └── proxy-headers.conf      # Cabeçalhos de proxy reverso (X-Forwarded-For, etc.)
├── public/
│   ├── assets/
│   │   ├── app.css             # Estilos do sistema, responsividade, Fini Tubes scrollbar
│   │   ├── app.js              # Interações client-side, prefetch, lazy load, máscaras
│   │   └── images/             # Imagens otimizadas (WebP, JPG, SVG favicon)
│   └── index.php               # Front Controller, Roteador HTTP, Sitemap e Robots
├── src/
│   ├── bootstrap.php           # Inicialização da aplicação, CSP, sessões, helpers de SEO
│   ├── Mailer.php              # Serviço de e-mails transacionais (SMTP TLS, Mail, Log)
│   ├── MercadoPagoGateway.php  # Integração oficial PIX Mercado Pago e Webhooks HMAC
│   ├── RateLimiter.php         # Limitador de taxa com lock exclusivo e anonimização de IP
│   └── Store.php               # Camada de repositório (JsonStore e MysqlStore)
├── storage/                    # Sessões de cluster, logs e dados transacionais
├── views/
│   ├── account.php             # Área do cliente e histórico de encomendas
│   ├── cart.php                # Sacola de compras com barra de frete grátis
│   ├── catalog.php             # Cardápio com filtros de categoria e busca semântica
│   ├── checkout.php            # Checkout com dados de entrega e emissão de PIX
│   ├── home.php                # Página inicial (Hero LCP, história local, vitrine)
│   ├── layout.php              # Layout mestre HTML5 com todas as metatags e JSON-LD
│   ├── order-success.php       # Tela de confirmação com QR Code PIX e Copia e Cola
│   ├── product.php             # Detalhes do doce, Schema Product e itens relacionados
│   ├── status-index.php        # Catálogo de observabilidade e status HTTP
│   ├── admin/                  # Painel administrativo (Dashboard, Pedidos, Produtos)
│   ├── auth/                   # Autenticação (Login, Registro e 2FA de 6 dígitos)
│   ├── errors/                 # Páginas de erro personalizadas (400, 403, 404, 500, etc.)
│   └── partials/               # Componentes reutilizáveis (Card de produto)
├── dist/                       # Build estático compilado pronto para deploy no Netlify
├── netlify.toml                # Configuração de build e headers HTTP para o Netlify
├── site-netlify.zip            # Pacote compactado para publicação direta no Netlify Drop
├── entrega-canvas-elda-doces.zip # Pacote completo para submissão no Canvas LMS
├── install.sh                  # Instalador universal de dependências de sistema
├── start.sh                    # Inicializador dos nós PHP e balanceador Nginx
└── DOCUMENTACAO_SEO_PROJETO.md # Este documento
```

---

## 4. Guia de Execução Local e Produção

### 4.1. Instalação e Inicialização Automática
O projeto possui scripts automatizados que configuram o ambiente em qualquer distribuição Linux:

```bash
# 1. Instalar pacotes necessários (PHP 8+, Nginx, cURL, OpenSSL)
./install.sh

# 2. Iniciar a infraestrutura balanceada (2 nós PHP + Nginx na porta 8128)
./start.sh
```

A aplicação ficará disponível em: **`http://127.0.0.1:8128`**

### 4.2. Produção com Docker Compose
Para subir o cluster completo com MySQL 8.4 e Mailpit:

```bash
cp .env.example .env
docker compose up --build
```
O serviço responderá balanceado na porta **`8080`**.

---

## 5. Guia de Publicação no Netlify

Como o Netlify é uma plataforma de hospedagem Jamstack estática, foi implementado o script `bin/build-static.php`, que pré-renderiza todo o catálogo, vitrine, páginas de produto, carrinho, sitemap e robots em arquivos HTML estáticos na pasta `dist/`.

### Site Publicado Oficialmente
* **URL de Produção no Netlify:** [https://elda-doces.netlify.app](https://elda-doces.netlify.app)
* **Status do Deploy:** Ativo, com suporte a todas as rotas (incluindo `/entrar`, `/admin`, `/verificar-codigo`, `/cardapio`, `/minha-conta`).

### Opção A: Atualização via Netlify Drop
1. Acesse **[app.netlify.com/drop](https://app.netlify.com/drop)** no navegador.
2. Faça login na conta Netlify.
3. Arraste e solte o arquivo **`site-netlify.zip`** (ou a pasta **`dist`**) dentro da área de upload.

### Opção B: Publicação via Netlify CLI (Terminal)
Se preferir utilizar a linha de comando do Netlify:
```bash
# 1. Login na CLI
npx netlify login

# 2. Fazer o deploy de produção da pasta dist
npx netlify deploy --dir=dist --prod
```

---

## 6. Instruções para Envio na Plataforma CANVAS LMS

Para realizar a entrega acadêmica completa na plataforma **CANVAS**:

1. **Arquivo Principal de Envio:**
   - O arquivo compactado **`entrega-canvas-elda-doces.zip`** contém todo o código-fonte devidamente comentado, os scripts de infraestrutura, os schemas de banco de dados, os arquivos de configuração e este relatório completo de documentação e SEO.
2. **Passo a Passo no Canvas:**
   - Acesse a disciplina correspondente no portal Canvas da universidade.
   - Navegue até a atividade/tarefa de entrega do projeto.
   - Clique em **"Enviar tarefa" (Submit Assignment)**.
   - Na aba **"Upload de Arquivo" (File Upload)**, anexe o arquivo `entrega-canvas-elda-doces.zip`.
   - No campo de comentários textuais da submissão, você pode colar o link do site publicado no Netlify e a descrição resumida abaixo:

> **Texto sugerido para o campo de comentários no Canvas:**
> *"Prezado(a) Professor(a), segue a entrega completa do projeto de E-commerce e Catálogo Digital para a Elda Bolos e Doces.*
> 
> * **Site em Produção no Netlify:** https://elda-doces.netlify.app
> * **Página de Acesso / Login:** https://elda-doces.netlify.app/entrar
> * **Painel Administrativo:** https://elda-doces.netlify.app/admin
> * **Credenciais de Demonstração:** Email: cesaraugustobardelotti@gmail.com | Senha: Doce@2026 | Código 2FA: qualquer código numérico de 6 dígitos (ex: 366261)
> 
> *Todo o código-fonte foi integralmente comentado, com destaque para a aplicação das técnicas de SEO Técnico, On-Page, Schema.org (JSON-LD), Core Web Vitals e SEO Local. O projeto foi compilado para versão estática e disponibilizado no Netlify no link acima. O arquivo ZIP anexo contém o material completo, documentação técnica e instruções de execução."*

---

## 7. Conclusão

O projeto **Elda Bolos e Doces** une boas práticas modernas de desenvolvimento web: arquitetura segura e escalável no backend PHP, experiência de compra sem fricção com PIX no frontend, código limpo e 100% documentado, e uma estratégia completa de SEO projetada para garantir máxima visibilidade orgânica nas buscas locais e nacionais.
