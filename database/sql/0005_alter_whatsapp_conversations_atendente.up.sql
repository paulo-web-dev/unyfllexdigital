-- 0005_alter_whatsapp_conversations_atendente.up.sql
--
-- Fatia 7 — atribuicao de atendente. Fecha a AUSENCIA DELIBERADA que o
-- cabecalho do 0003 registrou ("nenhuma coluna de atendente ... atribuicao e
-- nossa e nasce em outra fatia").
--
-- Aplicado manualmente. Sem migration: schema de negocio e SQL versionado
-- (regra de ouro 7).
--
-- A ATRIBUICAO E NOSSA, INTEIRA. `lead_assignedAttendant_id` da Uazapi
-- continua ignorado — nao lemos e nao escrevemos (regra de ouro 5). Estas
-- colunas nao tem contraparte no provedor.
--
-- SEM FOREIGN KEY para users, seguindo o 0003 (conversation_id tambem nao
-- tem). Consequencia assumida: usuario removido deixa id orfao. A tela
-- renderiza "atendente removido" em vez de quebrar — o custo de uma FK num
-- schema compartilhado que nao usa FK em lugar nenhum e maior que o do id
-- orfao.
--
-- Elegibilidade (power === 13, Comercial) NAO e imposta aqui. Coluna e int;
-- a regra vive em App\Enums\AdminRole e na validacao do controller. Um CHECK
-- congelaria em SQL uma regra de papel que hoje muda em PHP.

ALTER TABLE `whatsapp_conversations`
  -- NULL = nao atribuida. E o estado inicial de TODA conversa, e continua
  -- sendo o estado comum: a tela trata "Nao atribuida" como caminho normal.
  ADD COLUMN `atendente_id`     BIGINT UNSIGNED NULL AFTER `ultima_mensagem_em`,

  -- datetime(3) pelo mesmo motivo de ultima_mensagem_em na mesma tabela:
  -- coerencia de precisao dentro da linha.
  ADD COLUMN `atribuida_em`     DATETIME(3)     NULL AFTER `atendente_id`,

  -- Quem atribuiu != quem atende. Sem isto, reatribuicao fica anonima e
  -- ninguem consegue reconstruir como a conversa foi parar naquela carteira.
  ADD COLUMN `atribuida_por_id` BIGINT UNSIGNED NULL AFTER `atribuida_em`,

  -- O filtro "minhas conversas" e exatamente
  -- WHERE atendente_id = ? ORDER BY ultima_mensagem_em DESC.
  ADD KEY `wa_conv_atendente` (`atendente_id`, `ultima_mensagem_em`);
