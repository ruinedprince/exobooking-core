# Gestão do projeto (Jira e documentação)

Este diretório reúne documentação de gestão e integração com o Jira (projeto **ExoBookingCore**).

## Conteúdo

| Documento | Descrição |
|-----------|-----------|
| [Classificação de issues](classificacao-issues.md) | Guia para escolher o tipo correto de issue (Tarefa, História, Bug, Epic, etc.) ao criar itens no Jira. |

## Modelos de issues

Na pasta **issues/** ficam os textos de descrição usados para criar issues no Jira (por exemplo via `--description-file` no CLI):

- `issue-01-base-regra-ouro.txt` — Base do plugin, CPT Passeios e endpoint anti-overbooking (primeira etapa do Plano de Ação).

## Uso do CLI Jira

O CLI para criar issues está em `scripts/jira_cli.py`. Consulte o [JIRA.md](../JIRA.md) para configuração e uso.
