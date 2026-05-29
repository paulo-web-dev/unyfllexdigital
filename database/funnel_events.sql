-- ══════════════════════════════════════════════════════════════════════
-- Cole no phpMyAdmin → SQL
-- Tabela de eventos do funil de conversão
-- ══════════════════════════════════════════════════════════════════════

CREATE TABLE `funnel_events` (
  `id`          bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id`  varchar(64)  NOT NULL COMMENT 'ID único do visitante (cookie)',
  `etapa`       varchar(50)  NOT NULL COMMENT 'visita|visualizou|carrinho|checkout|pagamento|converteu',
  `origem`      varchar(20)  NOT NULL DEFAULT 'organico' COMMENT 'referral|organico',
  `referral`    varchar(100) DEFAULT NULL COMMENT 'token do vendedor se veio por referral',
  `classes_id`  int UNSIGNED DEFAULT NULL COMMENT 'minisérie envolvida (quando aplicável)',
  `ip`          varchar(45)  DEFAULT NULL,
  `cidade`      varchar(100) DEFAULT NULL,
  `estado`      varchar(100) DEFAULT NULL,
  `pais`        varchar(10)  DEFAULT NULL,
  `user_agent`  varchar(255) DEFAULT NULL,
  `created_at`  timestamp    NULL DEFAULT NULL,
  `updated_at`  timestamp    NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `funnel_session`  (`session_id`),
  KEY `funnel_etapa`    (`etapa`),
  KEY `funnel_origem`   (`origem`),
  KEY `funnel_referral` (`referral`),
  KEY `funnel_classes`  (`classes_id`),
  KEY `funnel_created`  (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
