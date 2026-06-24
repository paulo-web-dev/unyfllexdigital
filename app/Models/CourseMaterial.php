<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Material de estudo em PDF de um curso modular.
 * type: resumo | cartoes | notas | prova (extensível).
 */
class CourseMaterial extends Model
{
    protected $fillable = [
        'modular_course_id',
        'type',
        'title',
        'pdf_path',
        'content',
        'status',
        'sort_order',
        'version',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'version'    => 'integer',
    ];

    /** Rótulos amigáveis por tipo. */
    public const TIPOS = [
        'resumo'  => 'Resumos de Estudo',
        'cartoes' => 'Cartões Didáticos',
        'notas'   => 'Notas de Estudo',
        'prova'   => 'Prova',
    ];

    public function course()
    {
        return $this->belongsTo(ModularCourse::class, 'modular_course_id');
    }

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->type] ?? ucfirst((string) $this->type);
    }

    /** Cartões (deck) decodificados, quando type=cartoes. */
    public function cards(): array
    {
        $arr = json_decode((string) $this->content, true);
        return is_array($arr) ? $arr : [];
    }

    /** URL pública do PDF, montada a partir da base pública configurada. */
    public function pdfUrl(): ?string
    {
        if (empty($this->pdf_path)) {
            return null;
        }
        $base = rtrim((string) config('cursos_modulares.public_base_url'), '/');
        return $base . '/' . ltrim($this->pdf_path, '/');
    }

    public function hasPdf(): bool
    {
        return ! empty($this->pdf_path);
    }
}
