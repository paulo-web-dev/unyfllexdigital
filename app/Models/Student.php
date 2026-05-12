<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

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

    public function enrollment()
    {
        return $this->hasMany(Enrollment::class, 'student_id', 'id')->with('classes')->with('enrollmentobservations');
    }



    public function user()   
    {
        return $this->hasOne(User::class, 'student_id', 'id')->with('loginava');
    }
}
