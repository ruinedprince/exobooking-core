# Base e regra de ouro
- Estrutura mínima do plugin (arquivo principal, cabeçalho do plugin, ativação/desativação).
- Criação do CPT “Passeios” (nome no singular, como pedido).
- Criação da tabela (ou meta) de estoque: algo como passeio_id, data, vagas_total, vagas_reservadas (ou vagas_disponiveis). Se usar meta, definir schema claro por passeio/dia.

- Endpoint REST para criar reserva:
    - Receber: identificador do passeio, data, dados do cliente (ex.: nome, e-mail).
    - Implementar a lógica anti-overbooking (transação + lock ou UPDATE condicional).
    - Respostas claras: 200 quando aprovar, 4xx quando negar (ex.: “sem vagas”).

- Tabela de reservas: se ainda não existir, garantir uma tabela (ou CPT “Reservas”) com ID, cliente, passeio, status, data da reserva, etc.
- Tela no admin: página em wp-admin listando reservas (ID, Cliente, Passeio, Status). Pode ser uma subpágina em “Passeios” ou um menu “ExoBooking” / “Reservas”. Usar WP_List_Table ou uma listagem simples com $wpdb.
- Revisão de código (nomes, comentários, segurança: nonce, permissões, sanitização).
- Empacotar o plugin em .zip (somente a pasta do plugin, sem .git).
- Gravar o vídeo (Loom/YouTube não listado): instalação do plugin + teste de overbooking na tela (mostrar as requisições que falham quando o limite estoura).
- docker-compose.yml com WordPress + MySQL para subir o ambiente e testar o plugin.