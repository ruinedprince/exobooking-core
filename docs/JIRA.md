# Integração Jira – CLI

Este projeto inclui um CLI em Python para **criar tarefas no Jira** automaticamente via linha de comando, usando a API REST do Jira Cloud.

## Pré-requisitos

- Python 3.8+
- Conta Atlassian (Jira Cloud) com um projeto criado

## Configuração

### 1. Instalar dependências

Na raiz do projeto:

```bash
pip install -r requirements-jira.txt
```

### 2. Configurar credenciais (nunca commitar o token)

1. Copie o arquivo de exemplo:
   ```bash
   copy .env.example .env
   ```
   (No Linux/macOS: `cp .env.example .env`)

2. Edite o arquivo **`.env`** e preencha:
   - **JIRA_SITE** – nome do seu site Jira (ex.: `minhaempresa.atlassian.net`, sem `https://`).
   - **JIRA_EMAIL** – e-mail da sua conta Atlassian.
   - **JIRA_API_TOKEN** – token de API. Gere em: [Atlassian – API tokens](https://id.atlassian.com/manage-profile/security/api-tokens).

O arquivo `.env` já está no `.gitignore` e **não deve ser commitado**.

## Uso

### Criar uma tarefa

```bash
python scripts/jira_cli.py create --project CHAVE_DO_PROJETO --summary "Título da tarefa"
```

Exemplo com descrição e tipo:

```bash
python scripts/jira_cli.py create -p EXO -s "Implementar endpoint de reservas" -d "Criar POST /wp-json/exobooking/v1/reservas" -t Task
```

**Parâmetros:**

| Parâmetro          | Atalho | Obrigatório | Descrição                                      |
|--------------------|--------|-------------|------------------------------------------------|
| `--project`        | `-p`   | Sim         | Chave do projeto (ex.: PROJ, EXO)             |
| `--summary`        | `-s`   | Sim         | Título da issue                                |
| `--description`    | `-d`   | Não         | Descrição (texto livre)                        |
| `--description-file` | Não  | Não         | Caminho para arquivo com a descrição           |
| `--type`           | `-t`   | Não         | Tipo da issue (padrão: Task). Ver [classificação](management/classificacao-issues.md). |
| `--assignee`       | `-a`   | Não         | Nome do responsável (ex.: Gabriel Maciel)      |
| `--start-date`     | Não    | Não         | Data de início YYYY-MM-DD (padrão: hoje)      |

### Exemplo de saída

```
Tarefa criada: EXO-42
URL: https://seu-site.atlassian.net/browse/EXO-42
```

## Variáveis de ambiente

| Variável         | Descrição                                      |
|------------------|------------------------------------------------|
| `JIRA_SITE`      | Domínio do Jira (ex.: `empresa.atlassian.net`) |
| `JIRA_EMAIL`     | E-mail da conta Atlassian                      |
| `JIRA_API_TOKEN` | Token de API (gerado no perfil Atlassian)      |

Podem ser definidas no arquivo `.env` (recomendado) ou exportadas no shell.

## Segurança

- **Nunca** commite o arquivo `.env` nem coloque o token no código.
- Use o **.env.example** apenas como modelo (sem valores reais).
- Se o token for exposto (ex.: em chat ou repositório), **revogue-o** em [Atlassian – API tokens](https://id.atlassian.com/manage-profile/security/api-tokens) e crie um novo.

## Documentação de gestão

- **Classificação de issues:** [management/classificacao-issues.md](management/classificacao-issues.md) — guia para escolher o tipo (Tarefa, História, Bug, Epic, Recurso, Função, Subtask) no projeto ExoBookingCore.

## Exemplo: primeira issue do Plano de Ação

Para registrar a issue “Base do plugin, CPT Passeios e endpoint anti-overbooking” (descrição em `management/issues/issue-01-base-regra-ouro.txt`), com assignee no projeto **EBC** (ExoBookingCore):

```bash
python scripts/jira_cli.py create -p EBC -s "Base do plugin, CPT Passeios e endpoint anti-overbooking" --description-file docs/management/issues/issue-01-base-regra-ouro.txt -t 10055 --assignee "Gabriel Maciel"
```

No projeto EBC o tipo “História” pode ser passado pelo ID `10055` (`-t 10055`). O campo *Start date* não existe no projeto; se o seu tiver, use `--start-date YYYY-MM-DD`.

## Referência da API

- [Jira Cloud REST API v3 – Create issue](https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-issues/#api-rest-api-3-issue-post)
