-- 0004_alter_whatsapp_raw_events_erro.down.sql
--
-- Reverte 0004_alter_whatsapp_raw_events_erro.up.sql.
--
-- ATENCAO: descarta o registro de POR QUE cada payload falhou no
-- processamento. O payload cru em si permanece intacto — só o diagnóstico
-- se perde, e ele não se reconstrói sem reprocessar tudo.
--
-- CONSEQUÊNCIA OPERACIONAL de reverter: sem process_attempts, a varredura
-- volta a repescar indefinidamente todo payload que falha. Reverter isto
-- exige reverter também a condição de desistência em VarrerEventosCrus,
-- senão a query passa a referenciar coluna que não existe.

DROP INDEX `wa_raw_varredura` ON `whatsapp_raw_events`;

ALTER TABLE `whatsapp_raw_events`
  DROP COLUMN `process_attempts`,
  DROP COLUMN `process_error`;
