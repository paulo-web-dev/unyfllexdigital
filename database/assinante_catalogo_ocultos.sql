-- Turmas ocultas do catálogo da área do assinante (só apresentação).
--
-- Caso de uso (2026-09): a mesma turma existe em `classes` como minissérie
-- (express='1') E como gravada (express='0', unyflex=1), no mesmo course_id e com
-- o mesmo nome, mas com painéis de títulos diferentes — então a deduplicação do
-- catálogo (por course_id + título do painel) não as junta e o assinante vê o
-- curso duas vezes. Decisão: a minissérie fica; a edição gravada some do catálogo.
--
-- Efeito: a turma listada aqui não aparece no catálogo do assinante (nem nos
-- contadores/categorias). NADA mais muda: enrollments, views, provas e o acesso
-- de quem já está matriculado (/dashboard/player/{slug}) seguem iguais.
-- Para ocultar outra turma no futuro: INSERT do classes_id aqui. Para voltar a
-- exibir: DELETE da linha.
--
-- Idempotente. A aplicação funciona ANTES de rodar este script: sem a tabela,
-- nada é ocultado. Rodar à mão no phpMyAdmin do banco de produção.

CREATE TABLE IF NOT EXISTS assinante_catalogo_ocultos (
    classes_id  BIGINT UNSIGNED NOT NULL,
    motivo      VARCHAR(255)    NULL,
    created_at  DATETIME        NULL,
    PRIMARY KEY (classes_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Edições gravadas com o mesmo nome de uma minissérie, levantadas em 2026-09-02
-- (4 pares no mesmo course_id + 2 pares em course_id diferentes; a minissérie de
-- mesmo nome continua no catálogo).
INSERT IGNORE INTO assinante_catalogo_ocultos (classes_id, motivo, created_at) VALUES
    (2008, 'Duplicata da minissérie 2244 "Dispensa e Inexigibilidade Eletrônicas + MEI" (course_id 906)', NOW()),
    (1884, 'Duplicata da minissérie 2226 "Reforma Tributária" (course_id 923)',                          NOW()),
    (1960, 'Duplicata da minissérie 2237 "Redação, Oratória e Arquivologia" (course_id 858)',            NOW()),
    (2245, 'Duplicata da minissérie 2166 "Ouvidoria, e-SIC  Portal e LGPD" (course_id 803)',             NOW()),
    (1690, 'Duplicata da minissérie 2226 "Reforma Tributária" (course_id 911, minissérie em 923)',       NOW()),
    (1847, 'Duplicata da minissérie 2087 "Controle Interno" (course_id 907, minissérie em 981)',         NOW());

-- Duas turmas GRAVADAS com o mesmo nome (2026-09-02): fica a 1634 (2024, course_id 784,
-- conteúdo todo sobre as plataformas); sai a 1333 (2023, course_id 852, metade dos
-- painéis é de tesouraria/SIM-AM). Zero views nas duas; matrículas mantêm o acesso.
INSERT IGNORE INTO assinante_catalogo_ocultos (classes_id, motivo, created_at) VALUES
    (1333, 'Duplicata da gravada 1634 "Plataformas do Pregão" (course_id 852, edição 2023)', NOW());
