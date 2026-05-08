<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
class Teacher extends Model
{
    use HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
        'name',
        'cpf',
        'email',
        'phone',
        'photo',
        'short_resume',
        'full_resume',
        'status'
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

    public function panels()
    {
        return $this->hasMany(Panel::class, 'teacher_id', 'id')->with('classes');
    }
    public function notas()
    {
        return $this->hasMany(TeachersNotas::class, 'teacher_id', 'id');
    } 



}
