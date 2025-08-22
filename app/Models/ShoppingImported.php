<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingImported extends Model
{
    protected $table = 'shopping_imported';
    protected $fillable = [
        'imported_nro_dam',
        'shopping_id',
        'imported_expenses_cost',
        'imported_flete_cost',
        'imported_insurance_cost',
        'imported_destination_cost',
        'imported_coefficient'
    ];
}
