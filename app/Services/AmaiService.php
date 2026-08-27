<?php

namespace App\Services;

use App\Models\AccessLog;
use App\Models\AmaiVinculo;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\User;
use App\Models\ViewsMinisserie;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Regras da estrutura AMAI (exclusiva desse consórcio — sem generalização).
 *
 *  - Master: vê tudo, cadastra/remove pontos focais, ajusta cota, cadastra usuários sob
 *    qualquer ponto focal sem ser barrado pela cota (o usuário ocupa vaga do ponto focal).
 *  - Ponto focal: um por município; cadastra/remove só os usuários que ele mesmo criou;
 *    cota padrão de 14 VAGAS além dele (remover libera vaga).
 *  - Usuário: assinante comum (plano AMAI).
 *
 * A tabela amai_vinculos é criada por SQL manual. Enquanto não existir, instalado() devolve
 * false e a área /amai/* degrada sem erro.
 */
class AmaiService
{
    public const COTA_PADRAO = 14;

    public const MUNICIPIOS = [
        'Abelardo Luz', 'Bom Jesus', 'Entre Rios', 'Faxinal dos Guedes', 'Ipuaçu',
        'Lajeado Grande', 'Marema', 'Ouro Verde', 'Passos Maia', 'Ponte Serrada',
        'São Domingos', 'Vargeão', 'Xanxerê', 'Xaxim',
    ];

    private static ?bool $instalado = null;

    /** True se a tabela amai_vinculos existe (checada por consulta, memo por request). */
    public static function instalado(): bool
    {
        if (self::$instalado !== null) {
            return self::$instalado;
        }
        try {
            DB::table('amai_vinculos')->select('id')->limit(1)->get();
            return self::$instalado = true;
        } catch (\Throwable $e) {
            return self::$instalado = false;
        }
    }

    /** Vínculo ativo do usuário (ou null). */
    public function vinculoDe(?User $user): ?AmaiVinculo
    {
        if (! $user || ! self::instalado()) {
            return null;
        }
        return AmaiVinculo::ativos()->where('user_id', $user->id)->first();
    }

    /** Papel ativo ('master' | 'ponto_focal' | 'usuario') ou null. */
    public function papel(?User $user): ?string
    {
        return $this->vinculoDe($user)?->papel;
    }

    /** True se o usuário pode entrar na área de gestão (master ou ponto focal). */
    public function gestor(?User $user): bool
    {
        return in_array($this->papel($user), [AmaiVinculo::MASTER, AmaiVinculo::PONTO_FOCAL], true);
    }

    // ══════════════════════════════════════════════════════════════════════
    // Leitura
    // ══════════════════════════════════════════════════════════════════════

    /** Pontos focais ativos com vagas calculadas. */
    public function pontosFocais(): Collection
    {
        $focais = AmaiVinculo::ativos()->pontosFocais()->with('user')->orderBy('municipio')->get();
        $usados = AmaiVinculo::ativos()->usuarios()
            ->whereIn('parent_user_id', $focais->pluck('user_id'))
            ->selectRaw('parent_user_id, COUNT(*) AS n')->groupBy('parent_user_id')->pluck('n', 'parent_user_id');

        return $focais->each(function (AmaiVinculo $f) use ($usados) {
            $f->setAttribute('vagas_cota',   $f->cota ?? self::COTA_PADRAO);
            $f->setAttribute('vagas_usadas', (int) ($usados[$f->user_id] ?? 0));
            $f->setAttribute('vagas_livres', max(0, ($f->cota ?? self::COTA_PADRAO) - (int) ($usados[$f->user_id] ?? 0)));
        });
    }

    /** Vagas de um ponto focal: ['cota','usadas','livres']. */
    public function vagas(AmaiVinculo $focal): array
    {
        $cota   = $focal->cota ?? self::COTA_PADRAO;
        $usadas = AmaiVinculo::ativos()->usuarios()->where('parent_user_id', $focal->user_id)->count();
        return ['cota' => $cota, 'usadas' => $usadas, 'livres' => max(0, $cota - $usadas)];
    }

    /** Usuários ativos de um ponto focal. */
    public function usuariosDe(AmaiVinculo $focal): Collection
    {
        return AmaiVinculo::ativos()->usuarios()->where('parent_user_id', $focal->user_id)
            ->with('user')->orderBy('id')->get();
    }

    /** Todos os usuários ativos (master), com o ponto focal de cada um. */
    public function todosUsuarios(?string $municipio = null): Collection
    {
        $q = AmaiVinculo::ativos()->usuarios()->with(['user', 'pontoFocal'])->orderBy('municipio')->orderBy('id');
        if ($municipio) {
            $q->where('municipio', $municipio);
        }
        return $q->get();
    }

    /** Último acesso e resumo de uso por users.id (uma query por tabela, sem N+1). */
    public function resumoUso(Collection $userIds): array
    {
        $ids = $userIds->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }
        $students = User::whereIn('id', $ids)->pluck('student_id', 'id');

        $logins = AccessLog::whereIn('student_id', $students->values())->where('action', 'login')
            ->selectRaw('student_id, MAX(created_at) AS ultimo, COUNT(*) AS n')->groupBy('student_id')->get()->keyBy('student_id');
        $views = ViewsMinisserie::whereIn('id_user', $ids)
            ->selectRaw('id_user, COUNT(DISTINCT video_id) AS aulas, MAX(updated_at) AS ultimo')->groupBy('id_user')->get()->keyBy('id_user');

        $out = [];
        foreach ($ids as $uid) {
            $sid = $students[$uid] ?? null;
            $out[$uid] = [
                'ultimo_login' => $sid && isset($logins[$sid]) ? $logins[$sid]->ultimo : null,
                'logins'       => $sid && isset($logins[$sid]) ? (int) $logins[$sid]->n : 0,
                'aulas'        => isset($views[$uid]) ? (int) $views[$uid]->aulas : 0,
                'ultima_aula'  => isset($views[$uid]) ? $views[$uid]->ultimo : null,
            ];
        }
        return $out;
    }

    /** Histórico detalhado de um usuário (master): logins e cursos acessados. */
    public function historico(AmaiVinculo $v): array
    {
        $sid = $v->user?->student_id;
        return [
            'logins' => $sid ? AccessLog::where('student_id', $sid)->where('action', 'login')->orderByDesc('created_at')->limit(30)->get() : collect(),
            'cursos' => $sid ? AccessLog::where('student_id', $sid)->where('action', 'curso_view')->whereNotNull('detail')
                ->selectRaw('detail, COUNT(*) AS vezes, MAX(created_at) AS ultimo')->groupBy('detail')->orderByDesc('ultimo')->get() : collect(),
            'aulas'  => ViewsMinisserie::where('id_user', $v->user_id)->with(['video', 'classes'])->orderByDesc('updated_at')->limit(30)->get(),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // Escrita
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Cadastra um usuário sob um ponto focal. Ator = master ou o próprio ponto focal.
     * Ponto focal é barrado pela cota; master não (mas o usuário ocupa vaga).
     */
    public function cadastrarUsuario(User $ator, AmaiVinculo $atorVinculo, AmaiVinculo $focal, array $dados): AmaiVinculo
    {
        if (! $focal->isPontoFocal()) {
            throw ValidationException::withMessages(['focal' => 'Ponto focal inválido.']);
        }
        if ($atorVinculo->isPontoFocal() && $atorVinculo->user_id !== $focal->user_id) {
            throw ValidationException::withMessages(['focal' => 'Você só pode cadastrar usuários no seu próprio município.']);
        }

        return DB::transaction(function () use ($ator, $atorVinculo, $focal, $dados) {
            // Trava o ponto focal para a checagem de cota não sofrer corrida.
            $focalLock = AmaiVinculo::whereKey($focal->id)->lockForUpdate()->first();
            $vagas = $this->vagas($focalLock);
            if ($atorVinculo->isPontoFocal() && $vagas['livres'] <= 0) {
                throw ValidationException::withMessages(['cota' => "Cota esgotada: {$vagas['usadas']} de {$vagas['cota']} vagas em uso. Remova um usuário para liberar vaga ou peça ampliação ao master."]);
            }

            $user = $this->garantirConta($dados);

            $existente = AmaiVinculo::where('user_id', $user->id)->first();
            if ($existente && ! $existente->removed_at) {
                throw ValidationException::withMessages(['email' => 'Esta pessoa já faz parte da estrutura AMAI.']);
            }

            $this->garantirAssinatura($user, $ator, $this->validadeDoFocal($focalLock));

            $attrs = [
                'papel'          => AmaiVinculo::USUARIO,
                'municipio'      => $focalLock->municipio,
                'parent_user_id' => $focalLock->user_id,
                'cota'           => null,
                'created_by'     => $ator->id,
                'removed_at'     => null,
                'removed_by'     => null,
            ];
            if ($existente) {
                $existente->fill($attrs)->save();
                return $existente;
            }
            return AmaiVinculo::create($attrs + ['user_id' => $user->id]);
        });
    }

    /** Remove um usuário: encerra a assinatura AMAI (cancelado) e marca o vínculo. Não apaga histórico. */
    public function removerUsuario(User $ator, AmaiVinculo $alvo): void
    {
        if (! $alvo->isUsuario() || $alvo->removed_at) {
            throw ValidationException::withMessages(['alvo' => 'Usuário inválido.']);
        }
        DB::transaction(function () use ($ator, $alvo) {
            $this->encerrarAssinatura($alvo->user);
            $alvo->forceFill(['removed_at' => now(), 'removed_by' => $ator->id])->save();
        });
    }

    /** Master: cadastra (ou vincula conta existente como) ponto focal de um município. */
    public function cadastrarPontoFocal(User $master, array $dados): AmaiVinculo
    {
        if (! in_array($dados['municipio'], self::MUNICIPIOS, true)) {
            throw ValidationException::withMessages(['municipio' => 'Município fora da lista da AMAI.']);
        }
        if (AmaiVinculo::ativos()->pontosFocais()->where('municipio', $dados['municipio'])->exists()) {
            throw ValidationException::withMessages(['municipio' => 'Este município já tem ponto focal ativo. Remova-o antes de cadastrar outro.']);
        }

        return DB::transaction(function () use ($master, $dados) {
            $user = $this->garantirConta($dados);
            $existente = AmaiVinculo::where('user_id', $user->id)->first();
            if ($existente && ! $existente->removed_at) {
                throw ValidationException::withMessages(['email' => 'Esta pessoa já faz parte da estrutura AMAI.']);
            }
            $this->garantirAssinatura($user, $master, now()->addYear()->toDateString());

            $attrs = [
                'papel'          => AmaiVinculo::PONTO_FOCAL,
                'municipio'      => $dados['municipio'],
                'parent_user_id' => null,
                'cota'           => null,
                'created_by'     => $master->id,
                'removed_at'     => null,
                'removed_by'     => null,
            ];
            if ($existente) {
                $existente->fill($attrs)->save();
                return $existente;
            }
            return AmaiVinculo::create($attrs + ['user_id' => $user->id]);
        });
    }

    /** Master: remove um ponto focal (encerra a assinatura dele; usuários dele continuam ativos). */
    public function removerPontoFocal(User $master, AmaiVinculo $alvo): void
    {
        if (! $alvo->isPontoFocal() || $alvo->removed_at) {
            throw ValidationException::withMessages(['alvo' => 'Ponto focal inválido.']);
        }
        DB::transaction(function () use ($master, $alvo) {
            $this->encerrarAssinatura($alvo->user);
            $alvo->forceFill(['removed_at' => now(), 'removed_by' => $master->id])->save();
        });
    }

    /** Master: altera a cota de um ponto focal (não pode ficar abaixo das vagas em uso). */
    public function alterarCota(AmaiVinculo $focal, int $cota): void
    {
        if (! $focal->isPontoFocal() || $focal->removed_at) {
            throw ValidationException::withMessages(['alvo' => 'Ponto focal inválido.']);
        }
        $usadas = $this->vagas($focal)['usadas'];
        if ($cota < $usadas) {
            throw ValidationException::withMessages(['cota' => "A cota não pode ser menor que as {$usadas} vagas já em uso."]);
        }
        $focal->forceFill(['cota' => $cota])->save();
    }

    // ══════════════════════════════════════════════════════════════════════
    // Internos
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Garante students + users para a pessoa (reaproveita conta existente pelo e-mail ou CPF).
     * Senha inicial = CPF só números (padrão do sistema, igual ao checkout).
     */
    private function garantirConta(array $d): User
    {
        $email = strtolower(trim($d['email']));
        $cpf   = preg_replace('/\D/', '', $d['cpf'] ?? '');
        $nome  = trim($d['nome']);
        $cargo = trim((string) ($d['cargo'] ?? '')) ?: null;

        $user = User::where('email', $email)->first();

        if ($user) {
            if ($user->power >= 13) {
                throw ValidationException::withMessages(['email' => 'Este e-mail pertence a uma conta administrativa da Unyflex e não pode entrar na estrutura AMAI.']);
            }
            if ($user->cpf && $user->cpf !== $cpf) {
                throw ValidationException::withMessages(['cpf' => 'Já existe uma conta com este e-mail e outro CPF. Confira os dados.']);
            }
            if (! $user->student_id) {
                $student = Student::where('cpf', $cpf)->first() ?: $this->criarStudent($nome, $email, $cpf, $cargo);
                $user->student_id = $student->id;
            }
            if (! $user->cpf) {
                $user->cpf = $cpf;
            }
            if ($cargo && ! $user->funcao) {
                $user->funcao = $cargo;
            }
            $user->save();
            return $user;
        }

        $student = Student::where('cpf', $cpf)->first();
        if ($student && $student->email && strtolower($student->email) !== $email
            && User::where('student_id', $student->id)->exists()) {
            throw ValidationException::withMessages(['cpf' => 'Este CPF já está cadastrado com outro e-mail. Use o e-mail da conta existente.']);
        }
        if (! $student) {
            $student = $this->criarStudent($nome, $email, $cpf, $cargo);
        }

        return User::create([
            'name'       => $nome,
            'email'      => $email,
            'cpf'        => $cpf,
            'password'   => Hash::make($cpf),
            'student_id' => $student->id,
            'funcao'     => $cargo,
            'setor'      => 'AMAI',
            'power'      => 1,
        ]);
    }

    private function criarStudent(string $nome, string $email, string $cpf, ?string $cargo): Student
    {
        return Student::create([
            'name'       => $nome,
            'email'      => $email,
            'cpf'        => $cpf,
            'password'   => Hash::make($cpf),
            'status'     => 'able',
            'minisserie' => '1',
            'cargo'      => $cargo,
            'entidade'   => 'AMAI',
        ]);
    }

    /** Garante uma assinatura AMAI vigente para o usuário (reativa/estende se já houver). */
    private function garantirAssinatura(User $user, User $criador, string $endDate): void
    {
        $vigente = Subscription::where('student_id', $user->student_id)->vigentes()->orderByDesc('end_date')->first();
        if ($vigente) {
            if ($vigente->plano !== Subscription::PLANO_AMAI) {
                // Já era assinante por outro plano: passa a contar como AMAI sem encurtar validade.
                $vigente->plano = Subscription::PLANO_AMAI;
                $vigente->save();
            }
            return;
        }
        Subscription::create([
            'student_id' => $user->student_id,
            'status'     => 'ativo',
            'start_date' => now()->toDateString(),
            'end_date'   => $endDate,
            'plano'      => Subscription::PLANO_AMAI,
            'observacao' => 'Cadastrado pela estrutura AMAI',
            'created_by' => $criador->id,
        ]);
    }

    /** Encerra as assinaturas AMAI vigentes do usuário (status cancelado, fim = hoje). */
    private function encerrarAssinatura(?User $user): void
    {
        if (! $user || ! $user->student_id) {
            return;
        }
        Subscription::where('student_id', $user->student_id)->amai()->where('status', 'ativo')
            ->update(['status' => 'cancelado', 'end_date' => now()->toDateString(), 'updated_at' => now()]);
    }

    /** Validade dos usuários = validade da assinatura do ponto focal; senão 1 ano. */
    private function validadeDoFocal(AmaiVinculo $focal): string
    {
        $sub = $focal->user?->student_id
            ? Subscription::where('student_id', $focal->user->student_id)->vigentes()->orderByDesc('end_date')->first()
            : null;
        return $sub?->end_date?->toDateString() ?: now()->addYear()->toDateString();
    }
}
