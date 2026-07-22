-- 0003_create_whatsapp_conversations_messages.down.sql
--
-- Reverte 0003_create_whatsapp_conversations_messages.up.sql.
--
-- PERDA REVERSÍVEL, ao contrário do 0002. Estas duas tabelas são derivadas
-- de whatsapp_raw_events: reverter descarta a camada estruturada, mas o cru
-- permanece e pode reconstruí-la (zerar processed_at e deixar a varredura
-- reprocessar).
--
-- A ressalva: só é reconstruível o que o cru ainda contém. Se o 0002 tiver
-- sido revertido antes, ou se algum cru tiver sido expurgado, o que sumir
-- daqui não volta.
--
-- Ordem importa: messages referencia conversation_id.

DROP TABLE IF EXISTS `whatsapp_messages`;
DROP TABLE IF EXISTS `whatsapp_conversations`;
