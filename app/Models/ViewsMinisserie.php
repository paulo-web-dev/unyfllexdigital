<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewsMinisserie extends Model 
{
    
    use HasFactory;

    protected $table = 'views_minisseries';

    
    /**
     * The attributes that should be hidden for arrays.
     *  
     * @var array 
     */

    protected $hidden = [
        'created_at',
        'updated_at',
    ];  
    protected $fillable = [
        'id_user',
        'classes_id',
        'video_id',
        'panel_id',
       
    ];

        public function user()

    {     

        return $this->hasOne(users::class, 'id', 'id_user');

    }

    public function video()  

    {     

        return $this->hasOne(VideoLesson::class, 'id', 'video_id')->with('panels'); 

    }

    public function classes()  

    {     

        return $this->hasOne(Classes::class, 'id', 'classes_id'); 

    }

}
