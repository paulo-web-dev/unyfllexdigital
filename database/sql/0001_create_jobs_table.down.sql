-- 0001_create_jobs_table.down.sql
--
-- Reverte 0001_create_jobs_table.up.sql.
--
-- ATENCAO: derrubar `jobs` descarta os jobs ainda nao processados que estiverem
-- na tabela. Rodar so com a fila vazia (SELECT COUNT(*) FROM jobs = 0), ou
-- aceitando a perda conscientemente.

DROP TABLE IF EXISTS `jobs`;
