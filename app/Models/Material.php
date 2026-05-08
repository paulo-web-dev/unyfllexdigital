<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $table = 'materials';

    protected $fillable = [
        'name',
        'file_name',
        'type',
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

    public function panels()
    {
        return $this->belongsToMany(Panel::class, 'material_panels', 'material_id', 'panel_id')->with('classes');
    }

    public function materialpanels()
    {
        return $this->hasMany(MaterialPanels::class, 'material_id', 'id')->with('materials');
    }
}
