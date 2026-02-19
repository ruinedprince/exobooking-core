# ExoBooking Core

Plugin WordPress que implementa um motor de reservas com **proteção contra overbooking** (concorrência de vagas).

**Versão:** `0.7.0` | **Licença:** GPL-2.0+ | **Requisitos:** WordPress >= 5.0 · PHP >= 7.4 · MySQL 8.0

---

## Visão Geral

O ExoBooking Core resolve um problema clássico em sistemas de reservas: quando múltiplos usuários tentam reservar a última vaga simultaneamente, sistemas ingênuos registram mais reservas do que a capacidade real. Este plugin garante que isso nunca aconteça usando uma operação `UPDATE` condicional atômica no banco de dados — sem locks explícitos, sem filas, funcionando diretamente no nível do MySQL.

O plugin é autocontido e instalável em qualquer WordPress: basta copiar a pasta do plugin e ativá-lo. Na ativação, as tabelas customizadas são criadas automaticamente via `dbDelta`.

**Tecnologias:** PHP (padrões WordPress), MySQL, Docker (ambiente de desenvolvimento).

---

## Funcionalidades

- **CPT "Passeios"** — Custom Post Type com suporte a título, editor e imagem destacada. Aparece no menu admin com ícone de palmeira.
- **Estoque de vagas por data** — Metabox na tela de edição do passeio permite definir vagas totais para cada data via AJAX, sem interferir no formulário padrão do WordPress.
- **Endpoint REST de reservas** — `POST /wp-json/exobooking/v1/reservas` público. Recebe `passeio_id`, `data`, `nome` e `email`; valida os dados, reserva uma vaga atomicamente e registra a reserva.
- **Anti-overbooking atômico** — A reserva de vaga usa um único `UPDATE ... WHERE (vagas_total - vagas_reservadas) >= 1`. Se nenhuma linha for afetada, não há vaga disponível e a requisição retorna `409 Conflict`.
- **Painel admin de reservas** — Submenu "Reservas" em Passeios com listagem paginada (ID, cliente, e-mail, passeio, data, status e data de criação).
- **Status de reserva** — Cada reserva tem status `pendente`, `confirmada` ou `cancelada`, atualizável via `ExoBooking_Core_Reservas::atualizar_status()`.

---

## Requisitos

| Componente | Versão mínima |
|---|---|
| WordPress | 5.0 |
| PHP | 7.4 |
| MySQL | 8.0 |

---

## Instalação

### Ambiente de desenvolvimento com Docker

O `docker-compose.yml` sobe um WordPress isolado (porta `9080`) e um MySQL 8.0 (porta `3307`), montando a pasta `exobooking-core/` diretamente em `wp-content/plugins/exobooking-core` dentro do container — qualquer alteração local é refletida imediatamente, sem necessidade de rebuild.

```bash
# 1. Clone o repositório
git clone <url-do-repo>
cd Desafio_Tecnico_Planeta_EXO_O_Motor_de_Reservas_Plugin

# 2. (Opcional) Configure variáveis de ambiente para o CLI Jira
cp .env.example .env

# 3. Suba os containers
docker-compose up -d
```

Após os containers subirem:

1. Acesse `http://localhost:9080` e conclua a instalação do WordPress (crie usuário admin).
2. Vá em **Plugins → Plugins instalados** e ative o **ExoBooking Core**.
3. Na ativação, as tabelas `exobooking_estoque_vagas` e `exobooking_reservas` são criadas automaticamente.

Para parar os containers sem perder dados:

```bash
docker-compose down
```

Para destruir completamente (incluindo volumes do banco e WordPress):

```bash
docker-compose down -v
```

> Guia completo de teste (incluindo teste de overbooking com requisições simultâneas): [`docs/docker-teste.md`](docs/docker-teste.md)

### Instalação manual em WordPress existente

1. Copie a pasta `exobooking-core/` para o diretório `wp-content/plugins/` da sua instalação WordPress.
2. No painel admin, acesse **Plugins → Plugins instalados** e ative o **ExoBooking Core**.
3. As tabelas são criadas automaticamente na ativação.

Para desinstalar, desative e remova a pasta do plugin. As tabelas do banco **não são removidas** na desativação (padrão WordPress, para preservar dados em caso de reativação).

---

## Estrutura do Projeto

```
Desafio_Tecnico_Planeta_EXO_O_Motor_de_Reservas_Plugin/
├── README.md                          # Esta documentação
├── CHANGELOG.md                       # Histórico de versões
├── VERSION                            # Versão atual (fonte única)
├── JIRA.md                            # Atalho para docs/JIRA.md
├── .env.example                       # Variáveis de ambiente (modelo)
├── docker-compose.yml                 # Ambiente de desenvolvimento
├── exobooking-core/                   # Plugin WordPress instalável
│   ├── exobooking-core.php            # Bootstrap do plugin
│   └── includes/
│       ├── class-exobooking-core.php          # Orquestrador central
│       ├── class-activator.php                # Ativação e migrações
│       ├── class-deactivator.php              # Desativação
│       ├── class-cpt-passeios.php             # CPT Passeios
│       ├── class-estoque-vagas-schema.php     # DDL tabela de estoque
│       ├── class-estoque-vagas.php            # CRUD estoque + anti-overbooking
│       ├── class-reservas-schema.php          # DDL tabela de reservas
│       ├── class-reservas.php                 # CRUD reservas
│       ├── class-rest-reservas-controller.php # Endpoint REST POST /reservas
│       ├── class-admin-estoque-metabox.php    # Metabox admin + AJAX
│       └── class-admin-reservas.php           # Painel de listagem de reservas
│   └── assets/
│       └── js/
│           └── admin-estoque.js               # Script jQuery para metabox
├── docs/
│   ├── docker-teste.md                # Guia de teste com Docker
│   ├── JIRA.md                        # Guia de integração com Jira
│   ├── challenge/
│   │   ├── Exo_Booking_Core.md        # Requisitos do desafio técnico
│   │   └── Plano_de_Acao.md          # Plano de execução por etapas
│   ├── management/
│   │   └── classificacao-issues.md    # Guia de tipos de issue no Jira
│   └── versioning/
│       └── VERSIONING.md              # Guia de versionamento semântico
└── scripts/
    └── jira_cli.py                    # CLI Python para criar issues no Jira
```

### Descrição de cada arquivo

| Arquivo | Responsabilidade |
|---|---|
| `exobooking-core.php` | Bootstrap: define a constante de versão, registra os hooks de ativação/desativação e instancia `ExoBooking_Core` |
| `class-exobooking-core.php` | Orquestrador central: carrega todas as dependências, registra hooks admin e públicos, expõe rotas REST via `rest_api_init` |
| `class-activator.php` | Valida versões mínimas (WP e PHP), cria as tabelas via `dbDelta`, executa migrações e salva a versão instalada em `wp_options` |
| `class-deactivator.php` | Cancela cron jobs na desativação; preserva as tabelas intencionalmente para não perder dados |
| `class-cpt-passeios.php` | Registra o Custom Post Type `passeio` (slug interno) com labels em português, suporte a título, editor e thumbnail |
| `class-estoque-vagas-schema.php` | Define o DDL da tabela `{prefix}exobooking_estoque_vagas` com chave UNIQUE `(passeio_id, data)` |
| `class-estoque-vagas.php` | CRUD completo do estoque por passeio/data; `reservar_vaga()` executa o `UPDATE` condicional atômico que é o núcleo do anti-overbooking |
| `class-reservas-schema.php` | Define o DDL da tabela `{prefix}exobooking_reservas` com campo `status`; `maybe_upgrade()` adiciona colunas em instalações antigas |
| `class-reservas.php` | `criar()`, `get_todas()` (com paginação e JOIN para título do passeio) e `atualizar_status()` |
| `class-rest-reservas-controller.php` | Endpoint público `POST /wp-json/exobooking/v1/reservas`: valida todos os campos, chama `reservar_vaga()` e registra a reserva |
| `class-admin-estoque-metabox.php` | Metabox "Estoque de vagas" na tela do passeio; handler `wp_ajax` para salvar sem formulário aninhado |
| `class-admin-reservas.php` | Submenu "Reservas" sob Passeios; tabela paginada com 7 colunas e paginação via `paginate_links()` |
| `assets/js/admin-estoque.js` | Script jQuery: envia data + vagas via AJAX e atualiza a tabela de estoque dinamicamente sem recarregar a página |

---

## Banco de Dados

O plugin cria duas tabelas customizadas na ativação:

### `{prefix}exobooking_estoque_vagas`

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | PK |
| `passeio_id` | BIGINT UNSIGNED | ID do post (CPT passeio) |
| `data` | DATE | Data do passeio |
| `vagas_total` | INT UNSIGNED | Capacidade máxima para a data |
| `vagas_reservadas` | INT UNSIGNED | Vagas já ocupadas |

Constraint: `UNIQUE KEY (passeio_id, data)` — um único registro de estoque por passeio/data.

### `{prefix}exobooking_reservas`

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | PK |
| `passeio_id` | BIGINT UNSIGNED | ID do post (CPT passeio) |
| `data` | DATE | Data reservada |
| `nome_cliente` | VARCHAR(255) | Nome do cliente |
| `email_cliente` | VARCHAR(255) | E-mail do cliente |
| `status` | VARCHAR(20) | `pendente`, `confirmada` ou `cancelada` |
| `criado_em` | DATETIME | Timestamp de criação (DEFAULT CURRENT_TIMESTAMP) |

### Mecanismo anti-overbooking

A proteção contra race conditions está em `ExoBooking_Core_Estoque_Vagas::reservar_vaga()`:

```sql
UPDATE {prefix}exobooking_estoque_vagas
SET vagas_reservadas = vagas_reservadas + 1
WHERE passeio_id = %d
  AND data = %s
  AND (vagas_total - vagas_reservadas) >= 1
```

Se `$wpdb->rows_affected === 0`, não havia vaga disponível no momento exato da operação. O endpoint retorna `409 Conflict`. Esse padrão é seguro contra requisições concorrentes porque o MySQL garante atomicidade na execução do `UPDATE`.

---

## API REST

### `POST /wp-json/exobooking/v1/reservas`

Cria uma reserva. Endpoint público (não requer autenticação).

**Body (JSON):**

| Campo | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `passeio_id` | integer | Sim | ID do post CPT passeio (deve existir e não estar na lixeira) |
| `data` | string | Sim | Data no formato `YYYY-MM-DD` |
| `nome` | string | Sim | Nome do cliente |
| `email` | string | Sim | E-mail válido do cliente |

**Exemplo de requisição:**

```bash
curl -s -X POST http://localhost:9080/wp-json/exobooking/v1/reservas \
  -H "Content-Type: application/json" \
  -d '{"passeio_id": 1, "data": "2026-03-15", "nome": "Ana Silva", "email": "ana@exemplo.com"}'
```

**Resposta de sucesso (201 Created):**

```json
{
  "id": 42,
  "passeio_id": 1,
  "data": "2026-03-15",
  "nome": "Ana Silva",
  "email": "ana@exemplo.com",
  "status": "pendente"
}
```

**Respostas de erro:**

| Código | Motivo |
|---|---|
| `400` | Campo obrigatório ausente, e-mail inválido, data inválida ou passeio não encontrado |
| `409` | Sem vagas disponíveis para o passeio na data solicitada |
| `500` | Falha ao gravar a reserva no banco após a vaga ser reservada |

---

## Versionamento

A versão do projeto é definida no arquivo [`VERSION`](VERSION) na raiz e deve ser espelhada no cabeçalho do plugin (`exobooking-core.php`) e na constante `EXOBOOKING_CORE_VERSION`.

- **Guia de versionamento semântico:** [`docs/versioning/VERSIONING.md`](docs/versioning/VERSIONING.md)
- **Histórico de alterações:** [`CHANGELOG.md`](CHANGELOG.md)

---

## Integração com Jira

O projeto usa um CLI Python para criar issues no Jira do projeto `EBC` (ExoBookingCore).

**Configuração:**

```bash
cp .env.example .env
# Edite .env com suas credenciais Atlassian
```

```env
JIRA_SITE=seu-site.atlassian.net
JIRA_EMAIL=seu-email@exemplo.com
JIRA_API_TOKEN=seu_token_aqui
```

**Uso básico:**

```bash
python scripts/jira_cli.py create -p EBC -t "Título da issue" --type Task
```

Guia completo com todos os comandos e tipos de issue: [`docs/JIRA.md`](docs/JIRA.md)

---

## Documentação adicional

| Documento | Conteúdo |
|---|---|
| [`docs/docker-teste.md`](docs/docker-teste.md) | Passo a passo completo: configuração do Docker, instalação do plugin, criação de passeio, configuração de estoque e teste de overbooking com `curl` paralelo |
| [`docs/challenge/Exo_Booking_Core.md`](docs/challenge/Exo_Booking_Core.md) | Requisitos completos do desafio técnico: funcionalidades, critérios de aceite e entrega |
| [`docs/challenge/Plano_de_Acao.md`](docs/challenge/Plano_de_Acao.md) | Plano de execução por etapas com decisões técnicas documentadas |
| [`docs/versioning/VERSIONING.md`](docs/versioning/VERSIONING.md) | Regras de versionamento semântico (MAJOR / MINOR / PATCH) aplicadas ao projeto |
| [`docs/JIRA.md`](docs/JIRA.md) | Guia de integração com o Jira: configuração, comandos do CLI e fluxo de trabalho |
| [`docs/management/classificacao-issues.md`](docs/management/classificacao-issues.md) | Guia para escolher o tipo correto de issue no Jira (Tarefa, História, Bug, Epic, etc.) |
