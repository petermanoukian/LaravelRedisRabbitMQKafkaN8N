<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cat;
use App\Models\Traits\HasFileAttributes;


class Prod extends Model
{
    
    use HasFileAttributes; 

    protected $table = 'prods'; 

    // Mass-assignable fields
    protected $fillable = [
        'catid',
        'name',
        'des',
        'dess',
        'filer',
        'img',
        'img2', 
        'filename',
        'mime', 
        'sizer',
        'extension',
    ];

    // Relationships
    public function cat()
    {
        return $this->belongsTo(Cat::class, 'catid');
    }


    public function prodorders() 
    { 
        return $this->hasMany(Prodorder::class, 'prodid'); 
    }
}
