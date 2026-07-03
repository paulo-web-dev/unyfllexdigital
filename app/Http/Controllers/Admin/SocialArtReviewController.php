<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialArtDraft;
use App\Models\SocialPost;
use App\Models\SocialPostMedia;
use Illuminate\Support\Carbon;

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
}
