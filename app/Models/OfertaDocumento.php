<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo OfertaDocumento
 * Representa un documento adjunto a una licitación
 */
class OfertaDocumento extends Model
{
    protected $table      = 'ofertas_documentos';
    public    $timestamps = false;

    protected $fillable = [
        'licitacion_id',
        'titulo',
        'descripcion',
        'archivo',
        'creado_en',
    ];

    /**
     * Relación: un documento pertenece a una oferta
     */
    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'licitacion_id');
    }

    /**
     * Retorna la extensión del archivo en mayúsculas (PDF, ZIP)
     */
    public function getExtensionAttribute(): string
    {
        return strtoupper(pathinfo($this->archivo, PATHINFO_EXTENSION));
    }
}