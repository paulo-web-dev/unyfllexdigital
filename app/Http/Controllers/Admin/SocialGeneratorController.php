<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Models\SocialPostMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SocialGeneratorController extends Controller
{
    private const DIR = 'storage/social';

    /** Dispara a geração das peças de um dia para o n8n. */
    public function gerar(Request $request)
    {
        $this->authorize('admin.social');

        $data = $request->validate([
            'scheduled_date'      => ['required', 'date'],
            'items'               => ['required', 'array', 'min:1', 'max:8'],
            'items.*.tipo'        => ['required', 'in:story,feed'],
            'items.*.pedido'      => ['required', 'string', 'max:500'],
            'items.*.horario'     => ['required', 'date_format:H:i'],
            'items.*.objetivo'    => ['nullable', 'string', 'max:40'],
            'items.*.estilo'      => ['nullable', 'in:auto,dark,light'],
            'items.*.foto_real'   => ['nullable', 'in:sim,nao'],
            'items.*.categoria'   => ['nullable', 'string', 'max:60'],
            'items.*.tom'         => ['nullable', 'string', 'max:40'],
            'items.*.cta'         => ['nullable', 'string', 'max:60'],
            'items.*.elementos'   => ['nullable', 'array'],
            'items.*.elementos.*' => ['nullable', 'string', 'max:20'],
        ]);

        $account = SocialAccount::where('platform', 'instagram')->first();
        if (!$account) {
            return back()->with('warning', 'Cadastre a conta do Instagram antes de gerar.');
        }

        $items = [];
        foreach (array_values($data['items']) as $i => $it) {
            $items[] = [
                'ref'       => 'p' . ($i + 1),
                'tipo'      => $it['tipo'],
                'pedido'    => $it['pedido'],
                'horario'   => $it['horario'],
                'objetivo'  => $it['objetivo'] ?? '',
                'estilo'    => $it['estilo'] ?? 'auto',
                'foto_real' => (($it['foto_real'] ?? 'sim') === 'sim'),
                'categoria' => $it['categoria'] ?? '',
                'tom'       => $it['tom'] ?? '',
                'cta'       => $it['cta'] ?? '',
                'elementos' => array_values($it['elementos'] ?? []),
            ];
        }

        $payload = [
            'batch_id'       => (int) now()->timestamp,
            'scheduled_date' => $data['scheduled_date'],
            'callback_url'   => config('social.callback_url') ?: url('/api/n8n/social/artes'),
            'catalogo_url'   => config('social.catalogo_url'),
            'items'          => $items,
        ];

        $ok = $this->dispararN8n($payload);

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok
                ? 'Geração disparada — as artes chegam em 1-2 min e aparecem no dia (atualize a página).'
                : 'Não consegui acionar o n8n. Confira o webhook em config/social.php.'
        );
    }

    /** Recebe as artes renderizadas do n8n e cria os posts pré-agendados. */
    public function callback(Request $request)
    {
        $this->validarSecret($request);

        $data = $request->validate([
            'scheduled_date'        => ['required', 'date'],
            'artes'                 => ['required', 'array', 'min:1'],
            'artes.*.tipo'          => ['required', 'string'],
            'artes.*.horario'       => ['nullable', 'string'],
            'artes.*.caption'       => ['nullable', 'string'],
            'artes.*.first_comment' => ['nullable', 'string'],
            'artes.*.image_base64'  => ['nullable', 'string'],
        ]);

        $account = SocialAccount::where('platform', 'instagram')->first();
        if (!$account) {
            return response()->json(['ok' => false, 'error' => 'conta nao encontrada'], 404);
        }

        $dir = public_path(self::DIR);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $date = $data['scheduled_date'];
        $criados = 0;

        foreach ($data['artes'] as $a) {
            $bin = !empty($a['image_base64']) ? base64_decode($a['image_base64'], true) : false;
            if ($bin === false || strlen($bin) === 0) {
                continue;
            }

            $tipo = strtolower($a['tipo'] ?? 'feed');
            $type = $tipo === 'story' ? 'story' : 'feed_image';
            $horario = $a['horario'] ?? '09:00';

            try {
                $when = Carbon::parse($date . ' ' . $horario);
            } catch (\Throwable $e) {
                $when = Carbon::parse($date . ' 09:00');
            }

            $fname = 'gen-' . $date . '-' . Str::lower(Str::random(6)) . '.png';
            File::put($dir . '/' . $fname, $bin);

            $post = SocialPost::create([
                'social_account_id' => $account->id,
                'type'              => $type,
                'status'            => 'agendado',
                'caption'           => $a['caption'] ?? null,
                'first_comment'     => $a['first_comment'] ?? null,
                'scheduled_for'     => $when,
                'source'            => 'ia',
            ]);

            SocialPostMedia::create([
                'social_post_id' => $post->id,
                'path'           => self::DIR . '/' . $fname,
                'media_type'     => 'image',
                'sort_order'     => 1,
                'source'         => 'render',
                'created_at'     => now(),
            ]);

            $criados++;
        }

        return response()->json(['ok' => true, 'created' => $criados]);
    }

    // -------------------- helpers --------------------

    private function dispararN8n(array $payload): bool
    {
        $url = config('social.n8n_gerar_webhook');
        if (empty($url)) {
            return false;
        }
        try {
            $resp = Http::withHeaders(['X-Webhook-Secret' => config('social.n8n_secret')])
                ->timeout(20)
                ->post($url, $payload);
            return $resp->successful();
        } catch (\Throwable $e) {
            Log::warning('social gerar n8n: ' . $e->getMessage());
            return false;
        }
    }

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
