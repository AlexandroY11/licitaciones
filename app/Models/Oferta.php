<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Modelo Oferta
 * Representa una licitación en el sistema
 */
class Oferta extends Model
{
    protected $table      = 'ofertas';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = [
        'consecutivo',
        'objeto',
        'descripcion',
        'moneda',
        'presupuesto',
        'actividad_id',
        'fecha_inicio',
        'hora_inicio',
        'fecha_cierre',
        'hora_cierre',
        'estado',
        'creado_en',
        'actualizado_en',
    ];

    protected $dates = [
        'fecha_inicio',
        'fecha_cierre',
    ];

    // --------------------------------------------------------
    // Relaciones
    // --------------------------------------------------------

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'actividad_id');
    }

    public function documentos()
    {
        return $this->hasMany(OfertaDocumento::class, 'licitacion_id');
    }

    // --------------------------------------------------------
    // Métodos de negocio
    // --------------------------------------------------------

    /**
     * Genera el consecutivo único con formato O-{0001}-{YY}
     * Usa lockForUpdate para evitar race conditions
     */
    public static function generarConsecutivo(): string
    {
        $anio   = date('y');
        $ultimo = self::whereRaw("RIGHT(consecutivo, 2) = ?", [$anio])
            ->lockForUpdate()
            ->count();

        $numero = str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);

        return "O-{$numero}-{$anio}";
    }
}
