<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocialPublisherController extends Controller
{
    /**
     * Retorna os posts agendados já vencidos e os TRAVA como "publicando"
     * (na mesma transação) para não publicar duas vezes.
     * Chamado pelo n8n (Schedule) via POST com header X-Webhook-Secret.
     */
    public function due(Request $request)
    {
        $this->validarSecret($request);

        $account = SocialAccount::where('platform', 'instagram')->first();
        if (!$account || !$account->access_token || !$account->ig_user_id) {
            return response()->json(['posts' => []]);
        }

        $posts = [];

        DB::transaction(function () use ($account, &$posts) {
            $due = SocialPost::due()
                ->where('social_account_id', $account->id)
                ->whereIn('type', ['feed_image', 'story']) // reels/carrossel: fase futura
                ->orderBy('scheduled_for')
                ->lockForUpdate()
                ->limit(10)
                ->get();

            foreach ($due as $post) {
                $post->load('media');
                $media = $post->media->first();
                if (!$media) {
                    continue; // sem imagem não há o que publicar
                }

                $post->status = 'publicando';
                $post->save();

                $posts[] = [
                    'post_id'       => $post->id,
                    'ig_user_id'    => $account->ig_user_id,
                    'token'         => $account->access_token,
                    'type'          => $post->type,
                    'caption'       => (string) $post->caption,
                    'first_comment' => (string) $post->first_comment,
                    'image_url'     => $media->url(),
                ];
            }
        });

        return response()->json(['posts' => $posts]);
    }

    /**
     * Recebe do n8n o resultado da publicação de cada post e atualiza o status.
     * Sucesso -> publicado; falha -> volta a agendado (retry) ou falhou após 3 tentativas.
     */
    public function publicado(Request $request)
    {
        $this->validarSecret($request);

        $data = $request->validate([
            'resultados'                => ['required', 'array'],
            'resultados.*.post_id'      => ['required', 'integer'],
            'resultados.*.ok'           => ['required', 'boolean'],
            'resultados.*.ig_media_id'  => ['nullable', 'string'],
            'resultados.*.permalink'    => ['nullable', 'string'],
            'resultados.*.erro'         => ['nullable', 'string'],
        ]);

        $n = 0;
        foreach ($data['resultados'] as $r) {
            $post = SocialPost::find($r['post_id']);
            if (!$post) {
                continue;
            }

            if (!empty($r['ok']) && !empty($r['ig_media_id'])) {
                $post->status        = 'publicado';
                $post->ig_media_id   = $r['ig_media_id'];
                $post->permalink     = $r['permalink'] ?? null;
                $post->published_at  = now();
                $post->error_message = null;
            } else {
                $post->retry_count = (int) $post->retry_count + 1;
                $post->status = $post->retry_count >= 3 ? 'falhou' : 'agendado';
                $post->error_message = $r['erro'] ?? 'falha na publicacao';
            }

            $post->save();
            $n++;
        }

        return response()->json(['ok' => true, 'updated' => $n]);
    }

    // -------------------- helper --------------------

    private function validarSecret(Request $request): void
    {
        $secret = (string) config('social.n8n_secret');
        abort_unless(
            hash_equals($secret, (string) $request->header('X-Webhook-Secret')),
            401,
            'Secret invalido.'
        );
    }
}
