<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            $model->log = Auth::check() ? Auth::user()->name : '';
        });

        static::creating(function ($model) {
            $model->log = Auth::check() ? Auth::user()->name : '';
        });
    }

    protected $fillable = [
        'course_id',
        'presencial_id',
        'unyflex',
        'title',
        'subtitle',
        'slug',
        'valor',
        'photo',
        'start_date',
        'end_date',
        'type',
        'workload',
        'live',
        'status',
        'confirmed',
        'id_polo',
        'polo',
        'incompany',
        'novidade',
        'cc',
        'mc',
        'cv',
        'express',
        'seminario',
        'brinde_modular',
        'info',
        'agenda',
        'cnpj',
        'log',
        'id_antiga',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'live'      => 'boolean',
        'express'   => 'boolean',
        'novidade'  => 'boolean',
        'incompany' => 'boolean',
        'cc'        => 'boolean',
        'mc'        => 'boolean',
        'cv'        => 'boolean',
        'seminario' => 'boolean',
        'confirmed' => 'boolean',
    ];

    public function panels()
    {
        return $this->hasMany(Panel::class, 'classes_id', 'id')
            ->with('video_lesson', 'material', 'questions', 'teachers')
            ->orderBy('start_time')
            ->orderBy('horario');
    }
}
