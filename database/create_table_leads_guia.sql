-- ============================================================
--  Tabela de leads do Guia de Licitacoes (isca / LP de ads)
--  Unyflex Digital - Lei 14.133/2021
--  Rode esta query no banco da aplicacao (ex.: unipublicabrasil3)
-- ============================================================

CREATE TABLE `leads_guia_licitacoes` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome`          VARCHAR(150)    NOT NULL,
  `email`         VARCHAR(150)    NOT NULL,
  `whatsapp`      VARCHAR(25)     NOT NULL,
  `cidade`        VARCHAR(120)    NOT NULL,
  `cargo`         VARCHAR(120)    NOT NULL,
  `origem`        VARCHAR(60)     NOT NULL DEFAULT 'lp-guia-licitacoes',
  `utm_source`    VARCHAR(120)    NULL DEFAULT NULL,
  `utm_medium`    VARCHAR(120)    NULL DEFAULT NULL,
  `utm_campaign`  VARCHAR(150)    NULL DEFAULT NULL,
  `utm_content`   VARCHAR(150)    NULL DEFAULT NULL,
  `utm_term`      VARCHAR(150)    NULL DEFAULT NULL,
  `contatado`     TINYINT(1)      NOT NULL DEFAULT 0,
  `contatado_em`  TIMESTAMP       NULL DEFAULT NULL,
  `baixou`        TINYINT(1)      NOT NULL DEFAULT 0,
  `baixado_em`    TIMESTAMP       NULL DEFAULT NULL,
  `observacoes`   TEXT            NULL DEFAULT NULL,
  `ip`            VARCHAR(45)     NULL DEFAULT NULL,
  `user_agent`    VARCHAR(255)    NULL DEFAULT NULL,
  `created_at`    TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_contatado` (`contatado`),
  KEY `idx_baixou` (`baixou`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
