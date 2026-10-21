# Elda Bolos e Doces

Aplicação PHP para catálogo, carrinho, autenticação por senha e código por e-mail, checkout PIX via Mercado Pago e administração de produtos e pedidos. O conteúdo apresenta uma confeitaria de Sorocaba/SP. WhatsApp, iFood e Instagram são links externos, não integrações de catálogo ou pedidos.

Este documento descreve o código do repositório, não certifica disponibilidade do site, desempenho, acessibilidade ou prontidão para produção. Licença: [MIT](LICENSE). Detalhes de metadados e rastreamento: [Documentação de SEO](DOCUMENTACAO_SEO_PROJETO.md).

## Arquitetura

O ponto de entrada é `public\index.php`: um **front controller procedural**, com roteamento e tratamento das requisições no próprio arquivo. `src\bootstrap.php` carrega classes explicitamente, lê configuração, inicia sessão e expõe funções globais. Os templates PHP em `views\` são renderizados com `views\layout.php`. Não há framework MVC, namespace `App` nem autoloader Composer.

```text
Navegador → servidor web → public\index.php → src\bootstrap.php
                                ├─ views\ + layout + assets
                                ├─ Store → JsonStore OU MysqlStore
                                ├─ Mailer → log / SMTP / mail()
                                └─ MercadoPagoGateway → API PIX
```

`Store` é uma **interface**, implementada por `JsonStore` e `MysqlStore` no mesmo arquivo `src\Store.php`:

- Sem `DB_DSN`: JSON em `storage\data.json`, com bloqueios de arquivo para as operações de persistência.
- Com `DB_DSN`: PDO/MySQL, com SQL real nas tabelas `products`, `users`, `orders` e `order_items`, definidas em `database\schema-v2.sql`. A inicialização executa migração e seed. Falhas de inicialização retornam 503; não há fallback silencioso para JSON.
- O seed inclui oito produtos e contas de administrador e cliente com senhas fixas no código. **Remova ou substitua essas contas/credenciais antes de expor o serviço.** Elas não são segredos adequados para produção.

### Dados persistentes

| Local | Conteúdo |
| --- | --- |
| `storage\data.json` ou MySQL | Produtos, usuários e pedidos, conforme o adaptador escolhido |
| `storage\sessions\` | Identidade autenticada, carrinho e desafio 2FA; não há tabela de códigos 2FA |
| `storage\runtime\rate-limits.json` | Estado do limitador por IP, inclusive com MySQL |
| `storage\runtime\password-resets.json` | Recuperação de senha, inclusive com MySQL |
| `storage\mail\` | E-mails HTML e referência ao último e-mail; contém material sensível |
| `storage\app.key` | Chave gerada quando `APP_KEY` está ausente/vazia |
| `public\assets\images\` | Imagens embarcadas e uploads administrativos |

O endereço salvo fica em `delivery_address` no JSON ou `users.delivery_json` no MySQL. Os dados do cliente de um pedido ficam no próprio pedido, em `orders.customer_json` no MySQL. Faça backup de banco, arquivos persistentes e imagens; usar MySQL não torna todo o estado da aplicação independente do disco.

## Estrutura do repositório

A árvore destaca arquivos ativos e distingue os diretórios históricos dos dados gerados em execução.

```text
elda-doces\
├─ bin\
│  ├─ build-css.cjs              # Geração/verificação do CSS minificado
│  ├─ build-static.php           # Exportação experimental de páginas
│  └─ latest-2fa.php             # Leitura CLI do último código no log de e-mail
├─ database\schema-v2.sql
├─ docker\
│  ├─ nginx.conf                 # Nginx do Compose, HTTP e balanceamento
│  ├─ nginx-local.conf           # Configuração local com caminhos fixos
│  ├─ php.ini
│  └─ proxy-headers.conf
├─ public\                      # Única raiz pública
│  ├─ index.php
│  ├─ gtm.js
│  ├─ favicon.ico
│  ├─ apple-touch-icon.png
│  ├─ site.webmanifest
│  └─ assets\
│     ├─ app.css / app.min.css
│     ├─ app.js / app.min.js
│     └─ images\                # Inclui hero-doces.webp e favicon.svg
├─ src\
│  ├─ bootstrap.php
│  ├─ Store.php
│  ├─ Mailer.php
│  ├─ MercadoPagoGateway.php
│  └─ RateLimiter.php
├─ storage\
│  ├─ backup-frontend\          # Cópias históricas de CSS/JS/views, não ativas
│  └─ backup-src\               # Cópias históricas de index.php/src, não ativas
│                                # Dados/sessões/runtime/mail gerados conforme uso
├─ views\
│  ├─ layout.php / home.php / catalog.php / product.php
│  ├─ cart.php / checkout.php / order-success.php / account.php
│  ├─ status-index.php
│  ├─ partials\product-card.php
│  ├─ auth\                     # login, register, two-factor,
│  │                            # forgot-password e reset-password (.php)
│  ├─ admin\                    # dashboard, orders e product-form (.php)
│  └─ errors\status.php
├─ tests\                       # Testes isolados; entrada run.php
├─ imagens\                     # Material de referência, incluindo produtos.txt
├─ produtos_ifood.txt            # Material de referência, sem importação automática
├─ .env.example / .gitignore / .dockerignore
├─ package.json / package-lock.json
├─ Dockerfile / docker-compose.yml
├─ router.php
├─ install.sh / start.sh / stop.sh
├─ LICENSE
├─ README.md
└─ DOCUMENTACAO_SEO_PROJETO.md
```

`node_modules\` é dependência local de manutenção, não parte do runtime PHP. `dist\` e `netlify.toml` são saídas do exportador, não requisitos para iniciar a loja. Não há pacotes ZIP de publicação/entrega prontos fornecidos pelo projeto.

## Execução local com PHP (PowerShell)

Recomendado: **PHP 8.4**. A sintaxe usada exige pelo menos PHP 8.2; a verificação de PHP 8.0 do `install.sh` é insuficiente. Disponibilize `curl`, `mbstring`, `fileinfo`, `openssl` e PDO; para MySQL, também `pdo_mysql`. O processo PHP precisa escrever em `storage\` e `public\assets\images\`.

Na raiz do repositório:

```powershell
php --version
php -m
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

**Edite `.env` antes de iniciar.** O exemplo contém placeholders de domínio, chave, SMTP e pagamento, e não representa uma configuração local pronta. Para uma demonstração sem rede externa:

```dotenv
APP_URL=http://127.0.0.1:8128
APP_KEY=
DB_DSN=
MAIL_TRANSPORT=log
MERCADO_PAGO_ACCESS_TOKEN=
MERCADO_PAGO_WEBHOOK_SECRET=
```

Use uma base descartável. A ausência de `APP_KEY` gera `storage\app.key`; preserve-a para reutilizar esse ambiente. Com os tokens vazios, o catálogo pode ser explorado, mas não se criam nem consultam pagamentos reais.

```powershell
php -d post_max_size=5M -d upload_max_filesize=4M -S 127.0.0.1:8128 -t public router.php
```

Abra `http://127.0.0.1:8128`. O servidor embutido é para desenvolvimento. Encerre com Ctrl+C. Não é necessário `composer install` ou Node.js para servir os arquivos já presentes.

### Variáveis de configuração

O parser de `.env` em `bootstrap.php` usa `putenv` e **sobrescreve variáveis já existentes no processo**. `.env.example` não lista todas as opções. Não versione `.env`, credenciais ou dados de clientes.

| Variável | Uso / padrão do código |
| --- | --- |
| `APP_URL` | Base de URLs absolutas/canônicas; configure para o ambiente real |
| `APP_KEY` | Chave HMAC; se vazia, usa/gera `storage\app.key` |
| `APP_NODE` | Identificação do nó, incluindo `/health` |
| `DB_DSN` | Vazio: JSON. MySQL: por exemplo `mysql:host=127.0.0.1;dbname=doce_atelier;charset=utf8mb4` |
| `DB_USER`, `DB_PASSWORD` | Credenciais PDO; usuário padrão `doce` |
| `DB_ROOT_PASSWORD` | Usada pelo Compose para inicializar o MySQL, não pelo cliente PDO |
| `MERCADO_PAGO_ACCESS_TOKEN` | Necessário para criação/consulta de pagamentos |
| `MERCADO_PAGO_WEBHOOK_SECRET` | Obrigatório para aceitar notificações de pagamento |
| `MAIL_TRANSPORT` | `log` (padrão), `smtp` ou `mail` |
| `MAIL_FROM`, `MAIL_FROM_NAME` | Padrões `admin@elda-doces.com` e `Elda Bolos e Doces` |
| `MAIL_REPLY_TO` | Padrão `suporte@elda-doces.com` |
| `SMTP_HOST`, `SMTP_PORT` | Servidor SMTP; porta padrão 587 |
| `SMTP_ENCRYPTION` | `tls` (padrão), `ssl` ou `none` |
| `SMTP_USERNAME`, `SMTP_PASSWORD` | Autenticação SMTP |
| `GOOGLE_ANALYTICS_ID` | Padrão `G-ELDADOCES0`; não comprova coleta configurada |
| `GOOGLE_TAG_MANAGER_ID` ou `GTM_ID` | ID GTM; padrão `GTM-T98GCPGN` |

As rotas e assets assumem instalação na **raiz do domínio**, não em subdiretório. Para hosts externos, o front controller redireciona acessos HTTP ou `www` para **`https://elda-doces.com`**, independentemente de `APP_URL`; `localhost` e `127.0.0.1` são exceções. Apenas trocar `APP_URL` não torna o roteamento portátil para outro domínio: revise esse redirecionamento e a configuração do proxy.

## Docker Compose

O Compose define MySQL 8.4, dois nós PHP 8.4-FPM Alpine (`app1`/`app2`), Nginx 1.27 com `least_conn` e Mailpit 1.27. O Dockerfile habilita `pdo_mysql` e OPcache. A aplicação responde em **HTTP na porta 8080**; não há TLS configurado no Compose.

Após ajustar `.env` para esse ambiente (em particular `APP_URL=http://localhost:8080`, chave compartilhada e senhas do banco), os comandos são:

```powershell
docker compose config --quiet
docker compose up --build -d
docker compose ps
# Para parar sem remover volumes:
docker compose down
```

- Mailpit: interface `http://localhost:8025`; SMTP `mailpit:1025` na rede dos contêineres. A porta SMTP não é publicada no host.
- O Compose força `MAIL_TRANSPORT=smtp` e SMTP/Mailpit. Não repassa automaticamente todas as opções de remetente ou SMTP do host; para outro provedor, ajuste explicitamente o Compose.
- `.dockerignore` exclui `.env` e `node_modules`: o arquivo local não deve entrar na imagem nem sobrescrever o SMTP do Compose dentro do PHP.
- `mysql_data`, `php_sessions` e `php_runtime` persistem banco, sessões e estado compartilhado. Os logs HTML de `storage\mail\` não têm volume compartilhado nesse Compose.
- `product_images` é compartilhado pelos dois nós PHP com escrita e pelo Nginx somente para leitura (`nocopy: true` no Nginx). O Dockerfile atribui `storage` e as imagens a `www-data`. Um volume vazio é inicializado pelas imagens embarcadas no contêiner PHP; um volume existente **não recebe automaticamente novos assets da imagem**.
- Ao migrar uma instalação existente, preserve os uploads antigos e copie-os para o volume compartilhado com permissões adequadas. Faça backup antes. **`docker compose down -v` apaga os volumes e pode destruir dados.**

Os valores padrão de chave e senhas são para uso local e precisam ser substituídos. Este Compose não é uma implantação pronta para produção: faltam decisões operacionais de TLS, domínio, e-mail, credenciais, backups, monitoramento e acesso ao Mailpit. Validar a sintaxe com `config --quiet` não comprova que os contêineres iniciam ou que as integrações funcionam.

### Scripts Linux

`install.sh`, `start.sh` e `stop.sh` são scripts POSIX/Linux, não comandos PowerShell. Leia-os antes de usar:

- `install.sh` pode instalar pacotes e alterar o host via apt/dnf/apk; seu teste de versão PHP não garante compatibilidade com o código atual.
- `start.sh` usa o serviço systemd/cloudflared quando disponível; no caminho local, inicia dois servidores PHP nas portas 9001/9002 e Nginx na 8128. `docker\nginx-local.conf` contém caminhos fixos para `/home/t0x1n/projeto`, que precisam ser adaptados.
- `APP_PORT` afeta texto exibido pelo script, não reconfigura o listener. `CLOUDFLARE_TUNNEL=1` habilita o caminho opcional do túnel, dependente de instalação/credenciais; pode usar túnel nomeado `elda` ou temporário para 8128. O Compose não provisiona Cloudflare.
- `stop.sh` usa encerramento por padrões de processo e pode afetar outros serviços do host. Não o execute indiscriminadamente em ambiente compartilhado.

## Funcionalidades e limites

### Catálogo, carrinho e pedidos

- `/cardapio` aceita `categoria` e `busca`, processados no servidor. Não é busca instantânea por API.
- O carrinho fica na sessão e limita quantidades pelo estoque cadastrado. **Criar um pedido não decrementa o estoque automaticamente.**
- Frete fixo de **R$ 14,90** para subtotal positivo abaixo de R$ 150,00; grátis a partir de **R$ 150,00**, inclusive. Não há cálculo por transportadora ou distância.
- Checkout exige usuário autenticado e carrinho com itens. Pedido e API de status exigem o usuário dono do pedido; administração exige papel `admin`.
- O checkbox `save_address` só salva/atualiza o endereço quando enviado com valor `1`. Desmarcado, mantém o endereço anterior sem alterá-lo nem excluí-lo.
- O CPF informado é enviado no `payer` ao Mercado Pago, mas não integra o endereço salvo nem os dados de cliente dos novos pedidos. O formulário não preenche CPF antigo. **Isso não purga registros legados ou backups**, que podem ainda conter CPF.
- O status operacional (`received`, `preparing`, `shipping`, `delivered`, `cancelled`) é separado do status de pagamento. O painel mostra um retrato dos dados na renderização, não um dashboard em tempo real.

### PIX e webhook

`src\MercadoPagoGateway.php` cria cobranças PIX e consulta pagamentos. `isConfigured()` verifica apenas o token; não significa que o segredo do webhook esteja configurado.

O endpoint `POST /webhooks/mercado-pago` exige ID numérico de 1 a 30 dígitos (string ou inteiro), aceitando `data.id` da query normalizada pelo PHP como `data_id`, ou ID do corpo. Antes de consultar o pagamento, verifica `x-signature` e `x-request-id` com `MERCADO_PAGO_WEBHOOK_SECRET`.

- ID inválido: 400; assinatura inválida ou segredo vazio: 401.
- Falha na consulta ou incompatibilidade de referência/valor: 422.
- A assinatura HMAC usa ID, request ID e timestamp. **Não há janela de idade nem prevenção de replay implementada.**

A tela de pedido oferece QR Code, copia-e-cola e consulta de status a cada 3 segundos, recarregando ao receber `approved`. O contador visual não implementa cancelamento automático ou job de expiração. Integrações devem ser exercitadas com credenciais e dados de teste; esta documentação não afirma validação de pagamentos reais.

### Autenticação e e-mail

As senhas usam bcrypt com custo 12. Login/cadastro passam pelo desafio de seis dígitos, cujo HMAC fica na sessão: validade de 10 minutos, cinco tentativas, intervalo de 60 segundos para reenvio e limite de quatro reenvios. Recuperação de senha usa código de 12 caracteres, válido por 15 minutos, com estado em `storage\runtime\password-resets.json`.

`Mailer` com transporte `log` grava HTML, **não envia e-mail**. O modo `smtp` também registra HTML antes de tentar transmitir; a presença do log não prova entrega e uma falha SMTP não é um fallback bem-sucedido. O modo `mail` usa a função nativa do PHP.

Em desenvolvimento, `php bin\latest-2fa.php` lê o último e-mail registrado. Esse utilitário e os logs expõem códigos sensíveis: não os disponibilize por HTTP nem os use para contornar autenticação em produção.

O código inclui tokens CSRF nos fluxos de formulário e cabeçalhos de segurança, incluindo CSP. O webhook usa autenticação por assinatura, não token de formulário. O limitador por IP permite 2.000 requisições numa janela de 1 segundo e bloqueia por 300 segundos, **somente** em `/entrar`, `/criar-conta`, `/verificar-codigo` e `/verificar-codigo/reenviar`. Há ainda bloqueio de login por sessão após cinco falhas por 10 minutos. Isso não equivale a proteção geral contra DDoS nem a uma auditoria de segurança.

### Upload de produtos

Os formulários administrativos aceitam JPG/PNG com limite de **4 MiB por arquivo** (4.194.304 bytes). O front controller permite corpo de até **5 MiB** somente nos POST de criação/edição de produto, reservando espaço para multipart; outras requisições mantêm o limite de **2.000.000 bytes**. `docker\php.ini` define `upload_max_filesize=4M` e `post_max_size=5M`; os dois arquivos Nginx usam `client_max_body_size 5m`.

Ao mudar esses limites, mantenha PHP, Nginx, validação da aplicação e formulário coerentes. A gravação precisa funcionar em `public\assets\images\`, inclusive no volume compartilhado do Compose.

## Rotas

`{slug}`, `{uuid}`, `{numero}` e `{arquivo}` representam parâmetros. Rotas de leitura aceitam GET/HEAD; as permissões continuam sendo verificadas.

| Métodos | Rotas |
| --- | --- |
| GET/HEAD | `/`, `/cardapio`, `/produto/{slug}`, `/carrinho` |
| GET/HEAD | `/pedido/sucesso/{numero}`, `/api/pedidos/{numero}/status`, `/minha-conta` |
| GET/HEAD | `/admin`, `/admin/pedidos` |
| GET/HEAD | `/health`, `/status`, `/status/{codigo}` (400, 401, 403, 404, 429, 500, 503) |
| GET/HEAD | `/api/imagens/{arquivo}`, `/robots.txt`, `/sitemap.xml` e arquivos públicos |
| GET/HEAD e POST | `/entrar`, `/criar-conta`, `/verificar-codigo`, `/esqueci-senha`, `/redefinir-senha`, `/checkout` |
| GET/HEAD e POST | `/admin/produtos/novo`, `/admin/produtos/{uuid}/editar` |
| POST | `/carrinho/adicionar`, `/carrinho/atualizar`, `/carrinho/remover`, `/sair`, `/verificar-codigo/reenviar` |
| POST | `/admin/pedidos/{uuid}/status`, `/webhooks/mercado-pago` |

`/health` retorna `status`, `service`, `node` e `time` após o bootstrap. Não testa SMTP, Mercado Pago ou a saúde completa da infraestrutura. `/status` é um catálogo de telas HTTP, não um painel de monitoramento.

## Frontend e manutenção de assets

O layout carrega **`app.min.css` e `app.min.js`** com versionamento por data de modificação. As fontes editáveis são `public\assets\app.css` e `public\assets\app.js`.

CSS usa variáveis, Grid/Flexbox, media queries, foco visível e redução de movimento. As pilhas tipográficas usam fontes locais/de sistema; mencionar Inter na pilha não implica download dessa fonte. A fonte CSS unificada inclui os estilos de `save-address` e `password-toggle` usados pela interface.

Para modificar CSS, use Node.js/npm somente como ferramentas de manutenção:

```powershell
npm ci
npm run build:css
npm run check:css
```

`bin\build-css.cjs` usa `clean-css` 5.3.3, fixado em `package-lock.json`, e normaliza finais de linha para reprodução em Windows/Linux. Edite `app.css`, gere e mantenha `app.min.css` junto; `check:css` detecta divergência. `npm test` executa os testes de CSS em `tests\assets.test.cjs`. **Não há pipeline equivalente fornecido para JavaScript**: ao alterá-lo, mantenha a fonte e o arquivo servido coerentes.

O JavaScript nativo implementa:

- Prefetch de `/entrar`, `/criar-conta` e `/carrinho` por interação com links.
- Menu com `aria-expanded`, Escape e clique fora; sem transferência/retorno automático de foco.
- Toasts com fechamento e transição após 4,8 segundos; máscaras de telefone, CEP e CPF.
- Imagens por URLs diretas, com API Base64 como alternativa; `IntersectionObserver` com margem de 1.500 px e uso de `requestIdleCallback`. Não há cache em `sessionStorage`.
- Cópia PIX, contador, consulta de pagamento, foco no submit ao completar seis dígitos, envio de status administrativo, preview de upload e mostrar/ocultar senha.

Esses recursos e arquivos WebP não constituem medição de desempenho ou certificação WCAG. A home contém uma única nota numérica decorativa **4.9**, em elemento `aria-hidden`; não há integração de avaliações verificadas. O JSON-LD do layout usa `Bakery` e `WebSite`, sem `aggregateRating`; os produtos incluem `Product`/`Offer` e `BreadcrumbList`. O `SearchAction` ainda presente não garante caixa de busca nos resultados de pesquisa.

## Exportação estática: limites e cuidados

`bin\build-static.php` é um utilitário experimental, não um backend para Netlify. Ele depende de PHP/bootstrap, cURL, comandos de shell POSIX (`rm`, `cp`, `mkdir`) e de um servidor **já ativo em 127.0.0.1:8128**; não inicia o servidor. Não é um comando PowerShell portátil.

**A execução apaga `dist` e sobrescreve `netlify.toml`.** Além de capturar páginas HTTP, o script renderiza manualmente telas de administração, checkout, conta e 2FA usando dados do `Store` e identidades de demonstração. Isso pode publicar pedidos, métricas ou dados pessoais. Use exclusivamente uma base descartável e inspecione toda a saída antes de qualquer publicação.

A exportação não implementa autenticação, POST, carrinho persistente, pagamentos, API, webhook ou controle de acesso administrativo. Falhas HTTP podem deixar páginas ausentes sem abortar o processo. A cópia de assets não garante que GTM, manifest e ícones estejam completos; o catch-all `index.html 200` de `_redirects` pode produzir soft 404. Não há URL/deploy Netlify ou ZIP de entrega verificado por esta documentação.

## Validação e preparação para implantação

Com as dependências disponíveis, na raiz do projeto:

```powershell
npm run check:css
npm test
docker compose config --quiet
php tests\run.php
```

Os testes PHP precisam de `proc_open`, streams/sockets e `fileinfo`, além das extensões usadas pelo código. Usam servidor HTTP em loopback e fixtures isoladas: controlador, views e helpers selecionados são reais, mas bootstrap, persistência e serviços de rede são substituídos. Não usam banco, pagamento, correio ou dados pessoais reais.

Na validação desta revisão, passaram 75 testes PHP/449 asserções com PHP 8.4.27, lint dos arquivos PHP alterados, quatro testes Node e geração/verificação CSS. A configuração expandida do Compose confirmou os mounts compartilhados de imagens. **Docker/Nginx em execução, adaptadores de persistência reais e integrações externas não foram exercitados.** Esses resultados não substituem validação no ambiente de implantação.

Antes de uma implantação, valide em ambiente descartável: rotas e permissões; login/2FA/reset por e-mail de teste; checkbox de endereço e não persistência de CPF em novos registros; upload nos limites em ambos os nós; assinatura obrigatória, referência e valor do webhook com fixtures; consistência CSS; backup/restauração dos volumes. Configure TLS/proxy, domínio, chaves, contas iniciais, SMTP e credenciais de pagamento separadamente. Nunca use exportação estática ou dados reais como atalho para esses testes.

