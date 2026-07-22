-- 0001_create_jobs_table.up.sql
--
-- Tabela `jobs` padrao do Laravel 10 (driver de fila `database`, ja configurado
-- em config/queue.php:41-47). Escrita a mao em SQL em vez de `artisan queue:table`
-- porque schema neste repo e SQL versionado, nao migration (regra de ouro 7).
--
-- `failed_jobs` NAO entra aqui: ja existe pela migration boilerplate
-- database/migrations/2019_08_19_000000_create_failed_jobs_table.php.
--
-- Aplicar manualmente. Par: 0001_create_jobs_table.down.sql

CREATE TABLE IF NOT EXISTS `jobs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue`        VARCHAR(255)    NOT NULL,
  `payload`      LONGTEXT        NOT NULL,
  `attempts`     TINYINT UNSIGNED NOT NULL,
  `reserved_at`  INT UNSIGNED    NULL,
  `available_at` INT UNSIGNED    NOT NULL,
  `created_at`   INT UNSIGNED    NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
