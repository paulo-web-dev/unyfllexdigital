<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialArtDraft;
use App\Models\SocialArtFeedback;
use App\Models\SocialPost;
use App\Models\SocialPostMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialArtReviewController extends Controller
{
    /** Fila de aprovação: artes em revisão (e as que ainda estão gerando). */
    public function index()
    {
        $this->authorize('admin.social');

        $drafts = SocialArtDraft::whereIn('status', ['revisao', 'gerando'])
            ->orderByDesc('id')
            ->get();

        return view('admin.social.review', compact('drafts'));
    }

    /** Aprova a arte: cria o post agendado a partir do rascunho. */
    public function aprovar(SocialArtDraft $draft)
    {
        $this->authorize('admin.social');

        if ($draft->status !== 'revisao' || !$draft->image_path) {
            return back()->with('warning', 'Esta arte ainda não está pronta para aprovação.');
        }

        $date = $draft->scheduled_date ? $draft->scheduled_date->format('Y-m-d') : now()->format('Y-m-d');
        try {
            $when = Carbon::parse($date . ' ' . ($draft->horario ?: '09:00'));
        } catch (\Throwable $e) {
            $when = Carbon::parse($date . ' 09:00');
        }

        $post = SocialPost::create([
            'social_account_id' => $draft->social_account_id,
            'type'              => $draft->tipo,
            'status'            => 'agendado',
            'caption'           => $draft->caption,
            'first_comment'     => $draft->first_comment,
            'scheduled_for'     => $when,
            'source'            => 'ia',
        ]);

        SocialPostMedia::create([
            'social_post_id' => $post->id,
            'path'           => $draft->image_path,
            'media_type'     => 'image',
            'sort_order'     => 1,
            'source'         => 'render',
            'created_at'     => now(),
        ]);

        $draft->status = 'aprovado';
        $draft->social_post_id = $post->id;
        $draft->save();

        return back()->with('success', 'Arte aprovada e agendada para ' . $when->format('d/m \à\s H:i') . '.');
    }

    /** Descarta a arte (não gera post). */
    public function descartar(SocialArtDraft $draft)
    {
        $this->authorize('admin.social');

        $draft->status = 'reprovado';
        $draft->save();

        return back()->with('success', 'Arte descartada.');
    }

    /** Reprova com feedback e dispara a regeneração (nova versão) no n8n. */
    public function reprovar(Request $request, SocialArtDraft $draft)
    {
        $this->authorize('admin.social');

        if ($draft->status !== 'revisao') {
            return back()->with('warning', 'Só é possível refazer artes que estão em revisão.');
        }

        $data = $request->validate([
            'nao_gostou'     => ['required', 'string', 'max:1000'],
            'mudanca_pedida' => ['required', 'string', 'max:1000'],
        ], [], [
            'nao_gostou'     => 'o que não ficou bom',
            'mudanca_pedida' => 'o que mudar',
        ]);

        // Guarda o feedback desta versão (histórico que a IA usa para não repetir erros).
        SocialArtFeedback::create([
            'social_art_draft_id' => $draft->id,
            'versao'              => $draft->versao,
            'nao_gostou'          => $data['nao_gostou'],
            'mudanca_pedida'      => $data['mudanca_pedida'],
            'created_at'          => now(),
        ]);

        // Incrementa a versão e volta para "gerando".
        $draft->versao = $draft->versao + 1;
        $draft->status = 'gerando';
        $draft->save();

        $ok = $this->dispararRegeneracao($draft);

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok
                ? 'Feedback enviado — a nova versão (v' . $draft->versao . ') está sendo gerada e volta para a fila em 1-2 min.'
                : 'Feedback salvo, mas não consegui acionar o n8n. Verifique o webhook em config/social.php.'
        );
    }

    /** Dispara a regeneração de uma peça para o n8n (mesmo webhook, com arte anterior + feedbacks). */
    private function dispararRegeneracao(SocialArtDraft $draft): bool
    {
        $url = config('social.n8n_gerar_webhook');
        if (empty($url)) {
            return false;
        }

        $feedbacks = $draft->feedbacks()->orderBy('id')->get()
            ->map(fn ($f) => [
                'nao_gostou'     => $f->nao_gostou,
                'mudanca_pedida' => $f->mudanca_pedida,
            ])
            ->values()
            ->all();

        // Reaproveita o briefing original e acrescenta o contexto de revisão.
        $item = array_merge($draft->briefing ?? [], [
            'ref'           => $draft->ref,
            'tipo'          => $draft->tipo === 'story' ? 'story' : 'feed_image',
            'pedido'        => $draft->pedido,
            'regenerar'     => true,
            'versao'        => $draft->versao,
            'html_anterior' => $draft->html,
            'feedbacks'     => $feedbacks,
        ]);

        $payload = [
            'batch_id'       => $draft->batch_id,
            'scheduled_date' => optional($draft->scheduled_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'callback_url'   => config('social.callback_url') ?: url('/api/n8n/social/artes'),
            'catalogo_url'   => config('social.catalogo_url'),
            'items'          => [$item],
        ];

        try {
            $resp = Http::withHeaders(['X-Webhook-Secret' => config('social.n8n_secret')])
                ->timeout(20)
                ->post($url, $payload);
            return $resp->successful();
        } catch (\Throwable $e) {
            Log::warning('social regenerar n8n: ' . $e->getMessage());
            return false;
        }
    }
}
