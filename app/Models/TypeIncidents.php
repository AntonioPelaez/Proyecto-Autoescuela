<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeIncidents extends Model
{
    protected $fillable = [
        'nombre',
    ];

    public function incidents()
    {
        return $this->hasMany(Incidents::class, 'tipo_id');
    }
}
