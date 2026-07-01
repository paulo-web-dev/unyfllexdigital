<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlogGeneratorController extends Controller
{
    /** Tela do gerador. */
    public function form()
    {
        $this->authorize('admin.blog');
        $categories = BlogCategory::orderBy('sort_order')->orderBy('name')->get();
        $temas = self::temasSugeridos();
        return view('admin.blog.gerar', compact('categories', 'temas'));
    }

    /** Dispara a geração no n8n (que chama o Claude e devolve via callback). */
    public function gerar(Request $request)
    {
        $this->authorize('admin.blog');

        $data = $request->validate([
            'titulo'               => ['required', 'string', 'max:255'],
            'palavra_chave'        => ['required', 'string', 'max:180'],
            'palavras_secundarias' => ['nullable', 'string', 'max:500'],
            'blog_category_id'     => ['nullable', 'integer', 'exists:blog_categories,id'],
            'tamanho'              => ['nullable', 'integer', 'min:800', 'max:4000'],
            'instrucoes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $cat = ! empty($data['blog_category_id']) ? BlogCategory::find($data['blog_category_id']) : null;

        $ok = $this->dispararN8n([
            'titulo'               => $data['titulo'],
            'palavra_chave'        => $data['palavra_chave'],
            'palavras_secundarias' => $data['palavras_secundarias'] ?? '',
            'categoria_id'         => $cat?->id,
            'categoria_nome'       => $cat?->name,
            'minisserie_url'       => $cat?->minisserie_url,
            'tamanho'              => $data['tamanho'] ?? 1800,
            'instrucoes'           => $data['instrucoes'] ?? '',
            'callback_url'         => url('/api/n8n/blog/artigo'),
        ]);

        return redirect()
            ->route('admin.blog.posts.index')
            ->with(
                $ok ? 'success' : 'warning',
                $ok
                    ? 'Artigo sendo gerado pela IA — chega como rascunho em ~1 min (atualize a lista).'
                    : 'Não consegui acionar o n8n. Confira a URL do webhook do blog no config/.env.'
            );
    }

    /** Callback do n8n com o artigo pronto -> cria o post como RASCUNHO. */
    public function callback(Request $request)
    {
        $this->validarSecret($request);

        $data = $request->validate([
            'titulo'               => ['required', 'string', 'max:255'],
            'slug'                 => ['nullable', 'string', 'max:280'],
            'excerpt'              => ['nullable', 'string', 'max:500'],
            'conteudo_html'        => ['required', 'string'],
            'meta_title'           => ['nullable', 'string', 'max:255'],
            'meta_description'     => ['nullable', 'string', 'max:320'],
            'palavra_chave'        => ['nullable', 'string', 'max:180'],
            'palavras_secundarias' => ['nullable'],
            'faq'                  => ['nullable', 'array'],
            'tags'                 => ['nullable', 'array'],
            'categoria_id'         => ['nullable', 'integer'],
            'autor'                => ['nullable', 'string', 'max:150'],
        ]);

        $post = new BlogPost();
        $post->title            = $data['titulo'];
        $post->slug             = ! empty($data['slug']) ? Str::slug($data['slug']) : null; // model gera se null
        $post->excerpt          = $data['excerpt'] ?? null;
        $post->content          = $data['conteudo_html'];
        $post->meta_title       = $data['meta_title'] ?? null;
        $post->meta_description = $data['meta_description'] ?? null;
        $post->focus_keyword    = $data['palavra_chave'] ?? null;

        $sec = $data['palavras_secundarias'] ?? null;
        $post->secondary_keywords = is_array($sec) ? implode(', ', $sec) : $sec;

        $post->blog_category_id = $data['categoria_id'] ?? null;
        $post->author           = $data['autor'] ?? 'Equipe Unyflex';
        $post->source           = 'ia';
        $post->status           = 'rascunho';
        $post->published_at     = null;

        // FAQ: aceita {q,a} ou {pergunta,resposta}
        $faqClean = [];
        foreach ((array) ($data['faq'] ?? []) as $it) {
            $q = $it['q'] ?? $it['pergunta'] ?? null;
            $a = $it['a'] ?? $it['resposta'] ?? null;
            if ($q && $a) {
                $faqClean[] = ['q' => trim($q), 'a' => trim($a)];
            }
        }
        $post->faq = ! empty($faqClean)
            ? json_encode($faqClean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : null;

        $post->save();

        // tags (cria as que não existem)
        $ids = [];
        foreach ((array) ($data['tags'] ?? []) as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $tag = BlogTag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
            $ids[] = $tag->id;
        }
        $post->tags()->sync($ids);

        return response()->json([
            'ok'       => true,
            'post_id'  => $post->id,
            'edit_url' => route('admin.blog.posts.edit', $post),
        ]);
    }

    // ───────────────────────────── helpers ─────────────────────────────

    private function dispararN8n(array $payload): bool
    {
        $url = config('blog.n8n_webhook_url');
        if (empty($url)) {
            return false;
        }
        try {
            $resp = Http::withHeaders(['X-Webhook-Secret' => config('blog.n8n_secret')])
                ->timeout(20)
                ->post($url, $payload);
            return $resp->successful();
        } catch (\Throwable $e) {
            Log::warning('n8n blog: ' . $e->getMessage());
            return false;
        }
    }

    private function validarSecret(Request $request): void
    {
        $secret = (string) config('blog.n8n_secret');
        abort_unless(
            hash_equals($secret, (string) $request->header('X-Webhook-Secret')),
            401,
            'Secret invalido.'
        );
    }

    /** Os 40 temas do PLANO-SEO (sugestões no formulário). */
    public static function temasSugeridos(): array
    {
        return [
            // Licitações e Contratos
            ['t' => 'Pregão eletrônico passo a passo pela Lei 14.133', 'k' => 'pregão eletrônico', 'c' => 'licitacoes-e-contratos'],
            ['t' => 'Como elaborar o Estudo Técnico Preliminar (ETP)', 'k' => 'estudo técnico preliminar', 'c' => 'licitacoes-e-contratos'],
            ['t' => 'Termo de Referência: o que é e como fazer', 'k' => 'termo de referência', 'c' => 'licitacoes-e-contratos'],
            ['t' => 'Dispensa de licitação eletrônica: limites e procedimento', 'k' => 'dispensa de licitação', 'c' => 'licitacoes-e-contratos'],
            ['t' => 'Inexigibilidade de licitação: quando se aplica', 'k' => 'inexigibilidade de licitação', 'c' => 'licitacoes-e-contratos'],
            ['t' => 'Agente de contratação: funções, responsabilidades e designação', 'k' => 'agente de contratação', 'c' => 'licitacoes-e-contratos'],
            ['t' => 'Pesquisa de preços na Lei 14.133: como fazer corretamente', 'k' => 'pesquisa de preços', 'c' => 'licitacoes-e-contratos'],
            ['t' => 'Fiscalização e gestão de contratos administrativos', 'k' => 'fiscal de contrato', 'c' => 'licitacoes-e-contratos'],
            ['t' => 'PNCP: o que é e como publicar suas contratações', 'k' => 'pncp', 'c' => 'licitacoes-e-contratos'],
            // Controle Interno
            ['t' => 'Como estruturar o controle interno municipal', 'k' => 'controle interno municipal', 'c' => 'controle-interno-auditoria'],
            ['t' => 'Auditoria interna no setor público: guia prático', 'k' => 'auditoria interna setor público', 'c' => 'controle-interno-auditoria'],
            ['t' => 'Compliance público: o que é e como implementar', 'k' => 'compliance público', 'c' => 'controle-interno-auditoria'],
            ['t' => 'Governança pública municipal: pilares e boas práticas', 'k' => 'governança pública', 'c' => 'controle-interno-auditoria'],
            ['t' => 'Como se preparar para uma auditoria do Tribunal de Contas', 'k' => 'tribunal de contas auditoria', 'c' => 'controle-interno-auditoria'],
            // Transparência, LGPD e Ouvidoria
            ['t' => 'LGPD na prática para prefeituras', 'k' => 'lgpd prefeitura', 'c' => 'transparencia-lgpd-ouvidoria'],
            ['t' => 'e-SIC e Lei de Acesso à Informação: o que o município precisa', 'k' => 'e-sic', 'c' => 'transparencia-lgpd-ouvidoria'],
            ['t' => 'Ouvidoria pública: como implantar e gerir', 'k' => 'ouvidoria pública', 'c' => 'transparencia-lgpd-ouvidoria'],
            ['t' => 'Portal da Transparência: o que é obrigatório publicar', 'k' => 'portal da transparência', 'c' => 'transparencia-lgpd-ouvidoria'],
            ['t' => 'Encarregado de dados (DPO) no setor público', 'k' => 'encarregado de dados lgpd', 'c' => 'transparencia-lgpd-ouvidoria'],
            // Patrimônio, Frotas e Almoxarifado
            ['t' => 'Gestão patrimonial no setor público: guia completo', 'k' => 'gestão patrimonial pública', 'c' => 'patrimonio-frotas-almoxarifado'],
            ['t' => 'Controle de frotas na administração pública', 'k' => 'controle de frotas pública', 'c' => 'patrimonio-frotas-almoxarifado'],
            ['t' => 'Almoxarifado público: organização e controle de estoque', 'k' => 'almoxarifado público', 'c' => 'patrimonio-frotas-almoxarifado'],
            ['t' => 'Inventário de bens públicos: como fazer', 'k' => 'inventário de bens públicos', 'c' => 'patrimonio-frotas-almoxarifado'],
            ['t' => 'Depreciação de bens públicos: o que o servidor precisa saber', 'k' => 'depreciação bens públicos', 'c' => 'patrimonio-frotas-almoxarifado'],
            // IA e Tecnologia
            ['t' => 'Inteligência artificial aplicada a licitações', 'k' => 'inteligência artificial licitações', 'c' => 'ia-e-tecnologia-gestao-publica'],
            ['t' => 'Ferramentas de IA para servidores públicos', 'k' => 'ia para servidores públicos', 'c' => 'ia-e-tecnologia-gestao-publica'],
            ['t' => 'Como a IA ajuda na gestão de frotas públicas', 'k' => 'ia frotas públicas', 'c' => 'ia-e-tecnologia-gestao-publica'],
            ['t' => 'Transformação digital no setor público municipal', 'k' => 'transformação digital setor público', 'c' => 'ia-e-tecnologia-gestao-publica'],
            // Finanças, Tributos e Reforma
            ['t' => 'Reforma tributária e os municípios: o que muda', 'k' => 'reforma tributária municípios', 'c' => 'financas-tributos-reforma'],
            ['t' => 'IBS e CBS na prática municipal', 'k' => 'ibs cbs', 'c' => 'financas-tributos-reforma'],
            ['t' => 'Finanças públicas municipais: conceitos essenciais', 'k' => 'finanças públicas municipais', 'c' => 'financas-tributos-reforma'],
            ['t' => 'Tributos municipais: ISS, IPTU e ITBI explicados', 'k' => 'tributos municipais', 'c' => 'financas-tributos-reforma'],
            // Comunicação Pública
            ['t' => 'Assessoria de imprensa na gestão pública', 'k' => 'assessoria de imprensa pública', 'c' => 'comunicacao-publica'],
            ['t' => 'Redação oficial: guia para servidores', 'k' => 'redação oficial', 'c' => 'comunicacao-publica'],
            ['t' => 'Comunicação institucional nas redes sociais do município', 'k' => 'comunicação pública redes sociais', 'c' => 'comunicacao-publica'],
            ['t' => 'Oratória para servidores públicos', 'k' => 'oratória servidor público', 'c' => 'comunicacao-publica'],
            // Processo Legislativo
            ['t' => 'Processo legislativo municipal: como funciona', 'k' => 'processo legislativo municipal', 'c' => 'processo-legislativo-vereanca'],
            ['t' => 'A função fiscalizadora do vereador', 'k' => 'função fiscalizadora vereador', 'c' => 'processo-legislativo-vereanca'],
            ['t' => 'Técnica legislativa: como redigir projetos de lei', 'k' => 'técnica legislativa', 'c' => 'processo-legislativo-vereanca'],
        ];
    }
}
