-- 0003_create_whatsapp_conversations_messages.up.sql
--
-- Fatia 3 — camada ESTRUTURADA da inbox Uazapi. O payload cru continua
-- sendo a autoridade (whatsapp_raw_events, 0002); estas duas tabelas são
-- derivadas dele e, em tese, reconstruíveis a partir do cru.
--
-- Aplicado manualmente. Sem migration: schema de negócio é SQL versionado
-- (regra de ouro 7).
--
-- DUAS AUSÊNCIAS DELIBERADAS, para que uma revisão futura não as leia como
-- esquecimento:
--
--   1. NENHUMA coluna de atendente. `lead_assignedAttendant_id` da Uazapi é
--      ignorado — não lemos e não escrevemos (regra de ouro 5). Atribuição
--      é nossa e nasce em outra fatia.
--   2. NENHUMA coluna de CRM (lead_id, student_id, nome do contato casado).
--      Matching é Fatia 4 e está travado até a Q8c fechar a cobertura em
--      linhas. A fronteira entre "ver" e "identificar" fica visível no
--      schema de propósito.

CREATE TABLE IF NOT EXISTS `whatsapp_conversations` (
  `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  -- Identificador do chat no provedor (ex.: 5511987654321@s.whatsapp.net
  -- ou ...@g.us). É a chave natural de deduplicação da conversa.
  `wa_chat_id`         VARCHAR(191)    NOT NULL,

  -- Telefone canônico: só dígitos, DDI 55, 12 ou 13 (ver CLAUDE.md).
  -- varchar(20) e NÃO char(13): a regra do repo é coluna nova nascer com
  -- folga. users.telefone é varchar(14) — o tamanho exato de
  -- '+5511987654321' — e 15,9% das suas linhas estão no teto, onde valor
  -- cheio e valor truncado são indistinguíveis. Não repetir isso.
  -- NULL em conversa de grupo, que não tem telefone único.
  `chat_phone`         VARCHAR(20)     NULL,

  -- Persistido SEMPRE, inclusive para grupo. O filtro de grupo é de
  -- EXIBIÇÃO (WHERE is_group = 0 na tela), nunca de ingestão — regra de
  -- ouro 8. Descartar grupo na entrada jogaria fora dado que não volta.
  `is_group`           TINYINT(1)      NOT NULL DEFAULT 0,

  `nome_exibicao`      VARCHAR(191)    NULL,
  `ultima_mensagem_em` DATETIME(3)     NULL,
  `created_at`         TIMESTAMP       NULL,
  `updated_at`         TIMESTAMP       NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wa_conv_chat_id` (`wa_chat_id`),
  KEY `wa_conv_phone` (`chat_phone`),
  -- Índice composto: a tela lista 1:1 ordenadas por recência, e é
  -- exatamente WHERE is_group = 0 ORDER BY ultima_mensagem_em DESC.
  KEY `wa_conv_listagem` (`is_group`, `ultima_mensagem_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `whatsapp_messages` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `conversation_id`     BIGINT UNSIGNED NOT NULL,

  -- body.message.id do provedor, formato owner:messageid.
  -- ÚNICO: é a idempotência de novo, agora na camada estruturada. O cru já
  -- deduplica na ingestão (0002), mas o processamento pode rodar duas vezes
  -- pelo desenho — afterResponse E varredura por cron podem pegar a mesma
  -- linha. Sem este índice, o reprocessamento duplicaria mensagem na tela.
  -- NOT NULL aqui, ao contrário do cru: evento sem message.id (presença,
  -- status de conexão) não vira mensagem, é descartado no parser.
  `provider_message_id` VARCHAR(191)    NOT NULL,

  `from_me`             TINYINT(1)      NOT NULL DEFAULT 0,
  `tipo`                VARCHAR(30)     NULL,
  `texto`               TEXT            NULL,

  -- datetime(3): a Uazapi manda timestamp em MILISSEGUNDOS. Truncar para
  -- segundo perderia a ordenação entre mensagens do mesmo segundo, que é
  -- justamente o caso de rajada numa conversa.
  `enviada_em`          DATETIME(3)     NULL,

  `created_at`          TIMESTAMP       NULL,
  `updated_at`          TIMESTAMP       NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wa_msg_provider_id` (`provider_message_id`),
  -- A thread é WHERE conversation_id = ? ORDER BY enviada_em.
  KEY `wa_msg_thread` (`conversation_id`, `enviada_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
