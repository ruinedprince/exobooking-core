# Changelog

Alterações notáveis do projeto ExoBooking Core. O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

---

## [0.7.0] - 2026-02-19

### Corrigido (EBC-7: revisão de código — nomes, comentários e segurança)

- **Bug duplo registro de hooks:** `ExoBooking_Core::run()` re-chamava `set_locale()`, `define_admin_hooks()` e `define_public_hooks()` que já haviam sido executados no `__construct()`, causando registro duplicado do CPT, das rotas REST e dos hooks de admin. O método `run()` foi simplificado para não re-executar as inicializações.
- **Guard WPINC ausente:** `class-activator.php` e `class-deactivator.php` não possuíam a verificação `if ( ! defined( 'WPINC' ) ) { die; }`, presente em todos os demais arquivos do plugin.
- **Typo no `@link`:** cabeçalho de `class-activator.php` referenciava `ruinedrprince` (errado); corrigido para `ruinedprince`.
- **HTTP 200 → 201:** endpoint REST `POST /wp-json/exobooking/v1/reservas` retornava `200 OK` para criação de recurso; corrigido para `201 Created` (padrão REST).

### Melhorado (EBC-7: segurança e boas práticas)

- **Capability check explícito:** `ExoBooking_Core_Admin_Reservas::render_page()` passou a verificar `current_user_can('edit_posts')` explicitamente (defesa em profundidade além do bloqueio do menu do WordPress).
- **Documentação de segurança:** `permission_callback => '__return_true'` no controller REST passou a ter comentário que documenta a intenção pública do endpoint.
- **PHPDoc descritivo:** métodos `activate()` e `deactivate()` tinham descrições placeholder do boilerplate; substituídos por documentação real do comportamento de cada método.
- **Comentário de segurança em SQL:** `ExoBooking_Core_Reservas::get_total()` recebeu comentário explicando por que a interpolação de `$table` é segura (derivada de `$wpdb->prefix`).

### Arquivos alterados (EBC-7)

| Ação     | Arquivo |
|----------|---------|
| Alterado | `exobooking-core/includes/class-activator.php` (guard WPINC, typo @link, PHPDoc) |
| Alterado | `exobooking-core/includes/class-deactivator.php` (guard WPINC, typo @link, PHPDoc) |
| Alterado | `exobooking-core/includes/class-exobooking-core.php` (fix duplo registro de hooks em run()) |
| Alterado | `exobooking-core/includes/class-rest-reservas-controller.php` (HTTP 201, PHPDoc permission_callback) |
| Alterado | `exobooking-core/includes/class-admin-reservas.php` (current_user_can em render_page) |
| Alterado | `exobooking-core/includes/class-reservas.php` (comentário de segurança em get_total) |
| Alterado | `exobooking-core/exobooking-core.php` (versão 0.7.0) |
| Alterado | `VERSION` |
| Alterado | `CHANGELOG.md` |

---

## [0.6.0] - 2025-02-19

### Adicionado (EBC-6: tabela/CPT de reservas)

- **Campo status na tabela de reservas:** valores `pendente`, `confirmada`, `cancelada`; coluna criada na ativação ou via upgrade em instalações existentes.
- **Schema:** `ExoBooking_Core_Reservas_Schema::maybe_upgrade()` adiciona a coluna `status` se não existir; constantes `COL_STATUS` e `STATUS_VALIDOS`.
- **Reservas:** `ExoBooking_Core_Reservas::criar()` grava `status = 'pendente'`; `get_todas( $args )` inclui `status` no SELECT e aceita filtro `'status'` em `$args`; `get_total( $args )` aceita filtro por status; novo método `atualizar_status( $reserva_id, $status )`.
- **REST:** resposta do POST de criação de reserva passa a incluir o campo `status` (ex.: `"pendente"`).
- **Admin Reservas:** coluna "Status" na listagem (Pendente / Confirmada / Cancelada); helper `format_status()` para rótulos traduzidos.

### Alterado

- **Activator:** após `create_table()` da tabela de reservas, chama `ExoBooking_Core_Reservas_Schema::maybe_upgrade()`.
- **Versão do plugin:** 0.5.0 → 0.6.0.

### Arquivos alterados (EBC-6)

| Ação     | Arquivo |
|----------|---------|
| Alterado | `exobooking-core/includes/class-reservas-schema.php` (coluna status, maybe_upgrade) |
| Alterado | `exobooking-core/includes/class-reservas.php` (criar com status, get_todas/get_total com status, atualizar_status) |
| Alterado | `exobooking-core/includes/class-rest-reservas-controller.php` (resposta com status) |
| Alterado | `exobooking-core/includes/class-admin-reservas.php` (coluna Status, format_status) |
| Alterado | `exobooking-core/includes/class-activator.php` (chamada maybe_upgrade) |
| Alterado | `exobooking-core/exobooking-core.php` (versão 0.6.0) |
| Alterado | `VERSION` |
| Alterado | `CHANGELOG.md` |

---

## [0.5.0] - 2025-02-19

### Adicionado

- **Interfaces de admin para uso por usuário real**
  - **Estoque de vagas na tela "Editar passeio":** metabox "Estoque de vagas" com formulário para informar data e vagas totais (Adicionar/Atualizar) e tabela com estoque por data (vagas totais, reservadas, disponíveis). Formulário enviado via `admin-post.php` para evitar form aninhado.
  - **Página "Reservas" no menu Passeios:** listagem de reservas com colunas ID, Cliente, E-mail, Passeio (link para editar), Data, Criado em; paginação quando houver mais de 20 itens.
- **Estoque:** método `get_estoque_por_passeio( $passeio_id )` em `ExoBooking_Core_Estoque_Vagas` para listar todas as datas com estoque de um passeio.
- **Reservas:** métodos `get_todas( $args )` e `get_total()` em `ExoBooking_Core_Reservas` para listagem e paginação no admin.

### Alterado

- **Core:** carregamento condicional das classes admin (`class-admin-estoque-metabox.php`, `class-admin-reservas.php`) quando `is_admin()`; hooks de metabox e submenu em `define_admin_hooks()`.
- **Versão do plugin:** 0.4.0 → 0.5.0.

### Arquivos criados/alterados (interfaces admin)

| Ação    | Arquivo |
|---------|---------|
| Criado  | `exobooking-core/includes/class-admin-estoque-metabox.php` |
| Criado  | `exobooking-core/includes/class-admin-reservas.php` |
| Alterado| `exobooking-core/includes/class-estoque-vagas.php` (`get_estoque_por_passeio`) |
| Alterado| `exobooking-core/includes/class-reservas.php` (`get_todas`, `get_total`) |
| Alterado| `exobooking-core/includes/class-exobooking-core.php` |
| Alterado| `exobooking-core/exobooking-core.php` (versão 0.5.0) |
| Alterado| `VERSION` |
| Alterado| `CHANGELOG.md` |

---

## [0.4.0] - 2025-02-19

### Adicionado

- **Endpoint REST para reservas com proteção anti-overbooking** (tarefa EBC-5)
  - POST `/wp-json/exobooking/v1/reservas`: cria reserva recebendo `passeio_id`, `data`, `nome`, `email`
  - Lógica anti-overbooking via UPDATE condicional atômico em `ExoBooking_Core_Estoque_Vagas::reservar_vaga()` (evita race conditions)
  - Tabela `{prefixo}_exobooking_reservas` (id, passeio_id, data, nome_cliente, email_cliente, criado_em) criada na ativação
  - Classe `ExoBooking_Core_Reservas_Schema` em `exobooking-core/includes/class-reservas-schema.php`
  - Classe `ExoBooking_Core_Reservas` em `exobooking-core/includes/class-reservas.php` (método `criar()`)
  - Controller REST `ExoBooking_Core_REST_Reservas_Controller` em `exobooking-core/includes/class-rest-reservas-controller.php`
  - Respostas: 200 (reserva criada), 400 (dados inválidos), 409 (sem vagas), 500 (erro ao registrar)

### Alterado

- **Estoque de vagas:** novo método `reservar_vaga( $passeio_id, $data )` com UPDATE condicional atômico
- **Activator:** criação da tabela de reservas na ativação
- **Core:** carregamento das classes de reservas e controller REST; hook `rest_api_init` para registrar rotas
- **Versão do plugin:** 0.3.0 → 0.4.0

### Arquivos criados/alterados (EBC-5)

| Ação    | Arquivo |
|---------|---------|
| Criado  | `exobooking-core/includes/class-reservas-schema.php` |
| Criado  | `exobooking-core/includes/class-reservas.php` |
| Criado  | `exobooking-core/includes/class-rest-reservas-controller.php` |
| Alterado| `exobooking-core/includes/class-activator.php` |
| Alterado| `exobooking-core/includes/class-estoque-vagas.php` |
| Alterado| `exobooking-core/includes/class-exobooking-core.php` |
| Alterado| `exobooking-core/exobooking-core.php` (versão 0.4.0) |
| Alterado| `VERSION` |
| Alterado| `CHANGELOG.md` |

---

## [0.3.0] - 2025-02-19

### Adicionado

- **Estoque de vagas por passeio e data** (tarefa EBC-4)
  - Tabela `{prefixo}_exobooking_estoque_vagas` (passeio_id, data, vagas_total, vagas_reservadas) criada na ativação via `dbDelta`
  - Classe `ExoBooking_Core_Estoque_Vagas_Schema` em `exobooking-core/includes/class-estoque-vagas-schema.php` (nome da tabela, SQL de criação/remoção)
  - Classe `ExoBooking_Core_Estoque_Vagas` em `exobooking-core/includes/class-estoque-vagas.php`: `get_estoque()`, `get_vagas_disponiveis()`, `set_vagas_totais()`, `incrementar_reservadas()`, `normalize_date()`
  - Integração no activator (criação da tabela) e no core (carregamento das classes); desativador documentado para não remover a tabela

### Alterado

- **Activator:** inclusão do schema de estoque e chamada a `ExoBooking_Core_Estoque_Vagas_Schema::create_table()` na ativação
- **Deactivator:** comentário documentando que a tabela de estoque não é removida na desativação
- **Core:** carregamento de `class-estoque-vagas-schema.php` e `class-estoque-vagas.php` em `load_dependencies()`
- **Docker:** exposição da porta 3306 do serviço `db` para acesso ao MySQL a partir do host

### Arquivos criados/alterados (EBC-4)

| Ação    | Arquivo |
|---------|---------|
| Criado  | `exobooking-core/includes/class-estoque-vagas-schema.php` |
| Criado  | `exobooking-core/includes/class-estoque-vagas.php` |
| Alterado| `exobooking-core/includes/class-activator.php` |
| Alterado| `exobooking-core/includes/class-deactivator.php` |
| Alterado| `exobooking-core/includes/class-exobooking-core.php` |
| Alterado| `docker-compose.yml` (porta 3306 no db) |

---

## [0.2.0] - 2026-02-19

### Adicionado

- **CPT Passeios** (tarefa EBC-3)
  - Classe `exobooking-core/includes/class-cpt-passeios.php` registrando o Custom Post Type "Passeios" (slug `passeio`)
  - Integração em `exobooking-core/includes/class-exobooking-core.php` (carregamento e hook `init`)
  - Labels em português (singular/plural concisos); suporte a título, editor e thumbnail; visível no menu do wp-admin

### Alterado

- CLI JIRA: tratamento de resposta vazia (204) no comando `transition` em `scripts/jira_cli.py`

### Arquivos criados/alterados (EBC-3)

| Ação    | Arquivo |
|---------|---------|
| Criado  | `exobooking-core/includes/class-cpt-passeios.php` |
| Alterado| `exobooking-core/includes/class-exobooking-core.php` |
| Alterado| `scripts/jira_cli.py` (fix 204 em transition) |

### Como testar (EBC-3)

1. **Ambiente:** Subir o WordPress com o plugin: `docker-compose up -d` (plugin montado em `wp-content/plugins/exobooking-core`).
2. **Ativação:** Em wp-admin → Plugins, ativar "ExoBooking Core".
3. **CPT no admin:** No menu lateral do wp-admin deve aparecer **"Passeios"**. Clicar para listar e usar "Adicionar novo" para criar um passeio.
4. **WP-CLI (opcional):** `docker compose exec wordpress wp post-type list` — o tipo `passeio` deve constar na lista.

---

## [0.1.0] - 2026-02-19

### Adicionado

- **Estrutura mínima do plugin WordPress** (tarefa EBC-2)
  - Arquivo principal `exobooking-core.php` com cabeçalho completo (Plugin Name, Description, Version, Author, License)
  - Hooks de ativação (`register_activation_hook`) e desativação (`register_deactivation_hook`)
  - Classe de ativação com verificação de requisitos mínimos (WordPress 5.0+, PHP 7.4+)
  - Classe de desativação e classe principal do plugin
  - Estrutura de diretórios: `exobooking-core/` e `exobooking-core/includes/`
- **Ambiente Docker** para desenvolvimento e testes
  - `docker-compose.yml` com WordPress e MySQL (porta 9080, volumes e rede isolados)
  - Montagem do plugin em `wp-content/plugins/exobooking-core`
- **Documentação de instalação**
  - `README_INSTALACAO.md` com opções: Docker, manual, FTP, ZIP
  - Scripts para criar ZIP do plugin: `scripts/criar-zip-plugin.ps1` e `scripts/criar-zip-plugin.sh`
- **Documentação de versionamento**
  - Diretório `docs/versioning/` com guia de versionamento semântico
  - Este CHANGELOG

### Alterado

- Organização da documentação de versionamento em `docs/versioning/`

---

[0.5.0]: https://github.com/ruinedprince/exobooking-core/releases/tag/v0.5.0
[0.4.0]: https://github.com/ruinedprince/exobooking-core/releases/tag/v0.4.0
[0.3.0]: https://github.com/ruinedprince/exobooking-core/releases/tag/v0.3.0
[0.2.0]: https://github.com/ruinedprince/exobooking-core/releases/tag/v0.2.0
[0.1.0]: https://github.com/ruinedprince/exobooking-core/releases/tag/v0.1.0
