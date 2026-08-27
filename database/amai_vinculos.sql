-- ============================================================================
--  Estrutura AMAI — hierarquia de acessos (master / ponto focal / usuário)
--  Banco da aplicação (unipublicabrasil3). Rodar no phpMyAdmin, na ordem.
--  IDEMPOTENTE: pode rodar mais de uma vez sem duplicar (UNIQUE em user_id + INSERT IGNORE).
--  A aplicação funciona sem esta tabela (área /amai/* mostra "indisponível").
-- ============================================================================

-- PASSO 1 — tabela -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS amai_vinculos (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id         BIGINT UNSIGNED NOT NULL,                       -- users.id do membro
  papel           ENUM('master','ponto_focal','usuario') NOT NULL,
  municipio       VARCHAR(60)     NULL,                           -- ponto focal e seus usuários (NULL no master)
  parent_user_id  BIGINT UNSIGNED NULL,                           -- usuário -> users.id do ponto focal (NULL nos demais)
  cota            INT UNSIGNED    NULL,                           -- só ponto focal (NULL = padrão da aplicação, 14 vagas)
  created_by      BIGINT UNSIGNED NOT NULL,                       -- users.id de quem cadastrou
  removed_at      DATETIME        NULL,                           -- remoção lógica: libera vaga, preserva histórico
  removed_by      BIGINT UNSIGNED NULL,
  created_at      TIMESTAMP       NULL,
  updated_at      TIMESTAMP       NULL,
  UNIQUE KEY uq_amai_user   (user_id),                            -- um vínculo por usuário (remover = removed_at, recadastrar reativa)
  KEY        idx_amai_parent (parent_user_id, removed_at),
  KEY        idx_amai_papel  (papel, removed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conferir: tabela existe com 0 linhas.
-- SELECT COUNT(*) FROM amai_vinculos;

-- PASSO 2 — normaliza o plano legado ---------------------------------------
UPDATE subscriptions SET plano = 'AMAI' WHERE plano = 'ANUAL AMAI';

-- Conferir: 'ANUAL AMAI' deve sumir (15 linhas com plano = 'AMAI').
-- SELECT plano, COUNT(*) FROM subscriptions GROUP BY plano;

-- PASSO 3 — master (usuário com assinatura AMAI e e-mail do domínio da AMAI) -
INSERT IGNORE INTO amai_vinculos (user_id, papel, municipio, parent_user_id, cota, created_by, created_at, updated_at)
SELECT u.id, 'master', NULL, NULL, NULL, u.id, NOW(), NOW()
FROM users u
JOIN subscriptions s ON s.student_id = u.student_id
WHERE s.plano = 'AMAI'
  AND u.email LIKE '%@amaisc.org.br'
ORDER BY u.id
LIMIT 1;

-- Conferir: exatamente 1 linha com papel = 'master'.
-- SELECT id, user_id, papel FROM amai_vinculos WHERE papel = 'master';

-- PASSO 4 — pontos focais (uma pessoa por município, a partir das assinaturas AMAI) -
-- Município vem de students.city e precisa estar na lista da AMAI.
-- Vargeão duplicado (2 assinaturas do mesmo usuário) gera 1 vínculo só, Marema (sem assinatura) fica de fora.
SET @amai_master := (SELECT user_id FROM amai_vinculos WHERE papel = 'master' LIMIT 1);

INSERT IGNORE INTO amai_vinculos (user_id, papel, municipio, parent_user_id, cota, created_by, created_at, updated_at)
SELECT DISTINCT
  u.id,
  'ponto_focal',
  st.city,
  NULL,
  NULL,
  COALESCE(@amai_master, u.id),
  NOW(), NOW()
FROM subscriptions s
JOIN students st ON st.id = s.student_id
JOIN users    u  ON u.student_id = s.student_id
WHERE s.plano = 'AMAI'
  AND u.email NOT LIKE '%@amaisc.org.br'
  AND st.city IN ('Abelardo Luz','Bom Jesus','Entre Rios','Faxinal dos Guedes','Ipuaçu','Lajeado Grande','Marema',
                  'Ouro Verde','Passos Maia','Ponte Serrada','São Domingos','Vargeão','Xanxerê','Xaxim');

-- Conferir: 13 pontos focais (todos os municípios menos Marema), 1 por município, 1 master.
-- SELECT municipio, COUNT(*) FROM amai_vinculos WHERE papel = 'ponto_focal' GROUP BY municipio ORDER BY municipio;
-- SELECT papel, COUNT(*) FROM amai_vinculos GROUP BY papel;
