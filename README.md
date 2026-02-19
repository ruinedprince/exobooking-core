# ExoBooking Core

Plugin WordPress instalável que implementa um motor de reservas com **proteção contra overbooking** (concorrência de vagas).

## 📁 Estrutura do projeto

- **`docs/challenge/`** — Documentação do desafio técnico (requisitos e plano de ação)
- **`docs/management/`** — Gestão do projeto (Jira, classificação de issues)
- **`docs/`** — Documentação geral (versionamento, integração Jira)
- **`scripts/`** — Scripts auxiliares (CLI para Jira)

## 📚 Documentação

- **Versão:** definida em [`VERSION`](VERSION). Guia de versionamento: [`docs/versioning/VERSIONING.md`](docs/versioning/VERSIONING.md). Histórico: [CHANGELOG.md](CHANGELOG.md)
- **Desafio técnico:** [`docs/challenge/Exo_Booking_Core.md`](docs/challenge/Exo_Booking_Core.md)
- **Plano de ação:** [`docs/challenge/Plano_de_Acao.md`](docs/challenge/Plano_de_Acao.md)
- **Integração Jira:** [`docs/JIRA.md`](docs/JIRA.md)
- **Testar com Docker:** [`docs/docker-teste.md`](docs/docker-teste.md) — ambiente WordPress + MySQL e passo a passo (incluindo teste de overbooking)

## 🚀 Início rápido

1. Configure o ambiente (veja [`docs/challenge/Plano_de_Acao.md`](docs/challenge/Plano_de_Acao.md))
2. Para testar com Docker: `docker-compose up -d` e siga [`docs/docker-teste.md`](docs/docker-teste.md) (instalação do plugin, ativação e teste de overbooking).
3. Desenvolva o plugin seguindo os requisitos em [`docs/challenge/Exo_Booking_Core.md`](docs/challenge/Exo_Booking_Core.md)
4. Use o CLI Jira para criar issues: `python scripts/jira_cli.py create -p EBC ...` (veja [`docs/JIRA.md`](docs/JIRA.md))
