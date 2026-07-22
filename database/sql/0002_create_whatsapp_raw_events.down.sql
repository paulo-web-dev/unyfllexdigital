-- 0002_create_whatsapp_raw_events.down.sql
--
-- Reverte 0002_create_whatsapp_raw_events.up.sql.
--
-- ATENCAO: isto descarta o cru recebido, que e o unico registro do que a Uazapi
-- entregou. Nao ha de onde reconstruir — o provedor nao reenvia sob demanda.
-- Exportar antes se o conteudo importar.

DROP TABLE IF EXISTS `whatsapp_raw_events`;
