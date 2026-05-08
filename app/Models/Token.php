<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;

use Illuminate\Support\Str;



class Token extends Model

{

    protected $table = 'tokens_certificates';

    public function classes()
    {
        return $this->hasOne(Classes::class, 'id', 'class_id');
    }

}
    
