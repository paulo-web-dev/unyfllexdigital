<?php

namespace App\Services;

use App\Models\ResultadoRespostaArp;
use App\Models\FuncionarioQuestionarioArp;

class ArpCalculationService
{
    private const PESOS = [
        1 => 0.0,
        2 => 0.5,
        3 => 1.5,
        4 => 2.5,
        5 => 4.0,
    ];

    private const NIVEIS = [
        ['min' => 17, 'max' => 20, 'label' => 'Extremo',        'codigo' => 'PR1', 'cor' => '#FF2D55'],
        ['min' => 13, 'max' => 16, 'label' => 'Elevado',        'codigo' => 'PR2', 'cor' => '#FF6B00'],
        ['min' =>  9, 'max' => 12, 'label' => 'Moderado',       'codigo' => 'PR3', 'cor' => '#FFD60A'],
        ['min' =>  5, 'max' =>  8, 'label' => 'Baixo',          'codigo' => 'PR4', 'cor' => '#30D158'],
        ['min' =>  0, 'max' =>  4, 'label' => 'Insignificante', 'codigo' => 'NA',  'cor' => '#636366'],
    ];

    private const RECOMENDACOES = [
        'Funções e expectativas'                        => 'Definir funções de trabalho, relações de supervisão e requisitos de desempenho para minimizar confusões e equívocos. Facilitar o desenvolvimento de competências e atribuir tarefas a trabalhadores com conhecimentos e aptidões adequados.',
        'Controle de trabalho ou autonomia'             => 'Aumentar o controle dos trabalhadores sobre como atuam, introduzindo trabalho flexível, compartilhamento de trabalho e mais consulta sobre práticas de trabalho.',
        'Demandas de trabalho'                          => 'Priorizar tarefas e permitir prazos flexíveis. Proporcionar maior acesso ao apoio social e limitar o trabalho remoto ou isolado quando apropriado.',
        'Gestão de mudança organizacional'              => 'Consultar os trabalhadores e seus representantes sobre mudanças no local de trabalho e como estas podem afetá-los. Fornecer supervisão eficaz e orientação durante transições.',
        'Trabalho e ritmo de trabalho'                  => 'Fornecer suporte prático durante picos de carga (trabalhadores adicionais). Permitir pausas e restringir contato relacionado ao trabalho nos períodos de folga.',
        'Horários de trabalho e cronograma'             => 'Priorizar tarefas e permitir prazos flexíveis. Reduzir exigências de horas extras imprevistas e comunicar mudanças com antecedência.',
        'Segurança sobre desemprego e trabalhos precários' => 'Desenvolver políticas claras de cargos, salários e benefícios. Garantir conformidade com as leis trabalhistas.',
        'Ambiente de trabalho, equipamentos e tarefas perigosas' => 'Adequar espaço, iluminação, temperatura e equipamentos. Exigir uso de EPIs e realizar manutenção preventiva regularmente.',
        'Relações interpessoais'                        => 'Promover práticas de trabalho em equipe e relações hierárquicas saudáveis. Criar canais formais de comunicação e mediação de conflitos.',
        'Liderança'                                     => 'Fornecer informações e treinamentos para líderes sobre condutas e práticas adequadas. Estabelecer canais para reclamações e sugestões.',
        'Cultura organizacional'                        => 'Desenvolver políticas que descrevam expectativas de comportamento e como condutas inaceitáveis serão gerenciadas. Integrar programas de saúde e segurança.',
        'Reconhecimento e recompensa'                   => 'Implementar programas formais de reconhecimento. Garantir feedback construtivo e regular sobre o desempenho dos colaboradores.',
        'Apoio e suporte'                               => 'Garantir acesso ágil a serviços de suporte (TI, manutenção). Promover treinamentos e disponibilizar informações necessárias ao desempenho.',
        'Supervisão / Gerência'                         => 'Promover feedback construtivo e avaliações periódicas. Assegurar transparência e justiça nas decisões gerenciais.',
        'Civilidade e respeito'                         => 'Fomentar cultura de respeito mútuo. Estabelecer política de tolerância zero a comportamentos desrespeitosos.',
        'Equilíbrio Trabalho / Vida'                    => 'Implementar políticas de desconexão digital. Monitorar carga de trabalho para evitar interferência na vida pessoal e no descanso.',
        'Violência no trabalho / Assédio'               => 'Implementar política de tolerância zero a qualquer forma de assédio e violência. Criar canal de denúncias sigiloso e realizar treinamentos de prevenção.',
    ];

    private const FONTES_GERADORAS = [
        'Funções e expectativas' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Ausência de descrição formal de cargo ou desalinhamento entre as atribuições reais exercidas e as previstas, dificultando o entendimento mútuo entre liderança e equipe.',
            'Elevado' => 'Inexistência de critérios objetivos de desempenho esperado, fazendo com que o trabalhador não saiba claramente como está sendo avaliado.',
            'Extremo' => 'Sobreposição de funções entre diferentes colaboradores ou setores, gerando conflitos sobre quem deve executar determinada tarefa.',
        ],
        'Controle de trabalho ou autonomia' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Ausência de espaço para o trabalhador influenciar o ritmo, a ordem ou o método de realização do seu trabalho.',
            'Elevado' => 'Microgerenciamento constante por parte da liderança, reduzindo a sensação de competência e controle do colaborador sobre suas atividades.',
            'Extremo' => 'Falta de flexibilidade nos processos para que o trabalhador proponha melhorias ou ajustes na forma como o trabalho é realizado.',
        ],
        'Demandas de trabalho' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Metas desproporcionais à estrutura oferecida pela empresa, exigindo esforço além da capacidade sustentável do trabalhador.',
            'Elevado' => 'Acúmulo de funções não previstas no cargo original, ampliando a carga de trabalho sem ajuste correspondente de prazo ou remuneração.',
            'Extremo' => 'Complexidade das tarefas superior ao nível de capacitação oferecido, gerando pressão e insegurança na execução.',
        ],
        'Gestão de mudança organizacional' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Ausência de treinamento ou suporte adequado durante períodos de transição, reestruturação ou adoção de novas ferramentas.',
            'Elevado' => 'Falta de participação dos colaboradores no processo de mudança, gerando resistência, insegurança e percepção de perda de controle.',
            'Extremo' => 'Mudanças frequentes e mal planejadas que geram instabilidade constante e dificultam a criação de rotinas e previsibilidade no trabalho.',
        ],
        'Trabalho e ritmo de trabalho' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Ausência de controle do trabalhador sobre o próprio ritmo, com cadência determinada exclusivamente por máquinas, sistemas ou metas externas.',
            'Elevado' => 'Picos de demanda recorrentes sem ajuste de equipe ou prazo, obrigando intensificação do ritmo de forma não sustentável.',
            'Extremo' => 'Falta de monitoramento da fadiga acumulada, permitindo que o ritmo de trabalho avance sem critérios de segurança e bem-estar.',
        ],
        'Horários de trabalho e cronograma' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Escalas de trabalho definidas sem consulta prévia ao colaborador, dificultando a organização da vida pessoal e familiar.',
            'Elevado' => 'Convocações de última hora para horas extras ou mudanças de turno, sem antecedência mínima razoável.',
            'Extremo' => 'Ausência de política clara sobre compensação de horas, banco de horas ou intervalos, gerando insegurança quanto aos próprios direitos.',
        ],
        'Segurança sobre desemprego e trabalhos precários' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Política salarial e de benefícios desatualizada ou não competitiva, gerando percepção de desequilíbrio entre o esforço empregado e a recompensa financeira.',
            'Elevado' => 'Sazonalidade ou instabilidade econômica do negócio que impacta diretamente na carga horária e na composição da remuneração variável do trabalhador.',
            'Extremo' => 'Gestão administrativa ou financeira irregular que falha no recolhimento de encargos legais e direitos trabalhistas, gerando insegurança quanto à proteção social do empregado.',
        ],
        'Ambiente de trabalho, equipamentos e tarefas perigosas' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Falhas no projeto luminotécnico ou falta de manutenção em luminárias, resultando em níveis de iluminamento (lux) insuficientes ou excessivos para a execução segura da tarefa.',
            'Elevado' => 'Operação de maquinário pesado sem isolamento acústico e ausência de um programa de gestão de proteção auditiva que garanta o fornecimento e a fiscalização do uso de protetores auriculares.',
            'Extremo' => 'Deficiência no planejamento logístico e de suprimentos da empresa, obrigando o trabalhador a improvisar métodos de trabalho ou a interromper seu fluxo produtivo por falta de insumos.',
        ],
        'Relações interpessoais' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Cultura organizacional com baixa gestão de conflitos e falta de treinamento em competências socioemocionais, favorecendo interações interpessoais desgastantes ou hostis.',
            'Elevado' => 'Ambiente de alta competitividade ou desorganização de processos que gera sobreposição de responsabilidades, culminando em atritos e disputas diretas entre os pares.',
            'Extremo' => 'Estrutura de trabalho individualizada e ausência de práticas de cooperação ou suporte mútuo incentivadas pela liderança.',
        ],
        'Liderança' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Cultura organizacional centralizadora e falta de canais formais de escuta ativa, invalidando a participação dos colaboradores na melhoria dos processos e na resolução de problemas cotidianos.',
            'Elevado' => 'Estrutura de comunicação verticalizada que retém informações críticas, dificultando a previsibilidade das tarefas e a autonomia dos subordinados.',
            'Extremo' => 'Inexistência de códigos de conduta rigorosos e de mecanismos de fiscalização comportamental, permitindo estilos de gestão baseados no autoritarismo ou na pressão psicológica excessiva.',
        ],
        'Cultura organizacional' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Ausência de políticas estruturadas de treinamento, planos de carreira ou programas de incentivo à qualificação, o que limita as perspectivas de crescimento e a valorização do capital humano dentro da organização.',
            'Elevado' => 'Cultura institucional fundamentada em cobranças desproporcionais e falta de acolhimento social, permitindo que a pressão por resultados se transforme em práticas de gestão autoritárias sem a devida mediação de conduta.',
            'Extremo' => 'Falta de transparência nos critérios de recompensa, promoção e punição, aliada a uma aplicação desigual das regras internas que favorece a percepção de favoritismo ou injustiça organizacional.',
        ],
        'Reconhecimento e recompensa' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Falta de agilidade nos processos administrativos de promoção ou premiação e ausência de uma cultura de feedback imediato, gerando um distanciamento entre a entrega do resultado e a gratificação correspondente.',
            'Elevado' => 'Políticas de recompensa limitadas ou inexistentes que não contemplam formas simbólicas ou financeiras de valorizar as competências e as superações dos colaboradores.',
            'Extremo' => 'Cultura organizacional que foca excessivamente na correção de falhas e ignora as conquistas, gerando um sentimento de invisibilidade e baixo senso de pertencimento no trabalhador.',
        ],
        'Desenvolvimento de carreira' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Cultura organizacional com baixa rotatividade em cargos de nível superior e ausência de programas de incentivo à qualificação, resultando na percepção de que o tempo de serviço não se traduz em crescimento profissional.',
            'Elevado' => 'Ausência de políticas de recrutamento interno, programas de mentoria ou de sucessão, priorizando contratações externas em detrimento da valorização e capacitação do capital humano já existente na empresa.',
            'Extremo' => 'Ausência de políticas de recrutamento interno, programas de mentoria ou de sucessão, priorizando contratações externas em detrimento da valorização e capacitação do capital humano já existente na empresa.',
        ],
        'Apoio e suporte' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Deficiência no dimensionamento das equipes de apoio técnico e administrativo ou excesso de burocracia nos fluxos internos, dificultando a resolução de problemas operacionais que impactam a execução do trabalho.',
            'Elevado' => 'Inexistência de um programa estruturado de integração e educação continuada, somada a falhas na comunicação interna que não provê os dados necessários para a realização segura e eficiente das tarefas.',
            'Extremo' => 'Inexistência de um programa estruturado de integração e educação continuada, somada a falhas na comunicação interna que não provê os dados necessários para a realização segura e eficiente das tarefas.',
        ],
        'Supervisão / Gerência' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Ausência de políticas formais de valorização do colaborador e estilo de gestão focado exclusivamente em metas numéricas, ignorando o suporte motivacional e o esforço individual.',
            'Elevado' => 'Falta de transparência nos processos decisórios e aplicação subjetiva de normas internas, permitindo que critérios pessoais ou favoritismos prevaleçam sobre o mérito técnico.',
            'Extremo' => 'Modelo de gestão baseado no controle punitivo e na desconfiança, utilizando recursos tecnológicos para monitoramento invasivo do comportamento em vez de focar na segurança ou no suporte operacional.',
        ],
        'Civilidade e respeito' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Falta de treinamento em atendimento humanizado e pressão excessiva por produtividade (metas com prazos curtos), que levam à priorização da velocidade em detrimento da qualidade e da civilidade no trato com o público externo.',
            'Elevado' => 'Ambiente de trabalho com alta competitividade estimulada pela gestão, ausência de mediação de conflitos e falta de apoio social entre os pares, favorecendo um clima de hostilidade.',
            'Extremo' => 'Ausência de canais formais e seguros para reportar condutas desrespeitosas, fazendo com que situações de incivilidade não sejam tratadas ou corrigidas a tempo.',
        ],
        'Equilíbrio Trabalho / Vida' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Rigidez extrema nos cronogramas e horários de trabalho, aliada à falta de políticas de flexibilidade que permitam ao trabalhador conciliar imprevistos ou responsabilidades familiares com suas obrigações laborais.',
            'Elevado' => 'Cultura organizacional de hiperconectividade ou exigência de disponibilidade constante (ex: horas extras imprevistas), que impede o desligamento mental total e a recomposição fisiológica necessária nos períodos de folga.',
            'Extremo' => 'Cultura organizacional de hiperconectividade ou exigência de disponibilidade constante (ex: horas extras imprevistas), que impede o desligamento mental total e a recomposição fisiológica necessária nos períodos de folga.',
        ],
        'Violência no trabalho / Assédio / intimidações e vitimização' => [
            'Insignificante' => 'Não há evidência de risco de acordo com o perigo avaliado.',
            'Moderado' => 'Falta de diversidade na governança corporativa e ausência de critérios objetivos e imparciais para promoções, contratações e desligamentos, favorecendo vieses inconscientes ou conscientes.',
            'Elevado' => 'Modelos de gestão baseados no medo ou na coerção para o atingimento de metas, aliados à falta de treinamento comportamental para lideranças e colaboradores.',
            'Extremo' => 'Falha sistêmica na proteção da integridade biopsicossocial do trabalhador e ausência de programas de apoio psicológico ou canais de denúncia seguros e anônimos.',
        ],
    ];
    /**
     * Processa resultados de uma empresa.
     * Se $setor for informado, filtra apenas os respondentes daquele setor.
     */
    public function processar(int $idEmpresa, ?string $setor = null): array
    {
        $resultados = ResultadoRespostaArp::where('id_empresa', $idEmpresa)
            ->with(['pergunta.categoria.categoria', 'resposta', 'funcionario'])
            ->get();

        // ── Filtro por setor ──────────────────────────────────────────────
        if ($setor !== null && $setor !== '') {
            $resultados = $resultados->filter(function ($r) use ($setor) {
                $s = $r->funcionario->setor ?? 'Não informado';
                return $s === $setor;
            });
        }

        if ($resultados->isEmpty()) {
            return $this->emptyResult();
        }

        $participantes = $resultados->pluck('id_func')->unique()->count();

        $porCategoria = [];
        foreach ($resultados as $r) {
            if (!$r->resposta) continue;

            $categoria = $r->pergunta->categoria->categoria->nome
                       ?? $r->pergunta->categoria->nome
                       ?? 'Sem categoria';

            preg_match('/^\d+/', $r->resposta->resposta ?? '', $m);
            $valor = isset($m[0]) ? (int)$m[0] : null;
            if ($valor === null || $valor < 1 || $valor > 5) continue;

            $porCategoria[$categoria][] = $valor;
        }

        $categorias = [];
        foreach ($porCategoria as $nome => $valores) {
            $score = $this->calcularScore($valores);
            $nivel = $this->classificar($score);
            $categorias[] = [
                'nome'         => $nome,
                'score'        => round($score, 2),
                'score_pct'    => round(($score / 20) * 100, 1),
                'nivel'        => $nivel['label'],
                'codigo'       => $nivel['codigo'],
                'cor'          => $nivel['cor'],
                'respondentes' => count($valores),
                'recomendacao' => self::RECOMENDACOES[$nome] ?? 'Monitorar e revisar periodicamente.',
                'fonte_geradora' => self::FONTES_GERADORAS[$nome][$nivel['label']] ?? 'Não há evidência de risco de acordo com o perigo avaliado.',

            ];
        }

        usort($categorias, fn($a, $b) => $b['score'] <=> $a['score']);

        $maiorRisco = $categorias[0] ?? null;
        $scoreGeral = count($categorias) > 0
            ? round(array_sum(array_column($categorias, 'score')) / count($categorias), 2)
            : 0;
        $nivelGeral = $this->classificar($scoreGeral);

        $distribuicao = ['Extremo' => 0, 'Elevado' => 0, 'Moderado' => 0, 'Baixo' => 0, 'Insignificante' => 0];
        foreach ($categorias as $c) {
            $distribuicao[$c['nivel']] = ($distribuicao[$c['nivel']] ?? 0) + 1;
        }

        return [
            'categorias'    => $categorias,
            'participantes' => $participantes,
            'maior_risco'   => $maiorRisco,
            'score_geral'   => $scoreGeral,
            'nivel_geral'   => $nivelGeral,
            'distribuicao'  => $distribuicao,
            'radar_labels'  => array_column($categorias, 'nome'),
            'radar_scores'  => array_column($categorias, 'score_pct'),
            'radar_cores'   => array_column($categorias, 'cor'),
        ];
    }

    /**
     * Lista os setores que possuem respondentes ARP nesta empresa.
     */
    public function setoresDisponiveis(int $idEmpresa): array
    {
        return FuncionarioQuestionarioArp::where('id_empresa', $idEmpresa)
            ->whereNotNull('setor')
            ->where('setor', '!=', '')
            ->distinct()
            ->orderBy('setor')
            ->pluck('setor')
            ->toArray();
    }

    private function calcularScore(array $valores): float
    {
        if (empty($valores)) return 0;
        $soma = 0;
        foreach ($valores as $v) {
            $soma += self::PESOS[$v] ?? 0;
        }
        $media = $soma / count($valores);
        return ($media / 4.0) * 20;
    }

    public function classificar(float $score): array
    {
        // Bandas contíguas por limite inferior. NIVEIS está ordenado do maior
        // 'min' para o menor, então o primeiro match é a faixa correta.
        //
        // Antes o teste era ($score >= min && $score <= max) com min/max INTEIROS.
        // Como o score é float, qualquer valor nas lacunas — (4,5), (8,9),
        // (12,13), (16,17), ex.: 8.5 ou 12.7 — não caía em nenhuma faixa e
        // escorregava para o fallback self::NIVEIS[4] (Insignificante/cinza).
        // Era por isso que barras longas apareciam cinzas no relatório.
        foreach (self::NIVEIS as $nivel) {
            if ($score >= $nivel['min']) {
                return $nivel;
            }
        }
        return self::NIVEIS[array_key_last(self::NIVEIS)];
    }

    private function emptyResult(): array
    {
        return [
            'categorias'    => [],
            'participantes' => 0,
            'maior_risco'   => null,
            'score_geral'   => 0,
            'nivel_geral'   => self::NIVEIS[4],
            'distribuicao'  => ['Extremo' => 0, 'Elevado' => 0, 'Moderado' => 0, 'Baixo' => 0, 'Insignificante' => 0],
            'radar_labels'  => [],
            'radar_scores'  => [],
            'radar_cores'   => [],
        ];
    }
}
