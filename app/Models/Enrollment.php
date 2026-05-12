<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
class Enrollment extends Model
{
    use HasFactory;

  
  
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

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */ 
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

  

    public function classes()
    {
        return $this->belongsTo(Classes::class, 'classes_id', 'id')->with('panels');
    }

 
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id')->orderBy('city'); 
         
    }
         
   
    public function aluno() 
    {
        return $this->belongsTo(Student::class, 'id_aluno', 'id'); 
    }

   
    
}


