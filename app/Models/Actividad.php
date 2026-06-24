<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Actividad
 * Representa la tabla actividades (clasificador UNSPSC)
 */
class Actividad extends Model
{
    protected $table      = 'actividades';
    public    $timestamps = false; 

    protected $fillable = [
        'codigo_segmento',
        'segmento',
        'codigo_familia',
        'familia',
        'codigo_clase',
        'clase',
        'codigo_producto',
        'producto',
    ];

    /**
     * Relación: una actividad puede estar en muchas ofertas
     */
    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'actividad_id');
    }
}