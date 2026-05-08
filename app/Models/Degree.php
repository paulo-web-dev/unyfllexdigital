<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;



class Degree extends Model

{

    use HasFactory;



    protected $table = 'degree';



    protected $fillable = [

        'id_aluno',

        'id_curso',

        

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








}
