-- 0004_alter_whatsapp_raw_events_erro.up.sql
--
-- Acrescenta rastreio de falha de processamento a whatsapp_raw_events.
--
-- POR QUE ISTO É NECESSÁRIO, e não polimento:
--
-- A varredura por cron (rede de segurança da regra de ouro 2) procura o que
-- tem processed_at NULL. Um payload que o parser não consegue entender
-- nunca ganha processed_at — e portanto é repescado a cada minuto, para
-- sempre, falhando sempre. Um loop silencioso que só aparece quando alguém
-- olha o log ou a carga do banco.
--
-- Com estas duas colunas a varredura tem como desistir (process_attempts <
-- N) e o motivo da falha fica legível no banco, sem depender de correlação
-- com log.
--
-- NÃO é violação da regra "colunas legadas não são alteradas": essa regra
-- protege schema compartilhado com o resto do sistema. whatsapp_raw_events
-- é tabela desta integração, criada em 22/07/2026 pelo 0002.
--
-- process_error é VARCHAR(500) e recebe SÓ a mensagem da exceção, nunca o
-- payload nem trecho de conversa (LGPD).

ALTER TABLE `whatsapp_raw_events`
  ADD COLUMN `process_error`    VARCHAR(500)     NULL     AFTER `processed_at`,
  ADD COLUMN `process_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `process_error`;

-- A varredura filtra por processed_at IS NULL AND process_attempts < N.
-- O índice de processed_at sozinho (criado no 0002) não cobre o segundo
-- predicado; este composto cobre os dois.
CREATE INDEX `wa_raw_varredura` ON `whatsapp_raw_events` (`processed_at`, `process_attempts`);
