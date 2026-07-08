<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    protected $table = 'access_logs';

    public $timestamps = false;

    protected $fillable = [
        'student_id', 'user_id', 'action', 'detail', 'ip', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    /** Registra um acesso de forma segura (nunca derruba o fluxo se falhar). */
    public static function registrar(string $action, ?int $studentId, ?int $userId, ?string $detail = null): void
    {
        try {
            static::create([
                'student_id' => $studentId,
                'user_id'    => $userId,
                'action'     => $action,
                'detail'     => $detail,
                'ip'         => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // silencioso: log de acesso nunca deve quebrar login/navegação
        }
    }
}
