# Classificação de issues – ExoBookingCore (Jira)

No projeto **ExoBookingCore** existem **7 tipos de issue**. Use este guia para classificar corretamente cada item ao criar ou editar tarefas no Jira.

---

## Tipos disponíveis

| Tipo (nome no Jira) | Quando usar | Exemplo |
|---------------------|-------------|---------|
| **Tarefa** | Trabalho concreto e delimitado, sem foco em “valor para usuário” contado em história. Implementação técnica, configuração, revisão. | “Criar CPT Passeios no plugin”; “Adicionar nonce no formulário de reserva”. |
| **Função** | Capacidade ou comportamento do sistema do ponto de vista do usuário/negócio (feature). Pode ser implementada por várias tarefas/histórias. | “Sistema permite reservar vagas por data”; “Admin visualiza lista de reservas”. |
| **História** | Entrega de valor para o usuário/negócio em formato “Como [quem], quero [o quê] para [porquê]”. Conjunto de trabalho que gera algo utilizável. | “Como operador, quero listar reservas no admin para conferir vendas”. |
| **Bug** | Comportamento incorreto ou defeito: algo que deveria funcionar e não funciona, ou que funciona de forma errada. | “Endpoint retorna 200 ao estourar vagas”; “Tabela de reservas não aparece no admin”. |
| **Epic** | Iniciativa ou tema grande que agrupa várias histórias/funções. Escopo amplo, múltiplas entregas. | “ExoBooking Core – plugin completo de reservas com anti-overbooking”. |
| **Recurso** | Melhoria ou capacidade nova do produto (mais voltado a produto/roadmap que a uma tarefa técnica isolada). | “Relatório de ocupação por passeio”; “Notificação por e-mail ao confirmar reserva”. |
| **Subtask** | Parte executável de uma issue pai (Tarefa, História, Bug, etc.). Não deve ser usada como issue independente. | “Implementar registro do CPT” (subtask de “Criar CPT Passeios”). |

---

## Fluxo de decisão rápida

1. **É um defeito (algo quebrado)?** → **Bug**
2. **É um pedaço de uma issue já existente?** → **Subtask** (e vincule à issue pai)
3. **É um tema grande que agrupa várias entregas?** → **Epic**
4. **É uma capacidade do sistema para o usuário/negócio (feature)?** → **Função** ou **Recurso** (Recurso se for melhoria de produto)
5. **É uma entrega em formato “como X quero Y para Z”?** → **História**
6. **É trabalho técnico concreto e delimitado (implementação, config, revisão)?** → **Tarefa**

---

## Exemplos no contexto ExoBooking Core

| Situação | Tipo sugerido |
|----------|----------------|
| Estrutura do plugin + CPT Passeios + estoque + endpoint anti-overbooking + tabela reservas + tela admin (conjunto do Plano de Ação “Base e regra de ouro”) | **História** |
| Apenas “criar o endpoint REST de reservas” | **Tarefa** |
| “Garantir que 5 requisições simultâneas para 3 vagas aprovem 3 e neguem 2” | **Tarefa** ou **Bug** (se for correção de comportamento errado) |
| “Plugin ExoBooking Core – desafio técnico completo” | **Epic** |
| “Listar reservas no wp-admin” | **Tarefa** ou **História** (História se for descrito como valor para o operador) |
| “Adicionar chaves de tradução faltantes” | **Tarefa** ou **Bug** (Bug se for correção de algo que já deveria existir) |

---

## Resumo

- **Bug** = algo errado a corrigir  
- **Subtask** = parte de outra issue  
- **Epic** = tema grande / iniciativa  
- **História** = valor para usuário (“como X quero Y”)  
- **Função / Recurso** = capacidade ou melhoria de produto  
- **Tarefa** = trabalho técnico concreto e delimitado  

Use o tipo que melhor comunica o objetivo da issue para quem for executar ou revisar.
