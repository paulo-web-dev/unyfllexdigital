-- 0002_create_whatsapp_raw_events.up.sql
--
-- Payload cru do webhook da Uazapi. Persistido SINCRONAMENTE antes da resposta
-- HTTP: e a unica parte da ingestao que nao pode falhar (regra de ouro 2).
-- Processamento estruturado vem depois, na Fatia 3.
--
-- Aplicar manualmente. Par: 0002_create_whatsapp_raw_events.down.sql

CREATE TABLE IF NOT EXISTS `whatsapp_raw_events` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  -- body.message.id, formato `owner:messageid`. Chave de deduplicacao.
  -- NULL de proposito: no MySQL um indice unico aceita varios NULL, entao
  -- eventos sem message.id (presenca, status de conexao) entram todos sem
  -- colidir entre si — e continuam sendo persistidos, nao descartados.
  `message_id`   VARCHAR(191) NULL,

  -- Qual instancia entregou. Serve de prova documental de que o trafego veio
  -- da instancia de teste, e nao da de producao.
  `instance`     VARCHAR(100) NULL,
  `event_type`   VARCHAR(50)  NULL,

  -- JSON cru integral. LONGTEXT e nao JSON: nada consulta dentro do payload
  -- nesta fatia, e LONGTEXT nao depende da versao do servidor.
  `payload`      LONGTEXT     NOT NULL,

  `received_at`  DATETIME(3)  NOT NULL,

  -- Ja nasce aqui, indexado, mesmo sem uso nesta fatia: e por onde a varredura
  -- por cron da Fatia 3 (rede de seguranca) acha o que nao foi processado.
  `processed_at` DATETIME     NULL,

  `created_at`   TIMESTAMP    NULL,
  `updated_at`   TIMESTAMP    NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `wa_raw_message_id` (`message_id`),
  KEY `wa_raw_processed_at` (`processed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
