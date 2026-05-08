<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;



class MaterialPanels extends Model

{

    use HasFactory;



    /**

     * The attributes that are mass assignable.

     *

     * @var array

     */

    protected $fillable = [

        'material_id',

        'panel_id'

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

    public function materials()

    {

        return $this->hasOne(Material::class, 'id', 'material_id', );

    }

}

