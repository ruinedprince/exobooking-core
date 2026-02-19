# Changelog

Alterações notáveis do projeto ExoBooking Core. O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

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

[0.2.0]: https://github.com/ruinedprince/exobooking-core/releases/tag/v0.2.0
[0.1.0]: https://github.com/ruinedprince/exobooking-core/releases/tag/v0.1.0
