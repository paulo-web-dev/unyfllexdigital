<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
class Classes extends Model
{
    use HasFactory;
    
    protected $table = 'classes';
     
    protected static $defaultLogValue = null;

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
        'start_date',
        'end_date',
        'type',
        'status',
        'confirmed',
        'workload',
        'live'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',

    ];
    
    public function panels()
    {
        return $this->hasMany(Panel::class, 'classes_id', 'id')->with('video_lesson', 'material', 'questions', 'teachers')->orderBy('start_time')->orderBy('horario');
    }

}
