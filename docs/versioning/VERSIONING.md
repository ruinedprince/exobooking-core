# Guia de versionamento semântico – ExoBooking Core

O projeto usa **versionamento semântico** no formato **x.y.z** (MAJOR.MINOR.PATCH). A versão atual está definida no arquivo **`VERSION`** na raiz do projeto e deve ser replicada no cabeçalho do plugin WordPress.

---

## Formato: x.y.z

| Parte   | Nome  | Quando incrementar |
|--------|--------|--------------------|
| **x**  | MAJOR  | Mudanças incompatíveis (quebram uso anterior). |
| **y**  | MINOR  | Novas funcionalidades compatíveis com versões anteriores. |
| **z**  | PATCH  | Correções de bugs e ajustes que não mudam comportamento público. |

---

## Regras por grau de alteração

### MAJOR (x.0.0) – "Quebra" de compatibilidade

Incremente **x** e zere **y** e **z** quando:

- A **API REST** mudar de forma que clientes existentes deixem de funcionar (ex.: remoção ou mudança de endpoint, alteração obrigatória de parâmetros ou formato de resposta).
- O **esquema do banco** (tabelas, colunas, meta keys) for alterado de forma incompatível sem migração automática.
- Comportamento **obrigatório** do plugin mudar de forma que sites que dependem do comportamento antigo quebrem (ex.: mudança de capability, remoção de shortcode ou de ação).
- Requisitos mínimos subirem de forma incompatível (ex.: PHP 8.0 obrigatório, WordPress 6.0 obrigatório) e isso impactar quem está na versão anterior.

**Exemplos:** `1.0.0` → `2.0.0`

---

### MINOR (x.y.0) – Novas funcionalidades

Incremente **y** e zere **z** quando:

- For adicionado um **novo endpoint** ou novo método na API (sem remover ou alterar os existentes).
- For adicionada uma **nova funcionalidade** no admin ou no front (ex.: novo relatório, filtro, exportação).
- For introduzido um **novo CPT**, nova tabela ou novo recurso que não altera o que já existe.
- Melhorias de **comportamento** que não quebram contratos atuais (ex.: novo parâmetro opcional na API).

**Exemplos:** `1.2.0` → `1.3.0`

---

### PATCH (x.y.z) – Correções e ajustes

Incremente **z** quando:

- Corrigir **bugs** (comportamento errado, overbooking, erros de validação, etc.).
- Ajustes de **segurança** (sanitização, nonce, permissões) sem mudar a "cara" da API ou do plugin.
- Pequenas **melhorias de código** (refatoração, performance) que não alteram funcionalidade visível.
- Ajustes em **textos**, traduções ou documentação interna.
- Correções de **compatibilidade** com versões específicas de WP/PHP sem mudar o contrato público.

**Exemplos:** `1.2.3` → `1.2.4`

---

## Resumo rápido

| Tipo de alteração | Incrementar |
|-------------------|-------------|
| API/contrato quebra para quem já usa | **MAJOR** (x) |
| Nova feature, novo endpoint, novo recurso | **MINOR** (y) |
| Bugfix, segurança, texto, refatoração invisível | **PATCH** (z) |

---

## Onde manter a versão

1. **Arquivo `VERSION`** (raiz do projeto) – fonte única da verdade para scripts e documentação.
2. **Cabeçalho do plugin** – no arquivo principal do plugin, linha `Version: x.y.z` (obrigatório para o WordPress).
3. **Releases no GitHub** – criar tag e release com o mesmo número (ex.: `v1.0.0`).
4. **CHANGELOG.md** (raiz) – registrar alterações por versão e data.

---

## Exemplo de fluxo

- Versão atual: `0.1.0` (desenvolvimento inicial).
- Primeira entrega estável do desafio: `1.0.0`.
- Depois você corrige um bug na reserva: `1.0.1`.
- Adiciona um novo endpoint de listagem: `1.1.0`.
- Muda o formato da API e quebra clientes antigos: `2.0.0`.

Sempre que alterar a versão, atualize o arquivo **`VERSION`** e o cabeçalho do plugin no mesmo commit (ou no commit da release).
