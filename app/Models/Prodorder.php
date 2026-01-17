<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Prod;

class Prodorder extends Model
{
    protected $table = 'prodorders';

    // Mass-assignable fields
    protected $fillable = [
        'prodid',
        'quan',
        'customer',
    ];

    // Relationships
    public function prod()
    {
        return $this->belongsTo(Prod::class, 'prodid');
    }
}
