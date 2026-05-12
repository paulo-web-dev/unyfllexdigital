<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewsMaterialsMinisserie extends Model
{ 
    
    use HasFactory;
 
    protected $table = 'views_materials_minisseries';

    
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
        'material_id',  
       
    ];
    public function user()

    {   

        return $this->hasOne(users::class, 'id', 'id_user');

    }
 

    public function material()

    {   

        return $this->hasOne(Material::class, 'id', 'material_id')->with('panels');

    }

}
