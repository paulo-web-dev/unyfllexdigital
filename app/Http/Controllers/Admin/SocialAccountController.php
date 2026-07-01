<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SocialAccountController extends Controller
{
    /** Tela da conta do Instagram (dados + status do token). */
    public function index()
    {
        $this->authorize('admin.social');
        $account = SocialAccount::where('platform', 'instagram')->first();
        return view('admin.social.accounts.index', compact('account'));
    }

    /** Salva os dados da conta e, se enviado, o token da Página. */
    public function update(Request $request)
    {
        $this->authorize('admin.social');

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:120'],
            'ig_user_id'   => ['required', 'string', 'max:40'],
            'fb_page_id'   => ['nullable', 'string', 'max:40'],
            'access_token' => ['nullable', 'string'],
            'token_days'   => ['nullable', 'integer', 'min:1', 'max:120'],
        ]);

        $account = SocialAccount::where('platform', 'instagram')->first()
            ?? new SocialAccount(['platform' => 'instagram']);

        $account->platform   = 'instagram';
        $account->name       = $data['name'];
        $account->ig_user_id = $data['ig_user_id'];
        $account->fb_page_id = $data['fb_page_id'] ?? null;

        // Só troca o token quando um novo é colado (salvar em branco mantém o atual).
        if (!empty($data['access_token'])) {
            $account->access_token     = trim($data['access_token']);
            $account->token_expires_at = Carbon::now()->addDays($data['token_days'] ?? 60);
        }

        $account->status = 'ativo';
        $account->save();

        return redirect()
            ->route('admin.social.accounts.index')
            ->with('success', 'Conta salva com sucesso.');
    }
}
