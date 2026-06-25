<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Capa (banner 16:9) do curso para o site.
 */
class CourseCover extends Model
{
    protected $fillable = [
        'modular_course_id',
        'image_path',
        'status',
        'version',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(ModularCourse::class, 'modular_course_id');
    }

    public function imageUrl(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }
        $base = rtrim((string) config('cursos_modulares.public_base_url'), '/');
        return $base . '/' . ltrim($this->image_path, '/');
    }

    public function hasImage(): bool
    {
        return ! empty($this->image_path);
    }
}
