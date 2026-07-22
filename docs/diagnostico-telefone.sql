-- =====================================================================
-- Fatia 0 — Diagnóstico de normalização de telefone
-- =====================================================================
--
-- SOMENTE LEITURA. Este arquivo não contém INSERT, UPDATE, DELETE, DROP,
-- ALTER, CREATE nem TRUNCATE. Não altera schema nem dado.
--
-- SCHEMA — `unipublicabrasil3`, CONFIRMADO COMO PRODUÇÃO
--   Confirmado com o Paulo em 21/07/2026. Existem outros 2 schemas com
--   as mesmas tabelas (unipublicabrasil4, unipublicabrasil5); nenhum
--   dos dois é produção. Este script foi escrito contra o 3, e as
--   collations abaixo foram lidas dele.
--
--   VERIFICAÇÃO EMBUTIDA: em unipublicabrasil3 a tabela `leads` está
--   VAZIA (COUNT(*) = 0, confirmado na mesma data).
--
--   ESPERADO: NENHUMA linha 'leads.celular' em Q1, Q2 e Q3. A tabela não
--   tem linha, o ramo do UNION ALL não contribui nada, e GROUP BY coluna
--   não cria grupo para entrada que não existe. O rótulo simplesmente
--   não aparece. Isso é o resultado correto, não falha de execução.
--
--   ALARME: se a linha 'leads.celular' APARECER, `leads` tem linhas.
--   Duas causas, nenhuma delas permite seguir:
--     (a) o script rodou fora de unipublicabrasil3 — todo o resto do
--         relatório é de outro banco;
--     (b) a tabela foi repopulada depois de 21/07/2026 — o reescopo que
--         tirou `leads` do matching precisa ser revisto.
--   Nos dois casos o relatório PARA até confirmação.
--
--   CUIDADO AO LER QUALQUER TABELA VAZIA NESTE ARQUIVO — os dois
--   comportamentos convivem aqui, e confundi-los já custou tempo:
--     * GROUP BY sobre zero linhas NÃO produz grupo: a linha some.
--       (Q1, Q2, Q3, Q4b, Q6, Q8, Q8b, Q8c)
--     * Agregado escalar sem GROUP BY sobre tabela vazia produz UMA
--       linha de zeros.  (Q4, Q5, Q7)
--   Ausência de linha e linha zerada não são o mesmo sinal, e qual dos
--   dois esperar depende da query. Vale para contact e corporativos
--   também, se alguma delas se revelar vazia: Q4 mostraria a tabela,
--   Q4b a omitiria — mesmo dado, saídas opostas.
--
--   `leads` segue nas queries só por isso: como verificação, custo zero.
--   Ela NÃO é mais fonte de matching — ver "Tabelas vazias em produção"
--   no CLAUDE.md.
--
-- LGPD — garantias no desenho, não na disciplina:
--   * Nenhuma query projeta telefone, nome, e-mail ou id de pessoa.
--     Toda saída é COUNT/GROUP BY sobre classes.
--   * Sem MIN/MAX em coluna de telefone (devolveria um número real).
--   * Ressalva honesta: em Q6, Q7, Q8, Q8b e Q8c os telefones normalizados transitam
--     dentro do motor, numa subquery, para poderem ser agrupados. Eles
--     NÃO aparecem no resultado. É inevitável para contar sobreposição.
--   * Rodar com usuário de banco somente-leitura, se existir.
--
-- ESCOPO — o da Fatia 0, sem ampliar. Funil comercial vivo:
--   students.phone, negociacoes_comercial.whatsapp,
--   leads_guia_licitacoes.whatsapp, prematricula.celular, users.telefone
--   + leads.celular, que NÃO é mais fonte de matching (tabela vazia) e
--   permanece só como verificação de schema — ver bloco SCHEMA acima
--   + as duas colunas numéricas em avaliação:
--   contact.telefone (int), corporativos.telefone (bigint)
--   As duas numéricas são medidas em Q4 e Q4b, e ficam FORA das uniões
--   de Q6, Q7 e Q8. Exclusão intencional — ver a nota acima de Q6.
--   (Q8b é só negociacoes_comercial, por desenho.)
--
-- FORA DO ESCOPO (backups e importações — não consultar):
--   students2, studentsbkp, users2, ativos_parana, ativos_saoPaulo,
--   ativos_santaCatarina, e as demais 18 colunas de telefone do banco.
--
-- COMPATIBILIDADE: MySQL 5.7 e 8.0. Sem CTE e sem REGEXP_REPLACE (que só
-- existe no 8.0). Limpeza por cadeia de REPLACE; validação pelo operador
-- REGEXP, presente nas duas versões.
--
-- COLLATION — as 6 colunas varchar do escopo NÃO compartilham collation
-- em unipublicabrasil3:
--   utf8mb4_unicode_ci   → students.phone, users.telefone,
--                          leads_guia_licitacoes.whatsapp
--   utf8mb4_0900_ai_ci   → leads.celular, negociacoes_comercial.whatsapp,
--                          prematricula.celular
-- Duas collations explícitas de colunas diferentes num UNION dão
-- "Illegal mix of collations" (erro 1271): sem tratamento, o script não
-- roda. Por isso cada expressão computada das sub-SELECTs dos UNION é
-- envolvida em CONVERT(... USING utf8mb4) COLLATE utf8mb4_unicode_ci,
-- fixando um padrão comum.
-- Alvo utf8mb4_unicode_ci porque já vale na maioria das colunas e existe
-- nas duas versões — utf8mb4_0900_ai_ci só existe no 8.0, e este arquivo
-- promete rodar também no 5.7. Para dígitos ASCII as duas ordenam igual:
-- o alvo não muda resultado nenhum, muda só a resolubilidade.
-- Isto é normalização NA LEITURA. Nenhuma coluna é alterada — não há
-- ALTER TABLE ... COLLATE aqui, e não deve haver.
--
-- CUSTO: são varreduras completas. Rodar fora do horário de pico. Passar
-- EXPLAIN antes em qualquer tabela grande. Se alguma se mostrar pesada
-- demais, decidir amostragem COM o número na mão — não por precaução.
--
-- NOTA: o bloco UNION ALL se repete entre as queries de propósito. A
-- alternativa seria CREATE TEMPORARY TABLE, que tornaria o arquivo não
-- estritamente read-only. Preferimos a repetição.
--
-- Expressões-base usadas ao longo do arquivo:
--   d      = coluna com separadores removidos (espaço - ( ) . + /)
--   canon  = forma canônica de 12 ou 13 dígitos, ou '' se não classificável
--            (só dígitos, sem '+', DDI 55 presente — ver CLAUDE.md)
-- =====================================================================


-- ---------------------------------------------------------------------
-- Q1 — Inventário por coluna
-- Saída: coluna, total, nulos, vazios, com_nao_digito
-- Nota: orig é convertida SEM COALESCE, de propósito. CONVERT(NULL ...)
-- continua NULL, então SUM(orig IS NULL) segue medindo o que media;
-- trocar por COALESCE aqui zeraria a contagem de nulos.
-- Nota: NÃO espere uma linha 'leads.celular' aqui — a tabela está vazia
-- e o GROUP BY não gera grupo sem entrada. Ausência é o esperado;
-- presença é alarme. Ver VERIFICAÇÃO EMBUTIDA no cabeçalho.
-- ---------------------------------------------------------------------
SELECT coluna,
       COUNT(*)                                            AS total,
       SUM(orig IS NULL)                                   AS nulos,
       SUM(orig IS NOT NULL AND TRIM(orig) = '')           AS vazios,
       SUM(d <> '' AND d NOT REGEXP '^[0-9]+$')            AS com_nao_digito
FROM (
  SELECT CONVERT('leads.celular' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS coluna,
         CONVERT(celular USING utf8mb4) COLLATE utf8mb4_unicode_ci         AS orig,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci                       AS d
  FROM leads
  UNION ALL
  SELECT CONVERT('students.phone' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(phone USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(phone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM students
  UNION ALL
  SELECT CONVERT('negociacoes_comercial.whatsapp' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(whatsapp USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM negociacoes_comercial
  UNION ALL
  SELECT CONVERT('leads_guia_licitacoes.whatsapp' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(whatsapp USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM leads_guia_licitacoes
  UNION ALL
  SELECT CONVERT('prematricula.celular' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(celular USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM prematricula
  UNION ALL
  SELECT CONVERT('users.telefone' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(telefone USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(telefone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM users
) t
GROUP BY coluna
ORDER BY coluna;


-- ---------------------------------------------------------------------
-- Q2 — Distribuição de comprimento em dígitos, por coluna
-- Saída: coluna, digitos, linhas
-- Considera só valores que ficaram 100% numéricos após a limpeza.
-- ---------------------------------------------------------------------
SELECT coluna, CHAR_LENGTH(d) AS digitos, COUNT(*) AS linhas
FROM (
  SELECT CONVERT('leads.celular' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS coluna,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci                       AS d
  FROM leads
  UNION ALL
  SELECT CONVERT('students.phone' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(phone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM students
  UNION ALL
  SELECT CONVERT('negociacoes_comercial.whatsapp' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM negociacoes_comercial
  UNION ALL
  SELECT CONVERT('leads_guia_licitacoes.whatsapp' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM leads_guia_licitacoes
  UNION ALL
  SELECT CONVERT('prematricula.celular' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM prematricula
  UNION ALL
  SELECT CONVERT('users.telefone' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(telefone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM users
) t
WHERE d REGEXP '^[0-9]+$'
GROUP BY coluna, CHAR_LENGTH(d)
ORDER BY coluna, digitos;


-- ---------------------------------------------------------------------
-- Q3 — Aderência ao formato canônico, por coluna
-- Saída: coluna, classe, linhas
-- classe: canonico13 | canonico12 | sem_ddi_11 | sem_ddi_10
--         | fora_do_padrao | invalido
-- É esta query que produz a "taxa de aderência ao canônico" que a
-- Fatia 0 promete como critério de pronto.
-- ---------------------------------------------------------------------
SELECT coluna,
       CASE
         WHEN d = '' OR d NOT REGEXP '^[0-9]+$'                    THEN 'invalido'
         WHEN CHAR_LENGTH(d) = 13 AND LEFT(d,2) = '55'             THEN 'canonico13'
         WHEN CHAR_LENGTH(d) = 12 AND LEFT(d,2) = '55'             THEN 'canonico12'
         WHEN CHAR_LENGTH(d) = 11                                  THEN 'sem_ddi_11'
         WHEN CHAR_LENGTH(d) = 10                                  THEN 'sem_ddi_10'
         ELSE 'fora_do_padrao'
       END AS classe,
       COUNT(*) AS linhas
FROM (
  SELECT CONVERT('leads.celular' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS coluna,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci                       AS d
  FROM leads
  UNION ALL
  SELECT CONVERT('students.phone' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(phone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM students
  UNION ALL
  SELECT CONVERT('negociacoes_comercial.whatsapp' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM negociacoes_comercial
  UNION ALL
  SELECT CONVERT('leads_guia_licitacoes.whatsapp' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM leads_guia_licitacoes
  UNION ALL
  SELECT CONVERT('prematricula.celular' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM prematricula
  UNION ALL
  SELECT CONVERT('users.telefone' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
         CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           COALESCE(telefone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           USING utf8mb4) COLLATE utf8mb4_unicode_ci
  FROM users
) t
GROUP BY coluna, classe
ORDER BY coluna, classe;


-- ---------------------------------------------------------------------
-- Q4 — As duas colunas numéricas de telefone do banco
-- Fecha a divergência 5 do plan.md com número.
--   contact.telefone      int    → estoura acima de 2147483647
--   corporativos.telefone bigint → NÃO estoura; o defeito é de
--                                  representação (zero à esquerda, DDI)
-- Ambas são NOT NULL sem default: ausência de telefone vira 0.
-- Sem CONVERT/COLLATE aqui: esta união é só de literais e agregados
-- numéricos, não há mistura de collation de coluna a resolver.
-- Saída: coluna, total, zeros, no_teto_do_int
-- ---------------------------------------------------------------------
SELECT 'contact.telefone' AS coluna,
       COUNT(*)                        AS total,
       SUM(telefone = 0)               AS zeros,
       SUM(telefone = 2147483647)      AS no_teto_do_int
FROM contact
UNION ALL
SELECT 'corporativos.telefone',
       COUNT(*),
       SUM(telefone = 0),
       NULL
FROM corporativos;


-- Q4b — Distribuição de comprimento das duas numéricas.
-- É o que revela perda de zero à esquerda e ausência de DDI, que é o
-- defeito real de corporativos.telefone (bigint não estoura).
-- Saída: coluna, digitos, linhas
SELECT 'contact.telefone' AS coluna,
       CHAR_LENGTH(CAST(telefone AS CHAR)) AS digitos,
       COUNT(*) AS linhas
FROM contact
GROUP BY 1, 2
UNION ALL
SELECT 'corporativos.telefone',
       CHAR_LENGTH(CAST(telefone AS CHAR)),
       COUNT(*)
FROM corporativos
GROUP BY 1, 2
ORDER BY 1, 2;


-- ---------------------------------------------------------------------
-- Q5 — Truncamento em users.telefone varchar(14)
-- 14 é exatamente o tamanho de '+5511987654321': folga zero.
-- Sem UNION, coluna única: nada a resolver de collation.
-- Saída: total, no_teto_do_varchar, com_mais_de_13_digitos
-- ---------------------------------------------------------------------
SELECT COUNT(*)                                  AS total,
       SUM(CHAR_LENGTH(telefone) = 14)           AS no_teto_do_varchar,
       SUM(CHAR_LENGTH(
             REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(telefone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
           ) > 13)                               AS com_mais_de_13_digitos
FROM users;


-- ---------------------------------------------------------------------
-- NOTA — contact.telefone e corporativos.telefone estão fora das uniões
-- de Q6 e Q7 de propósito. Não é omissão: é decisão registrada aqui para
-- que uma revisão futura não a confunda com esquecimento.
--
-- Motivo ORIGINAL (antes da execução): as duas são NOT NULL sem default,
-- então ausência de telefone viraria 0; num GROUP BY tel todos os 0
-- colapsariam num único "telefone" presente em várias tabelas, inflando
-- a sobreposição com dado ausente, e Q7 herdaria o ruído.
--
-- MEDIDO em 21/07/2026 — a contaminação por 0 NÃO se confirmou, e a
-- exclusão continua certa por outro motivo:
--   * corporativos.telefone: 12 linhas, zeros = 0. Sem defeito próprio
--     além da ausência de DDI (8 linhas com 10 dígitos, 4 com 11) —
--     exatamente o padrão das demais tabelas.
--   * contact.telefone: 1 linha, 10 dígitos, no teto do int. O overflow
--     previsto é real; a amostra é de uma linha.
-- Ou seja: ficam fora por VOLUME IRRELEVANTE (1 e 12 linhas contra
-- ~17.376 telefones distintos), não por defeito. A distinção importa —
-- "coluna quebrada" e "coluna vazia de fato" pedem decisões diferentes
-- se alguém um dia repovoar essas tabelas.
--
-- A propriedade do schema (NOT NULL sem default → 0) continua verdadeira
-- e continua valendo como risco para inserts futuros. O que não se
-- confirmou foi a contaminação HOJE.
-- ---------------------------------------------------------------------

-- ---------------------------------------------------------------------
-- Q6 — Sobreposição entre tabelas
-- Quantos telefones canônicos aparecem em 1, 2, 3... tabelas distintas.
-- É exatamente o que o painel de CRM vai precisar unir.
-- O GROUP BY tel aqui agrupa valores vindos de colunas com collation
-- diferente: é o ponto do arquivo onde o padrão comum mais importa.
-- Saída: n_tabelas, telefones   (nenhum telefone no resultado)
-- ---------------------------------------------------------------------
SELECT n_tabelas, COUNT(*) AS telefones
FROM (
  SELECT tel, COUNT(DISTINCT origem) AS n_tabelas
  FROM (
    SELECT origem,
           CASE
             WHEN d = '' OR d NOT REGEXP '^[0-9]+$'         THEN ''
             WHEN CHAR_LENGTH(d) IN (12,13)
                  AND LEFT(d,2) = '55'                      THEN d
             WHEN CHAR_LENGTH(d) IN (10,11)                 THEN CONCAT('55', d)
             ELSE ''
           END AS tel
    FROM (
      SELECT CONVERT('leads' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS origem,
             CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci               AS d
      FROM leads
      UNION ALL
      SELECT CONVERT('students' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
             CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(phone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci
      FROM students
      UNION ALL
      SELECT CONVERT('negociacoes_comercial' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
             CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci
      FROM negociacoes_comercial
      UNION ALL
      SELECT CONVERT('leads_guia_licitacoes' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
             CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci
      FROM leads_guia_licitacoes
      UNION ALL
      SELECT CONVERT('prematricula' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
             CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci
      FROM prematricula
      UNION ALL
      SELECT CONVERT('users' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
             CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(telefone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci
      FROM users
    ) b
  ) u
  WHERE tel <> ''
  GROUP BY tel
) p
GROUP BY n_tabelas
ORDER BY n_tabelas;


-- ---------------------------------------------------------------------
-- Q7 — A métrica do 9º dígito
-- Quantos assinantes existem na base nas DUAS formas (12 e 13 dígitos) e
-- portanto só casariam pela variante — nunca por igualdade de string.
-- É o número que diz se a regra do 9º dígito é detalhe de borda ou uma
-- fatia relevante da base.
--
-- Posições no canônico de 13: 55 (1-2) + DDD (3-4) + 9º dígito (5)
--                             + assinante de 8 (6-13)
-- A guarda [6-9] na posição 6 é a mesma do CLAUDE.md: fixo brasileiro
-- começa em 2-5 e não pode ser colapsado, sob pena de falso positivo.
--
-- Saída: um escalar.
-- ---------------------------------------------------------------------
SELECT COUNT(*) AS nucleos_com_as_duas_formas
FROM (
  SELECT nucleo
  FROM (
    SELECT CASE
             WHEN CHAR_LENGTH(tel) = 13
                  AND SUBSTRING(tel,5,1) = '9'
                  AND SUBSTRING(tel,6,1) REGEXP '^[6-9]$'
             THEN CONCAT(LEFT(tel,4), SUBSTRING(tel,6))
             ELSE tel
           END              AS nucleo,
           CHAR_LENGTH(tel) AS forma
    FROM (
      SELECT CASE
               WHEN d = '' OR d NOT REGEXP '^[0-9]+$'      THEN ''
               WHEN CHAR_LENGTH(d) IN (12,13)
                    AND LEFT(d,2) = '55'                   THEN d
               WHEN CHAR_LENGTH(d) IN (10,11)              THEN CONCAT('55', d)
               ELSE ''
             END AS tel
      FROM (
        SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                 COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
                 USING utf8mb4) COLLATE utf8mb4_unicode_ci AS d
        FROM leads
        UNION ALL
        SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                 COALESCE(phone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
                 USING utf8mb4) COLLATE utf8mb4_unicode_ci
        FROM students
        UNION ALL
        SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                 COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
                 USING utf8mb4) COLLATE utf8mb4_unicode_ci
        FROM negociacoes_comercial
        UNION ALL
        SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                 COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
                 USING utf8mb4) COLLATE utf8mb4_unicode_ci
        FROM leads_guia_licitacoes
        UNION ALL
        SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                 COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
                 USING utf8mb4) COLLATE utf8mb4_unicode_ci
        FROM prematricula
        UNION ALL
        SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                 COALESCE(telefone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
                 USING utf8mb4) COLLATE utf8mb4_unicode_ci
        FROM users
      ) b
    ) c
    WHERE tel <> ''
  ) v
  GROUP BY nucleo
  HAVING COUNT(DISTINCT forma) > 1
) z;


-- ---------------------------------------------------------------------
-- Q8 — Alcançabilidade da forma de 12 dígitos
--
-- POR QUE ESTA QUERY EXISTE. A Q7 responde "quantos assinantes estão
-- gravados nas duas formas DENTRO da nossa base" (379). Esse não é o
-- caso de uso do matching. A Uazapi manda `chat.phone` SEMPRE com 13
-- dígitos; todo registro nosso que normaliza para 12 nunca casa por
-- igualdade de string — só pela variante do 9º dígito. E a forma de 12
-- é ~38% de tudo que é aproveitável (sem_ddi_10 + canonico12, Q3).
--
-- Mas a variante não se aplica a todos os 12: a guarda do CLAUDE.md só
-- deriva quando o assinante de 8 começa em 6-9. Fixo brasileiro começa
-- em 2-5, e inserir `9` nele inventaria um celular de outra pessoa.
-- Fixo, portanto, é INALCANÇÁVEL por WhatsApp por este caminho.
--
-- É esta query que separa o teto de cobertura da cobertura real.
--
-- Posições no canônico de 12: 55 (1-2) + DDD (3-4) + assinante de 8 (5-12)
--
-- Saída: classe, telefones_distintos
--   derivavel_celular_6a9 | fixo_2a5_inalcancavel | anomalo_0ou1
-- ATENÇÃO: é GROUP BY — classe com zero telefone NÃO produz linha
-- zerada, ela some. Ver VERIFICAÇÃO EMBUTIDA no cabeçalho.
-- ---------------------------------------------------------------------
SELECT CASE
         WHEN SUBSTRING(tel,5,1) REGEXP '^[6-9]$' THEN 'derivavel_celular_6a9'
         WHEN SUBSTRING(tel,5,1) REGEXP '^[2-5]$' THEN 'fixo_2a5_inalcancavel'
         ELSE 'anomalo_0ou1'
       END              AS classe,
       COUNT(*)         AS telefones_distintos
FROM (
  SELECT tel
  FROM (
    SELECT CASE
             WHEN d = '' OR d NOT REGEXP '^[0-9]+$'      THEN ''
             WHEN CHAR_LENGTH(d) IN (12,13)
                  AND LEFT(d,2) = '55'                   THEN d
             WHEN CHAR_LENGTH(d) IN (10,11)              THEN CONCAT('55', d)
             ELSE ''
           END AS tel
    FROM (
      SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci AS d
      FROM leads
      UNION ALL
      SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(phone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci
      FROM students
      UNION ALL
      SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci
      FROM negociacoes_comercial
      UNION ALL
      SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci
      FROM leads_guia_licitacoes
      UNION ALL
      SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci
      FROM prematricula
      UNION ALL
      SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(telefone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci
      FROM users
    ) b
  ) c
  WHERE CHAR_LENGTH(tel) = 12
  GROUP BY tel
) q
GROUP BY classe
ORDER BY classe;


-- ---------------------------------------------------------------------
-- Q8b — O mesmo recorte, só na fonte principal do funil
-- negociacoes_comercial concentra 754 das suas 867 linhas aproveitáveis
-- (87%) na forma de 12. Se a maior parte disso for fixo, a cobertura de
-- 28,8% cai muito abaixo disso — e é a tabela que sustenta o painel.
-- Saída: mesma classificação da Q8.
--
-- ATENÇÃO À UNIDADE: o `GROUP BY tel` da subquery interna deduplica ANTES
-- de classificar. O COUNT(*) externo conta TELEFONES DISTINTOS, não linhas.
-- Executada em 22/07/2026: 589 deriváveis + 39 fixos = 628 distintos, contra
-- as 754 LINHAS que a Q3 aponta. Os 126 de diferença são o mesmo número
-- repetido em negociações diferentes — não são registros perdidos.
-- Isso já foi lido como divergência uma vez. Ver Q8c logo abaixo.
-- ---------------------------------------------------------------------
SELECT CASE
         WHEN SUBSTRING(tel,5,1) REGEXP '^[6-9]$' THEN 'derivavel_celular_6a9'
         WHEN SUBSTRING(tel,5,1) REGEXP '^[2-5]$' THEN 'fixo_2a5_inalcancavel'
         ELSE 'anomalo_0ou1'
       END              AS classe,
       COUNT(*)         AS telefones_distintos
FROM (
  SELECT tel
  FROM (
    SELECT CASE
             WHEN d = '' OR d NOT REGEXP '^[0-9]+$'      THEN ''
             WHEN CHAR_LENGTH(d) IN (12,13)
                  AND LEFT(d,2) = '55'                   THEN d
             WHEN CHAR_LENGTH(d) IN (10,11)              THEN CONCAT('55', d)
             ELSE ''
           END AS tel
    FROM (
      SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
               COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
               USING utf8mb4) COLLATE utf8mb4_unicode_ci AS d
      FROM negociacoes_comercial
    ) b
  ) c
  WHERE CHAR_LENGTH(tel) = 12
  GROUP BY tel
) q
GROUP BY classe
ORDER BY classe;


-- ---------------------------------------------------------------------
-- Q8c — A MESMA classificação da Q8b, mas em LINHAS
--
-- POR QUE ESTA QUERY EXISTE. A Q8b conta telefones distintos; todo o
-- resto deste diagnóstico (867 de 3.006, os 754 da forma de 12) conta
-- linhas. Misturar as duas unidades produz razões sem significado:
-- 589/754 NÃO é taxa de cobertura, porque numerador e denominador
-- medem coisas diferentes. Isso já foi lido como "126 registros
-- faltando" uma vez — não faltavam, eram repetições.
--
-- Esta query fecha o lado em linhas, para responder "quanto do funil
-- comercial eu alcanço?" — que é pergunta por negociação, não por
-- telefone. A Q8b continua respondendo "uma mensagem que chega é
-- identificada?", que é pergunta por telefone.
--
-- ÚNICA diferença para a Q8b: não há `GROUP BY tel` na subquery. Cada
-- linha da tabela é classificada e contada por si.
--
-- Mesmas garantias do arquivo: somente leitura, nenhum telefone
-- projetado na saída, só COUNT/GROUP BY sobre classes.
--
-- Saída: classe, linhas
-- ATENÇÃO: é GROUP BY — classe com zero linha NÃO produz linha zerada.
-- ---------------------------------------------------------------------
SELECT CASE
         WHEN SUBSTRING(tel,5,1) REGEXP '^[6-9]$' THEN 'derivavel_celular_6a9'
         WHEN SUBSTRING(tel,5,1) REGEXP '^[2-5]$' THEN 'fixo_2a5_inalcancavel'
         ELSE 'anomalo_0ou1'
       END              AS classe,
       COUNT(*)         AS linhas
FROM (
  SELECT CASE
           WHEN d = '' OR d NOT REGEXP '^[0-9]+$'      THEN ''
           WHEN CHAR_LENGTH(d) IN (12,13)
                AND LEFT(d,2) = '55'                   THEN d
           WHEN CHAR_LENGTH(d) IN (10,11)              THEN CONCAT('55', d)
           ELSE ''
         END AS tel
  FROM (
    SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
             COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
             USING utf8mb4) COLLATE utf8mb4_unicode_ci AS d
    FROM negociacoes_comercial
  ) b
) c
WHERE CHAR_LENGTH(tel) = 12
GROUP BY classe
ORDER BY classe;


-- ---------------------------------------------------------------------
-- VERIFICAÇÃO EMBUTIDA da Q8c: a soma das suas classes deve dar
-- exatamente 754 — o total da forma de 12 em negociacoes_comercial
-- segundo a Q3 (sem_ddi_10 705 + canonico12 49).
--
-- Se der 754: Q3 e Q8c concordam, e a diferença para os 628 da Q8b está
-- inteiramente explicada por deduplicação. Nada mais a investigar.
--
-- Se NÃO der 754: aí sim há divergência real de lógica entre as duas
-- queries, e nenhuma taxa de cobertura deve ser publicada até entender.
-- ---------------------------------------------------------------------


-- =====================================================================
-- Q9 — RECONCILIAÇÃO ATÔMICA Q3 × Q8c
-- =====================================================================
--
-- POR QUE ESTA QUERY EXISTE. A Q8c voltou com 704 + 51 = 755, contra as
-- 754 linhas de forma de 12 que a Q3 apontou (sem_ddi_10 705 +
-- canonico12 49). A verificação embutida da Q8c manda PARAR nesse caso.
--
-- O DEFEITO DE MÉTODO: Q3 rodou em 21/07/2026 e Q8c em 22/07/2026, e
-- negociacoes_comercial recebe inserção do comercial todo dia. Duas
-- medições feitas em dias diferentes sobre uma tabela viva não podem ser
-- subtraídas — a diferença tanto pode ser divergência de lógica quanto
-- uma negociação nova. Comparação assim NÃO DECIDE NADA.
--
-- Esta query elimina o tempo como variável: calcula as DUAS
-- classificações na MESMA varredura, sobre exatamente as mesmas linhas.
--
-- COMO LER O RESULTADO — critério fixado ANTES de rodar:
--
--   * discordancias = 0 E q3_forma12 = q8c_forma12
--       → as lógicas concordam, e concordam sobre o mesmo instante.
--         Se total_linhas = 3007, o +1 era a negociação nova: ENCERRADO.
--         Se total_linhas = 3006, a premissa da deriva cai e o próximo
--         suspeito é a Q3 e a Q8c terem rodado contra SCHEMAS DIFERENTES
--         (unipublicabrasil3 × 4 × 5) — ver o bloco SCHEMA no cabeçalho.
--
--   * discordancias > 0
--       → divergência real de lógica, com a contagem exata. Rodar a Q9b.
--
-- NOTA: a equivalência das duas lógicas já foi PROVADA fora de produção,
-- em 22/07/2026, contra uma bateria de 35 casos de fronteira num MySQL
-- local (comprimentos 0/2/9/10/11/12/13/14/20, 10 dígitos começando com
-- 55, 11 começando com 55, separadores, controle, dígito não-ASCII, e
-- bloco de assinante em cada faixa). Zero discordâncias. Esta query
-- confirma isso sobre o dado real — não é o único apoio da conclusão.
--
-- Saída: uma linha.
-- ---------------------------------------------------------------------
SELECT COUNT(*)                                              AS total_linhas,
       SUM(classe_q3 = 'canonico12')                         AS q3_canonico12,
       SUM(classe_q3 = 'sem_ddi_10')                         AS q3_sem_ddi_10,
       SUM(classe_q3 IN ('canonico12','sem_ddi_10'))         AS q3_forma12,
       SUM(CHAR_LENGTH(tel) = 12)                            AS q8c_forma12,
       SUM( (classe_q3 IN ('canonico12','sem_ddi_10'))
            <> (CHAR_LENGTH(tel) = 12) )                     AS discordancias
FROM (
  SELECT CASE
           WHEN d = '' OR d NOT REGEXP '^[0-9]+$'        THEN 'invalido'
           WHEN CHAR_LENGTH(d) = 13 AND LEFT(d,2) = '55' THEN 'canonico13'
           WHEN CHAR_LENGTH(d) = 12 AND LEFT(d,2) = '55' THEN 'canonico12'
           WHEN CHAR_LENGTH(d) = 11                      THEN 'sem_ddi_11'
           WHEN CHAR_LENGTH(d) = 10                      THEN 'sem_ddi_10'
           ELSE 'fora_do_padrao'
         END AS classe_q3,
         CASE
           WHEN d = '' OR d NOT REGEXP '^[0-9]+$'      THEN ''
           WHEN CHAR_LENGTH(d) IN (12,13)
                AND LEFT(d,2) = '55'                   THEN d
           WHEN CHAR_LENGTH(d) IN (10,11)              THEN CONCAT('55', d)
           ELSE ''
         END AS tel
  FROM (
    SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
             COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
             USING utf8mb4) COLLATE utf8mb4_unicode_ci AS d
    FROM negociacoes_comercial
  ) b
) c;


-- ---------------------------------------------------------------------
-- Q9b — Assinatura das linhas discordantes. RODAR SÓ SE Q9 der
-- discordancias > 0; com zero, devolve conjunto vazio e não diz nada.
--
-- LGPD: projeta comprimento e dois booleanos. Nenhum telefone, nenhum
-- DDD (LEFT(d,2) entra como comparação, nunca como valor).
--
-- Saída: digitos, tem_ddi, so_digitos, linhas
-- ---------------------------------------------------------------------
SELECT CHAR_LENGTH(d)                AS digitos,
       (LEFT(d,2) = '55')            AS tem_ddi,
       (d REGEXP '^[0-9]+$')         AS so_digitos,
       COUNT(*)                      AS linhas
FROM (
  SELECT d,
         CASE
           WHEN d = '' OR d NOT REGEXP '^[0-9]+$'        THEN 'invalido'
           WHEN CHAR_LENGTH(d) = 13 AND LEFT(d,2) = '55' THEN 'canonico13'
           WHEN CHAR_LENGTH(d) = 12 AND LEFT(d,2) = '55' THEN 'canonico12'
           WHEN CHAR_LENGTH(d) = 11                      THEN 'sem_ddi_11'
           WHEN CHAR_LENGTH(d) = 10                      THEN 'sem_ddi_10'
           ELSE 'fora_do_padrao'
         END AS classe_q3,
         CASE
           WHEN d = '' OR d NOT REGEXP '^[0-9]+$'      THEN ''
           WHEN CHAR_LENGTH(d) IN (12,13)
                AND LEFT(d,2) = '55'                   THEN d
           WHEN CHAR_LENGTH(d) IN (10,11)              THEN CONCAT('55', d)
           ELSE ''
         END AS tel
  FROM (
    SELECT CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
             COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
             USING utf8mb4) COLLATE utf8mb4_unicode_ci AS d
    FROM negociacoes_comercial
  ) b
) c
WHERE (classe_q3 IN ('canonico12','sem_ddi_10')) <> (CHAR_LENGTH(tel) = 12)
GROUP BY 1, 2, 3
ORDER BY 1, 2, 3;


-- =====================================================================
-- Q10 — Caracteres de controle sobrevivendo à limpeza
-- =====================================================================
--
-- ACHADO INDEPENDENTE, descoberto em 22/07/2026 ao provar a equivalência
-- Q3 × Q8c num MySQL 9.7 local:
--
--   SELECT '1198765432\n' REGEXP '^[0-9]+$';   -- devolve 1  (!)
--   SELECT '1198765432\t' REGEXP '^[0-9]+$';   -- devolve 0
--
-- O `$` do ICU (motor de regex do MySQL 8+) casa TAMBÉM antes de um
-- terminador de linha final. Ou seja, REGEXP '^[0-9]+$' NÃO é um teste
-- estrito de "só dígitos": um valor terminado em \n passa.
--
-- CONSEQUÊNCIA, se existir na base: um celular de 10 dígitos com \n no
-- fim tem CHAR_LENGTH 11, é classificado sem_ddi_11 em vez de
-- sem_ddi_10, e normaliza para uma string de 13 CARACTERES com um
-- newline dentro — que jamais casaria com o chat.phone da Uazapi. Um
-- não-match silencioso, do tipo que o CLAUDE.md manda evitar.
--
-- Isto NÃO explica o +1 da Q8c: Q3 e Q8c usam o mesmo REGEXP, então o
-- efeito é idêntico nas duas e não produz assimetria.
--
-- Esta query mede se o problema existe de fato ou é só teórico. A cadeia
-- de REPLACE remove espaço - ( ) . + / — nunca removeu \n, \r, \t.
--
-- NÃO alterar a cadeia de limpeza por causa disto sem re-executar TODO o
-- diagnóstico: mudar a limpeza muda todos os números já publicados.
--
-- Saída: coluna, linhas_com_controle
-- ---------------------------------------------------------------------
SELECT coluna, SUM(tem_controle) AS linhas_com_controle
FROM (
  SELECT coluna,
         (INSTR(d, CHAR(10)) > 0 OR INSTR(d, CHAR(13)) > 0 OR INSTR(d, CHAR(9)) > 0) AS tem_controle
  FROM (
    SELECT CONVERT('students.phone' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS coluna,
           CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
             COALESCE(phone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
             USING utf8mb4) COLLATE utf8mb4_unicode_ci                        AS d
    FROM students
    UNION ALL
    SELECT CONVERT('negociacoes_comercial.whatsapp' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
           CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
             COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
             USING utf8mb4) COLLATE utf8mb4_unicode_ci
    FROM negociacoes_comercial
    UNION ALL
    SELECT CONVERT('leads_guia_licitacoes.whatsapp' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
           CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
             COALESCE(whatsapp,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
             USING utf8mb4) COLLATE utf8mb4_unicode_ci
    FROM leads_guia_licitacoes
    UNION ALL
    SELECT CONVERT('prematricula.celular' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
           CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
             COALESCE(celular,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
             USING utf8mb4) COLLATE utf8mb4_unicode_ci
    FROM prematricula
    UNION ALL
    SELECT CONVERT('users.telefone' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
           CONVERT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
             COALESCE(telefone,''),' ',''),'-',''),'(',''),')',''),'.',''),'+',''),'/','')
             USING utf8mb4) COLLATE utf8mb4_unicode_ci
    FROM users
  ) b
) c
GROUP BY coluna
ORDER BY coluna;
