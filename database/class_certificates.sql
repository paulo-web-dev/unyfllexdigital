-- Certificado por TURMA ("Curso Livre Aprofundado" da área do assinante) — regra 2026-09-02:
-- emitido quando o aluno atinge nota >= 70% na prova de TODOS os painéis da turma que
-- têm prova pronta; carga horária fixa de 20h; 1 certificado por (aluno, turma).
-- Tabela irmã de panel_certificates (certificado por painel: minissérie e Curso
-- Modular, 12h), com o mesmo token de autenticidade e a mesma página pública
-- /certificado/validar/{token}.
--
-- Idempotente. A aplicação funciona ANTES de rodar este script: sem a tabela, o
-- certificado de turma fica "indisponível no momento" e o resto segue igual.
-- Rodar à mão no phpMyAdmin do banco de produção.

CREATE TABLE IF NOT EXISTS class_certificates (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id       BIGINT UNSIGNED NOT NULL,
    classes_id       BIGINT UNSIGNED NOT NULL,
    token            CHAR(40)        NOT NULL, -- código de autenticidade; página pública /certificado/validar/{token}
    aluno            VARCHAR(255)    NOT NULL, -- nome congelado na emissão
    titulo           VARCHAR(255)    NOT NULL, -- título da turma congelado na emissão
    horas            INT UNSIGNED    NOT NULL DEFAULT 20,
    provas_total     INT UNSIGNED    NOT NULL, -- painéis com prova na emissão
    provas_aprovadas INT UNSIGNED    NOT NULL,
    concluido_em     DATE            NOT NULL, -- data em que a última prova pendente foi aprovada
    created_at       DATETIME        NULL,
    updated_at       DATETIME        NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_class_certificates_aluno_turma (student_id, classes_id),
    UNIQUE KEY uq_class_certificates_token (token),
    KEY idx_class_certificates_turma (classes_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
