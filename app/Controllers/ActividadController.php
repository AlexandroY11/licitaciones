<?php

require_once __DIR__.'/../../bootstrap/database.php';

use App\Helpers\Response;
use App\Models\Actividad;

class ActividadController
{
    private const POR_PAGINA = 20;

    /**
     * Búsqueda paginada de actividades UNSPSC.
     *
     * Endpoint:
     * GET /api/actividades?q=texto&pagina=1
     *
     * - Usa FULLTEXT SEARCH para relevancia.
     * - Requiere mínimo 3 caracteres.
     * - Respuesta paginada (20 registros por página).
     * - Evita exponer el catálogo completo.
     */
    public function listar(): void
    {
        $q = trim($_GET['q'] ?? '');
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $offset = ($pagina - 1) * self::POR_PAGINA;

        if (strlen($q) < 3) {
            Response::success([
                'actividades' => [],
                'total' => 0,
                'mensaje' => 'Escribe al menos 3 caracteres para buscar.',
            ]);

            return;
        }

        $query = Actividad::select('id', 'producto', 'clase', 'familia', 'segmento')
            ->whereRaw(
                'MATCH(producto, clase, familia, segmento) AGAINST(? IN BOOLEAN MODE)',
                ["+{$q}*"]
            )
            ->orderByRaw(
                'MATCH(producto, clase, familia, segmento) AGAINST(? IN BOOLEAN MODE) DESC',
                ["+{$q}*"]
            );

        $total = $query->count();
        $actividades = $query->skip($offset)->take(self::POR_PAGINA)->get();

        Response::success([
            'actividades' => $actividades,
            'total' => $total,
            'pagina' => $pagina,
            'total_paginas' => ceil($total / self::POR_PAGINA),
            'por_pagina' => self::POR_PAGINA,
        ]);
    }
}
