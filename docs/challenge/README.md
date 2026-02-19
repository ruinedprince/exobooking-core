# Documentação do desafio técnico

Este diretório contém a documentação relacionada ao desafio técnico do **ExoBooking Core**.

## Conteúdo

| Arquivo | Descrição |
|---------|-----------|
| [Exo_Booking_Core.md](Exo_Booking_Core.md) | Requisitos completos do desafio técnico: funcionalidades, requisitos técnicos e critérios de entrega. |
| [Plano_de_Acao.md](Plano_de_Acao.md) | Plano de ação detalhado para implementação do plugin, organizado por etapas. |

## Resumo do desafio

Desenvolver um plugin WordPress que:

1. **Cria um CPT "Passeios"** e gerencia estoque de vagas por dia
2. **Expõe endpoint REST** para criar reservas com proteção contra overbooking (race conditions)
3. **Fornece painel admin** simples para listar reservas

O diferencial técnico é garantir que, mesmo com requisições simultâneas, o sistema nunca registre mais vendas do que a capacidade real (anti-overbooking).
