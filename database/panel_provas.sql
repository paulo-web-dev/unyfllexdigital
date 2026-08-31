-- Prova por painel ("Curso Modular" da área do assinante).
-- Espelha course_materials (type='prova') e modular_prova_attempts, trocando
-- modular_course_id por panel_id. Rodar no phpMyAdmin do banco de produção.
-- Idempotente (IF NOT EXISTS); a aplicação funciona antes de rodar este script
-- (as consultas a estas tabelas estão protegidas por try/catch).

-- Uma prova por painel; content guarda o JSON de questões no mesmo formato dos
-- cursos modulares: [{enunciado, alternativas[], correta, comentario}].
CREATE TABLE IF NOT EXISTS `panel_provas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `panel_id` bigint unsigned NOT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gerando',
  `version` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pp_panel_uq` (`panel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tentativas do aluno/assinante (score corrigido no servidor a partir de answers).
CREATE TABLE IF NOT EXISTS `panel_prova_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `panel_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `score` int unsigned NOT NULL DEFAULT '0',
  `total` int unsigned NOT NULL DEFAULT '0',
  `answers` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ppa_panel_student_idx` (`panel_id`,`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
