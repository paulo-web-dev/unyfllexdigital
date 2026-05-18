<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Panel extends Model
{
    use HasFactory;

    protected $table = 'panels';

    protected static function boot()
    {
        parent::boot();

        static::updating(fn ($m) => $m->log = Auth::check() ? Auth::user()->name : '');
        static::creating(fn ($m) => $m->log = Auth::check() ? Auth::user()->name : '');
    }

    protected $fillable = [
        'classes_id',
        'teacher_id',
        'panel_presencial',
        'title',
        'subtitle',
        'content',
        'start_time',
        'status',
        'horario',
        'valor',
        'pagamento_status',
        'confirmation',
        'log',
        'id_antigo',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    // ── Relacionamentos ───────────────────────────────────────────────────

    public function classes()
    {
        return $this->belongsTo(Classes::class, 'classes_id', 'id');
    }

    public function video_lesson()
    {
        return $this->hasMany(VideoLesson::class, 'panel_id', 'id');
    }

    public function material()
    {
        return $this->belongsToMany(Material::class, 'material_panels', 'panel_id', 'material_id');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_panels', 'panel_id', 'question_id')
            ->with('alternatives');
    }

    public function teachers()
    {
        return $this->hasOne(Teacher::class, 'id', 'teacher_id')->with('notas');
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'id', 'teacher_id')->with('notas', 'panels');
    }
}
