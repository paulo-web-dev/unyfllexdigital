<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Prova de um painel ("Curso Modular" da área do assinante).
 *
 * Uma linha por painel (UNIQUE panel_id); `content` guarda o JSON de questões no
 * mesmo formato das provas dos cursos modulares (course_materials type='prova'):
 * [{enunciado, alternativas[], correta, comentario}].
 * Status: gerando | pronto | erro. Tabela criada por database/panel_provas.sql.
 */
class PanelProva extends Model
{
    protected $table = 'panel_provas';

    protected $fillable = [
        'panel_id',
        'title',
        'content',
        'status',
        'version',
    ];

    public function panel()
    {
        return $this->belongsTo(Panel::class, 'panel_id');
    }

    /**
     * Questões decodificadas (array vazio se o JSON for inválido), com as
     * alternativas EMBARALHADAS de forma determinística por (panel_id, índice
     * da questão).
     *
     * Motivo (2026-09): o workflow n8n concentra a correta nas primeiras letras
     * (90 das 428 provas saíram com o gabarito inteiro na mesma alternativa) e
     * a nota vai virar critério de certificação. O embaralhamento fica AQUI —
     * único ponto de leitura — para que o player (exibição) e provaResultado
     * (recálculo da nota no servidor) vejam sempre a MESMA ordem; a semente não
     * depende de sessão nem de tempo, então a ordem é estável entre
     * recarregamentos. O JSON em `content` permanece na ordem original do n8n.
     */
    public function questoes(): array
    {
        $questoes = json_decode((string) $this->content, true) ?: [];

        foreach ($questoes as $qi => $q) {
            $alts = array_values($q['alternativas'] ?? []);
            $n = count($alts);
            if ($n < 2) {
                continue;
            }

            // Permutação estável: índices ordenados pelo hash de (painel, questão, alternativa).
            // md5, não crc32: o crc32 é linear e, para chaves quase iguais, gerava ordens
            // correlacionadas entre questões (16 provas continuavam com gabarito numa letra só).
            $ordem = range(0, $n - 1);
            usort($ordem, fn ($a, $b) => md5("{$this->panel_id}:{$qi}:{$a}") <=> md5("{$this->panel_id}:{$qi}:{$b}"));

            $novaCorreta = array_search((int) ($q['correta'] ?? 0), $ordem, true);

            $questoes[$qi]['alternativas'] = array_map(fn ($i) => $alts[$i], $ordem);
            $questoes[$qi]['correta'] = $novaCorreta === false ? 0 : (int) $novaCorreta;
        }

        return $questoes;
    }
}
