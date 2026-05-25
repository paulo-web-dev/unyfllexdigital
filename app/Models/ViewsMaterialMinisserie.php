<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewsMaterialMinisserie extends Model
{
    protected $table = 'views_materials_minisseries';

    protected $fillable = [
        'id_user',
        'material_id',
    ];
}
