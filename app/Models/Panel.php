<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
class Panel extends Model
{
    use HasFactory;
    
    protected $table = 'panels';
     
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

    


    public function teacher()

    {

        return $this->hasOne(Teacher::class, 'id', 'teacher_id', )->with('notas')->with('panels');
     
    }   



    public function classes()

    {

        return $this->belongsTo(Classes::class, 'classes_id', 'id');

    }

    public function class()

    {

        return $this->hasMany(Classes::class, 'id', 'classes_id');

    }



    public function video_lesson()

    {

        return $this->hasMany(VideoLesson::class, 'panel_id', 'id', );

    }



    public function material()

    {

        return $this->belongsToMany(Material::class, 'material_panels', 'panel_id', 'material_id');

    }



    public function questions()

    {

        return $this->belongsToMany(Question::class, 'question_panels', 'panel_id', 'question_id')->with('alternatives');

    }



    public function teachers()

    {

        return $this->hasOne(Teacher::class, 'id', 'teacher_id', )->with('notas');

    }
      
}
