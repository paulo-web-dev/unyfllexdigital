-- 0005_alter_whatsapp_conversations_atendente.down.sql
--
-- REVERSAO DESTRUTIVA E IRRECUPERAVEL: derruba a atribuicao de atendente de
-- todas as conversas.
--
-- Diferente das mensagens, isto NAO se reconstroi a partir de
-- whatsapp_raw_events. A atribuicao e dado nosso, criado no painel; o
-- provedor nao sabe dela (ignoramos `lead_assignedAttendant_id` por decisao,
-- regra de ouro 5) e portanto nao ha de onde reimportar. Exportar antes se a
-- atribuicao ja estiver em uso.

ALTER TABLE `whatsapp_conversations`
  DROP KEY    `wa_conv_atendente`,
  DROP COLUMN `atribuida_por_id`,
  DROP COLUMN `atribuida_em`,
  DROP COLUMN `atendente_id`;
