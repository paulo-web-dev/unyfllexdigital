<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachersNotas extends Model
{
    
     
    use HasFactory; 

    protected $table = 'teachers_notas';

   
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at', 
    ];

    public function teachers() 
    {
        return $this->hasone(Teacher::class, 'id', 'teacher_id');
    }
    
}
