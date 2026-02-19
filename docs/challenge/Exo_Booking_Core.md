Seu desafio é desenvolver um Plugin WordPress Instalável que resolva um problema crítico de concorrência (Overbooking).

# 🎯 O Desafio: "ExoBooking Core"

Você deve criar um plugin simples (arquivo .zip) que, ao ser ativado no WordPress, habilite as seguintes funcionalidades:

### 1. Estrutura do Plugin

O plugin deve criar automaticamente um Custom Post Type chamado "Passeios".

Deve criar uma tabela personalizada no banco de dados (ou usar meta fields) para armazenar o estoque de vagas por dia.

### 2. A Regra de Ouro (Proteção contra Overbooking)

O plugin deve expor um Endpoint de API (REST) para receber novas reservas.

Cenário de Teste: Imagine que um Passeio tem apenas 3 vagas disponíveis para o dia 20/03.

O Problema: Se dispararmos 5 requisições simultâneas (via Postman ou script) tentando comprar essas vagas ao mesmo tempo, o seu sistema deve aprovar as 3 primeiras e bloquear as outras 2 com erro, garantindo que o banco de dados nunca registre mais vendas do que a capacidade real.

Dica: Queremos ver como você lida com "Race Conditions" e travamento de banco de dados.

### 3. Painel Administrativo Simples

Uma tela simples no Admin do WordPress (wp-admin) que liste as reservas realizadas (ID, Cliente, Passeio, Status). Não precisa de design complexo, apenas funcionalidade.

## 🛠️ Requisitos Técnicos

Linguagem: PHP (sinta-se à vontade para usar padrões modernos).

Banco de Dados: MySQL/MariaDB (uso de $wpdb ou Eloquent se preferir, desde que funcione dentro do WP).

Diferencial (Não obrigatório): Entregar um docker-compose.yml que suba o ambiente pronto para teste.

## 📦 O Que Você Deve Entregar

Responda a este e-mail em até 48 horas com:

O arquivo .zip do plugin (ou link do repositório GitHub).

MUITO IMPORTANTE: Um vídeo curto (Loom ou YouTube não listado, máx 5 min) mostrando:

A instalação do plugin.

O teste de overbooking acontecendo na sua tela (mostre as requisições de erro acontecendo quando o limite de vagas estoura).

Estamos buscando código limpo, lógica segura e uma solução que funcione.

Boa sorte!