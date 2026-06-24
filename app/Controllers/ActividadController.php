<?php

require_once __DIR__ . '/../../bootstrap/database.php';

use App\Models\Actividad;
use App\Helpers\Response;

class ActividadController
{
    /**
     * GET /api/actividades
     * Retorna todas las actividades para el select del formulario
     */
    public function listar(): void
    {
        $actividades = Actividad::select(
            'id', 'codigo_producto', 'producto',
            'clase', 'familia', 'segmento'
        )->orderBy('segmento')->orderBy('producto')->get();

        Response::success($actividades);
    }
}