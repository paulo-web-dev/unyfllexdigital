<?php

/*
|--------------------------------------------------------------------------
| Vitrine pública da Assinatura Premium Unyflex (/assinatura)
|--------------------------------------------------------------------------
| Público: órgãos públicos (CNPJ) — câmaras e prefeituras que compram por nota de empenho.
| Todo valor, número de contato, mensagem e texto de contratação da vitrine vem daqui.
| Números do catálogo (total de cursos, apostilas, categorias) NÃO ficam aqui: são
| calculados pelo AssinaturaVitrineService a partir do catálogo real (com cache).
|
| Marcadores:
|  [CONFIRMAR]  texto provisório que o comercial/jurídico precisa revisar;
|  [PREENCHER]  informação que ainda falta.
*/

return [

    'produto' => 'Assinatura Premium Unyflex',

    // ── WhatsApp (CTA único da vitrine) ─────────────────────────────────────
    // Número exclusivo da vitrine. A renovação da área do assinante usa config('assinante.whatsapp_comercial').
    'whatsapp' => env('VITRINE_WHATSAPP', '5541997587226'),

    'mensagens' => [
        'padrao'      => 'Olá! Gostaria de saber mais sobre a Assinatura Premium da Unyflex para minha instituição.',
        'plano'       => 'Olá! Tenho interesse na Assinatura Premium Unyflex - :plano. Pode me enviar a proposta?',
        'curso'       => 'Olá! Tenho interesse no curso ":curso" pela Assinatura Premium da Unyflex para minha instituição.',
        'categoria'   => 'Olá! Tenho interesse nos cursos de :categoria da Assinatura Premium da Unyflex para minha instituição.',
        'sem_tema'    => 'Olá! Não encontrei o tema que procuro no catálogo da Assinatura Premium Unyflex. Podem me ajudar?',
        'sob_medida'  => 'Olá! Somos um órgão com :servidores servidores para capacitar e gostaríamos de uma proposta sob medida da Assinatura Premium Unyflex.',
        'calculadora' => 'Olá! Fiz a simulação no site (servidores: :servidores; cursos por servidor no ano: :cursos). Pode me enviar a proposta da Assinatura Premium Unyflex?',
    ],

    // ── Rastreamento ────────────────────────────────────────────────────────
    // Único Meta Pixel da vitrine. Os CTAs disparam fbq('track','Contact') — nunca Lead no clique.
    'meta_pixel' => env('VITRINE_META_PIXEL', '1168799437651546'),
    'google_ads' => env('VITRINE_GOOGLE_ADS', 'AW-18192995141'),
    // Container verificado em 2026-10-06: publicado sem tags (não dispara o pixel 1614520382942883).
    'gtm'        => env('VITRINE_GTM', 'GTM-K7L7LBLS'),

    // ── Âncora: transmissão ao vivo avulsa ─────────────────────────────────
    'avulsa' => [
        'nome'        => 'Transmissão ao vivo avulsa',
        'preco'       => 2000,                       // por servidor, por curso (base da calculadora)
        'unidade'     => 'por servidor, por curso',  // [CONFIRMAR] por servidor/inscrição ou por órgão
        'acesso'      => '[PREENCHER] só ao vivo / gravação por X meses',
        'certificados'=> '1 (do curso assistido)',
        'materiais'   => '[PREENCHER] material do curso',
    ],

    // ── Planos (sempre anual, à vista) ───────────────────────────────────────
    // `preco_de` = preço cheio (usuarios × individual). Economia, % e valor por usuário são calculados.
    'planos' => [
        'individual' => [
            'nome'     => 'Individual',
            'rotulo'   => 'Individual — 1 usuário',
            'usuarios' => 1,
            'preco'    => 2490,
            'preco_de' => null,
            'selo'     => null,
            'destaque' => false,
            'chamada'  => 'Para o servidor que precisa de capacitação contínua.',
        ],
        'corporativo5' => [
            'nome'     => 'Corporativo 5',
            'rotulo'   => 'Corporativo 5 usuários',
            'usuarios' => 5,
            'preco'    => 11205,
            'preco_de' => 12450,
            'selo'     => null, // usa "Economize R$ X" calculado
            'destaque' => false,
            'chamada'  => 'Para setores e equipes pequenas.',
        ],
        'corporativo10' => [
            'nome'     => 'Corporativo 10',
            'rotulo'   => 'Corporativo 10 usuários',
            'usuarios' => 10,
            'preco'    => 19920,
            'preco_de' => 24900,
            'selo'     => 'Mais vantajoso',
            'destaque' => true,
            'chamada'  => 'Menos que 1 transmissão avulsa por servidor',
        ],
    ],

    // Itens comuns a todos os planos (o catálogo e as apostilas entram com o número real).
    'recursos_planos' => [
        ':cursos cursos no catálogo durante 12 meses',
        ':apostilas apostilas e materiais de pós-graduação',
        'Certificados ilimitados com carga horária',
        'Novos cursos incluídos durante a vigência',
        'Transmissões ao vivo incluídas [CONFIRMAR]',
        'Faculdade credenciada pelo MEC',
        'Aceita nota de empenho e emite NF para órgão público',
    ],

    // ── Selos (componente <x-vitrine.selos>) ────────────────────────────────
    // `:cursos` é trocado pelo número de marketing calculado (ex.: "+450").
    'selos' => [
        'mec'      => ['icone' => 'graduation-cap', 'texto' => 'Faculdade credenciada pelo MEC'],
        'empenho'  => ['icone' => 'file-check-2',   'texto' => 'Aceita nota de empenho'],
        'nf'       => ['icone' => 'receipt',        'texto' => 'Emissão de NF para órgão público'],
        'carga'    => ['icone' => 'award',          'texto' => 'Certificado com carga horária'],
        'catalogo' => ['icone' => 'library',        'texto' => ':cursos cursos no catálogo'],
        'vigencia' => ['icone' => 'sparkles',       'texto' => 'Novos cursos incluídos durante a vigência'],
        'aovivo'   => ['icone' => 'radio',          'texto' => 'Transmissões ao vivo'],
    ],

    // Selos do hero da home (no máximo 3, uma linha). Os demais aparecem nos planos e na contratação.
    'selos_hero' => ['mec', 'carga', 'aovivo'],

    // Badge do card de curso na vitrine (por tipo do catálogo).
    'badges' => [
        'minisserie' => 'Curso Minissérie',
        'gravado'    => 'Curso Modular',
        'livre'      => 'Curso Livre Aprofundado',
        'modular'    => 'Apostila',
    ],

    // ── "Por dentro da plataforma" (home e planos) ─────────────────────────
    // Vazio = a seção não aparece. largura/altura evitam salto de layout enquanto a imagem carrega.
    'plataforma_imagens' => [
        ['url' => 'https://unyflex.com.br/storage/fav/homeassinatura.jpeg',    'legenda' => 'Área do assinante',      'largura' => 1365, 'altura' => 595], // [CONFIRMAR] legenda
        ['url' => 'https://unyflex.com.br/storage/fav/auladiretoaoponto.jpeg', 'legenda' => 'Aulas diretas ao ponto', 'largura' => 1365, 'altura' => 588], // [CONFIRMAR] legenda
    ],

    // ── Categorias ──────────────────────────────────────────────────────────
    // Slugs de categories. Vazio = as 6 com mais cursos.
    'categorias_destaque' => [],

    'icones_categoria' => [
        'advogados-municipais'   => 'scale',
        'ambiente-e-urbanismo'   => 'trees',
        'ano-eleitoral'          => 'vote',
        'assessoria'             => 'briefcase',
        'comunicacao'            => 'megaphone',
        'controle-interno'       => 'shield-check',
        'financas-municipais'    => 'landmark',
        'legislativo'            => 'gavel',
        'licitacoes-publicas'    => 'file-text',
        'obras'                  => 'hard-hat',
        'patrimonio'             => 'building-2',
        'pregoeiros'             => 'hammer',
        'rh-municipal'           => 'users',
        'secretarias-municipais' => 'building',
        'lgpd'                   => 'lock',
        'tributacao-municipal'   => 'calculator',
        'pos-graduacao'          => 'book-open',
    ],

    // Categoria virtual para modular_courses (que não têm categoria no banco).
    'categoria_apostilas' => [
        'slug'   => 'pos-graduacao',
        'titulo' => 'Apostilas e Materiais Pós-Graduação',
    ],

    // Capa das turmas (classes.photo), servida pelo unyflex.com.br.
    'capa_turma_base' => env('VITRINE_CAPA_BASE', 'https://unyflex.com.br/storage/cursos/banner/'),

    'cursos_por_carrossel' => 12,
    // 60 min. O agendador roda vitrine:aquecer-cache a cada 50 min e regrava antes de expirar.
    'cache_ttl'            => 3600,

    // ── Como contratar pelo seu órgão ───────────────────────────────────────
    'passos' => [
        ['icone' => 'message-circle', 'titulo' => 'Solicite a proposta',          'texto' => 'Fale com um consultor pelo WhatsApp e informe quantos servidores serão capacitados.'],
        ['icone' => 'file-stack',     'titulo' => 'Receba proposta e documentos', 'texto' => 'Enviamos a proposta comercial e a documentação de habilitação para o cadastro de fornecedor. [CONFIRMAR]'],
        ['icone' => 'stamp',          'titulo' => 'Emita a nota de empenho',      'texto' => 'O órgão emite a nota de empenho conforme o seu procedimento interno. [CONFIRMAR]'],
        ['icone' => 'key-round',      'titulo' => 'Acessos liberados',            'texto' => 'Liberamos o acesso de cada servidor à plataforma. [CONFIRMAR prazo]'],
    ],

    // ── FAQ do gestor público ───────────────────────────────────────────────
    // Respostas provisórias. Não afirmar nada jurídico (dispensa, inexigibilidade) sem validação.
    'faq' => [
        ['p' => 'A Unyflex aceita nota de empenho?',
         'r' => '[CONFIRMAR] Sim. A contratação pode ser formalizada por nota de empenho emitida pelo órgão.'],
        ['p' => 'Vocês emitem nota fiscal para órgão público?',
         'r' => '[CONFIRMAR] Sim. A nota fiscal é emitida em nome do órgão (CNPJ) após a emissão do empenho.'],
        ['p' => 'Vocês enviam a documentação para o cadastro de fornecedor?',
         'r' => '[CONFIRMAR] Sim. Enviamos a documentação de habilitação junto com a proposta. Quais documentos: [PREENCHER].'],
        ['p' => 'Posso trocar o servidor de uma licença?',
         'r' => '[CONFIRMAR] Regra de troca de usuário durante a vigência: [PREENCHER].'],
        ['p' => 'O certificado tem carga horária?',
         'r' => '[CONFIRMAR] Sim. Cada curso concluído com aproveitamento gera certificado com carga horária, emitido pela faculdade credenciada pelo MEC.'],
        ['p' => 'Como é feita a liberação dos acessos?',
         'r' => '[CONFIRMAR] Após o empenho, cada servidor recebe login e senha por e-mail para acessar a área do assinante. Prazo: [PREENCHER].'],
        ['p' => 'A assinatura é mensal ou anual?',
         'r' => 'Anual, com pagamento à vista. O acesso vale por 12 meses a partir da liberação. [CONFIRMAR]'],
    ],

    'contato' => [
        'email'    => 'atendimento@unyflex.com.br',
        'telefone' => '(41) 99758-7226',
    ],
];
