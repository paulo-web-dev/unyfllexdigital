-- Certificados por painel ("Curso Modular" da área do assinante) — regras 2026-09:
-- melhor nota >= 70% na prova do painel E material didático (não-PODCAST) disponível;
-- carga horária fixa de 12h; 1 certificado por (aluno, painel).
--
-- Idempotente. A aplicação funciona ANTES de rodar este script: sem a tabela o
-- certificado ainda renderiza, apenas não fica registrado (sem token).
-- Rodar à mão no MySQL de produção, como os demais scripts de database/.

CREATE TABLE IF NOT EXISTS panel_certificates (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id   BIGINT UNSIGNED NOT NULL,
    panel_id     BIGINT UNSIGNED NOT NULL,
    token        CHAR(40)        NOT NULL, -- código de autenticidade; página pública /certificado/validar/{token}
    aluno        VARCHAR(255)    NOT NULL, -- nome congelado na emissão
    titulo       VARCHAR(255)    NOT NULL, -- "turma — painel" congelado na emissão
    horas        INT UNSIGNED    NOT NULL DEFAULT 12,
    score        INT UNSIGNED    NOT NULL, -- melhor nota no momento da emissão
    total        INT UNSIGNED    NOT NULL,
    concluido_em DATE            NOT NULL, -- data da 1ª tentativa que atingiu a nota mínima
    created_at   DATETIME        NULL,
    updated_at   DATETIME        NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_panel_certificates_aluno_painel (student_id, panel_id),
    UNIQUE KEY uq_panel_certificates_token (token),
    KEY idx_panel_certificates_panel (panel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
