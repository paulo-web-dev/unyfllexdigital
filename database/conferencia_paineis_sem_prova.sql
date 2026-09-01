-- Conferência: painéis do catálogo do assinante SEM prova pronta/gerando.
-- Reproduz AssinanteCatalogoService::idsPaineisExibiveis() (regras de 2026-08):
--   - turmas able elegíveis: minissérie (express='1') ou gravado (unyflex=1, express='0');
--   - só painéis able com pelo menos uma video_lesson com link não vazio;
--   - dedup entre turmas por (course_id, título do painel): vence a turma mais recente.
-- Somente leitura. Requer MySQL 8+ (window functions).
--
-- Coluna chars_conteudo_bruto: tamanho de panels.content COM HTML/entidades; o mínimo
-- de 200 chars da geração (PanelProvaService::MIN_FONTE) é medido SEM HTML, então um
-- painel pode passar de 200 aqui e ainda ficar sem prova. prova_status NULL = nunca
-- gerada; 'erro' = última geração falhou.

WITH base AS (
    SELECT
        p.id          AS painel_id,
        p.classes_id,
        c.course_id,
        c.title       AS turma,
        p.title       AS painel,
        c.start_date  AS turma_data,
        p.content,
        (SELECT COUNT(*) FROM video_lessons vl
          WHERE vl.panel_id = p.id AND vl.link IS NOT NULL AND vl.link <> '') AS aulas
    FROM panels p
    JOIN classes c ON c.id = p.classes_id
    WHERE p.status = 'able'
      AND c.status = 'able'
      AND (c.express = '1' OR (c.unyflex = 1 AND c.express = '0'))
),
com_aula AS (
    SELECT b.*,
        FIRST_VALUE(b.classes_id) OVER (
            PARTITION BY b.course_id, LOWER(TRIM(b.painel))
            ORDER BY b.turma_data DESC, b.classes_id DESC
        ) AS classe_vencedora
    FROM base b
    WHERE b.aulas > 0
)
SELECT
    e.painel_id,
    e.turma,
    e.painel,
    CHAR_LENGTH(COALESCE(e.content, '')) AS chars_conteudo_bruto,
    (SELECT pp2.status FROM panel_provas pp2
      WHERE pp2.panel_id = e.painel_id ORDER BY pp2.id DESC LIMIT 1) AS prova_status
FROM com_aula e
WHERE e.classes_id = e.classe_vencedora
  AND NOT EXISTS (
      SELECT 1 FROM panel_provas pp
       WHERE pp.panel_id = e.painel_id AND pp.status IN ('pronto', 'gerando')
  )
ORDER BY e.turma, e.painel_id;
