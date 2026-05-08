<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;



class Question extends Model

{

    use HasFactory;



    protected $table = 'questions_test';



    protected $fillable = [

    

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




    public function alternatives()

    {

        return $this->hasMany(Alternative::class, 'idquestion', 'id');

    }

    public function classes()

    {

        return $this->hasMany(Classes::class, 'id', 'id_class')->groupBy('id');

    }


    



}

