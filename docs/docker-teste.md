# Testar o plugin com Docker

Este guia descreve como subir o ambiente WordPress + MySQL com Docker e validar o plugin ExoBooking Core, incluindo o **teste de overbooking**.

## 1. Subir o ambiente

Na raiz do projeto:

```bash
docker-compose up -d
```

- WordPress fica em **http://localhost:9080**
- O plugin está montado em `wp-content/plugins/exobooking-core` (não é preciso instalar manualmente)
- MySQL (serviço `db`) escuta na porta **3306** dentro da rede; no host, a porta exposta é **3307** (evita conflito com outros projetos na 3306)

## 2. Configurar o WordPress (primeira vez)

1. Acesse http://localhost:9080 e conclua o assistente de instalação (título do site, usuário admin, senha, e-mail).
2. Faça login no wp-admin (http://localhost:9080/wp-admin).

## 3. Ativar o plugin

Em **Plugins** → liste os plugins instalados e ative **ExoBooking Core**.

## 4. Criar um passeio e definir estoque

1. No menu lateral, abra **Passeios** → **Adicionar novo**.
2. Crie um passeio (ex.: título "Passeio teste") e publique.
3. Na metabox **Estoque de vagas**, informe uma **data** (ex.: amanhã, no formato YYYY-MM-DD) e a quantidade de **vagas totais** (ex.: 2). Clique em **Adicionar** ou **Atualizar**.
4. Anote o **ID do passeio** (visível na URL ao editar o post, ex.: `post=123`) para usar no teste da API.

## 5. Teste de overbooking

O plugin protege contra overbooking: não permite mais reservas do que as vagas disponíveis, mesmo com requisições simultâneas.

**Passos:**

1. Com **N vagas** definidas para um passeio em uma data (ex.: N = 2), faça **N+1** requisições **POST** simultâneas para o endpoint de reservas, todas com o mesmo `passeio_id` e a mesma `data`.

   **Exemplo de corpo (JSON):**

   ```json
   {
     "passeio_id": 123,
     "data": "2026-02-20",
     "nome": "Cliente Teste",
     "email": "teste@exemplo.com"
   }
   ```

   **URL:** `POST http://localhost:9080/wp-json/exobooking/v1/reservas`

2. **Resultado esperado:**
   - **N** respostas **201 Created** (reservas criadas)
   - **1** resposta **409 Conflict** com mensagem indicando que não há vagas disponíveis

**Exemplo com curl (3 requisições para 2 vagas):**

```bash
# Substitua 123 e 2026-02-20 pelo ID do passeio e pela data configurados.
for i in 1 2 3; do
  curl -s -o /dev/null -w "%{http_code}\n" -X POST http://localhost:9080/wp-json/exobooking/v1/reservas \
    -H "Content-Type: application/json" \
    -d "{\"passeio_id\":123,\"data\":\"2026-02-20\",\"nome\":\"Cliente $i\",\"email\":\"cliente$i@exemplo.com\"}" &
done
wait
# Esperado: duas linhas 201 e uma 409 (a ordem pode variar).
```

## 6. Verificações opcionais (WP-CLI)

Com os containers em execução:

```bash
docker compose exec wordpress wp plugin list
# ExoBooking Core deve aparecer na lista (ativo).

docker compose exec wordpress wp post-type list
# O tipo "passeio" deve constar na lista.
```

## Resumo

| Etapa              | Ação |
|--------------------|------|
| Subir ambiente     | `docker-compose up -d` |
| WordPress          | http://localhost:9080 |
| Ativar plugin      | wp-admin → Plugins → ExoBooking Core |
| Estoque            | Editar passeio → metabox "Estoque de vagas" |
| Teste overbooking  | N+1 POSTs simultâneos → N×201 e 1×409 |
