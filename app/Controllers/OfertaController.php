<?php

require_once __DIR__.'/../../bootstrap/database.php';

use App\Helpers\Response;
use App\Helpers\Validator;
use App\Models\Oferta;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Controller de ofertas.
 *
 * Expone endpoints para:
 * - CRUD de ofertas (crear, editar, listar, detalle)
 * - Cambio de estado (activo/inactivo)
 * - Exportación a Excel
 * - Gestión de documentos adjuntos (PDF/ZIP)
 *
 * Aplica validaciones de negocio como:
 * - fechas coherentes (inicio < cierre)
 * - restricción de edición en estado cerrado
 * - obligatoriedad de documentos en edición
 */
class OfertaController
{
    /**
     * Lista ofertas con filtros y paginación.
     *
     * - Responde HTML si es navegación normal
     * - Responde JSON si es petición AJAX
     * - Actualiza automáticamente ofertas vencidas a estado "cerrado"
     *
     * GET /ofertas
     */
    public function index(): void
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            Oferta::where('estado', 'activo')
                    ->whereRaw("CONCAT(fecha_cierre, ' ', hora_cierre) < NOW()")
                    ->update([
                        'estado' => 'cerrado',
                        'actualizado_en' => date('Y-m-d H:i:s'),
                    ]);

            $q = trim($_GET['q'] ?? '');
            $estado = trim($_GET['estado'] ?? '');
            $moneda = trim($_GET['moneda'] ?? '');
            $fechaDesde = trim($_GET['fecha_desde'] ?? '');
            $fechaHasta = trim($_GET['fecha_hasta'] ?? '');
            $porPagina = 10;
            $pagina = max(1, (int) ($_GET['pagina'] ?? 1));

            $query = Oferta::with('actividad')->orderBy('creado_en', 'desc');

            if ($q !== '') {
                $query->where(function ($b) use ($q) {
                    $b->where('consecutivo', 'like', "%{$q}%")
                    ->orWhere('objeto', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%");
                });
            }
            if ($estado !== '') {
                $query->where('estado', $estado);
            }
            if ($moneda !== '') {
                $query->where('moneda', $moneda);
            }
            if ($fechaDesde !== '') {
                $query->where('fecha_inicio', '>=', $fechaDesde);
            }
            if ($fechaHasta !== '') {
                $query->where('fecha_cierre', '<=', $fechaHasta);
            }

            $total = $query->count();
            $ofertas = $query->skip(($pagina - 1) * $porPagina)->take($porPagina)->get();

            Response::success([
                'ofertas' => $ofertas,
                'total' => $total,
                'pagina' => $pagina,
                'por_pagina' => $porPagina,
                'total_paginas' => ceil($total / $porPagina),
            ]);

            return;
        }

        require_once __DIR__.'/../../views/ofertas/index.php';
    }

    /**
     * Retorna el detalle de una oferta.
     *
     * Si la petición es AJAX devuelve JSON,
     * de lo contrario renderiza la vista.
     *
     * GET /ofertas/detalle?id={id}
     */
    public function detalle(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $oferta = Oferta::with(['actividad', 'documentos'])->find($id);

        if (!$oferta) {
            if (
                !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            ) {
                Response::error('Oferta no encontrada.', 404);
            }
            header('Location: /licitaciones/public/ofertas');
            exit;
        }

        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        ) {
            Response::success($oferta);
        }

        require_once __DIR__.'/../../views/ofertas/detalle.php';
    }

    /**
     * Renderiza el formulario de creación de oferta.
     *
     * GET /ofertas/crear
     */
    public function crear(): void
    {
        require_once __DIR__.'/../../views/ofertas/form.php';
    }

    /**
     * Renderiza el formulario de edición de oferta.
     *
     * GET /ofertas/editar?id={id}
     */
    public function editar(): void
    {
        require_once __DIR__.'/../../views/ofertas/form.php';
    }

    /**
     * Crea una nueva oferta.
     *
     * - Valida datos obligatorios
     * - Genera consecutivo automático
     * - Ejecuta transacción
     *
     * POST /ofertas/guardar
     */
    public function guardar(): void
    {
        $datos = $this->obtenerDatosPost();
        $errores = $this->validar($datos);

        if (!empty($errores)) {
            Response::error('Errores de validación.', 422, $errores);
        }

        try {
            DB::transaction(function () use ($datos) {
                $datos['consecutivo'] = Oferta::generarConsecutivo();
                $datos['estado'] = 'activo';
                $datos['creado_en'] = date('Y-m-d H:i:s');
                $datos['actualizado_en'] = date('Y-m-d H:i:s');
                Oferta::create($datos);
            });

            Response::success([], 'Oferta creada correctamente.');
        } catch (Exception $e) {
            Response::error('Error al guardar la oferta: '.$e->getMessage(), 500);
        }
    }

    /**
     * Actualiza una oferta existente.
     *
     * - No permite edición si está cerrada
     * - Valida datos y existencia de documentos
     *
     * POST /ofertas/actualizar
     */
    public function actualizar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $oferta = Oferta::find($id);

        if (!$oferta) {
            Response::error('Oferta no encontrada.', 404);
        }

        if ($oferta->estado === 'cerrado') {
            Response::error('No se puede editar una oferta cerrada.', 403);
        }

        $datos = $this->obtenerDatosPost();
        $errores = $this->validar($datos);

        // En edición debe haber al menos 1 documento
        $totalDocs = $oferta->documentos()->count();
        if ($totalDocs < 1) {
            $errores['documentos'] = 'Debe existir al menos un documento adjunto.';
        }

        if (!empty($errores)) {
            Response::error('Errores de validación.', 422, $errores);
        }

        try {
            $datos['actualizado_en'] = date('Y-m-d H:i:s');
            $oferta->update($datos);
            Response::success([], 'Oferta actualizada correctamente.');
        } catch (Exception $e) {
            Response::error('Error al actualizar: '.$e->getMessage(), 500);
        }
    }

    /**
     * Cambia estado de una oferta.
     *
     * Estados válidos: activo | inactivo
     *
     * POST /ofertas/estado
     */
    public function cambiarEstado(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $estado = trim($_POST['estado'] ?? '');

        if (!in_array($estado, ['activo', 'inactivo'])) {
            Response::error('Estado inválido.', 422);
        }

        $oferta = Oferta::find($id);
        if (!$oferta) {
            Response::error('Oferta no encontrada.', 404);
        }

        $oferta->update([
            'estado' => $estado,
            'actualizado_en' => date('Y-m-d H:i:s'),
        ]);

        Response::success(['estado' => $estado], 'Estado actualizado correctamente.');
    }

    /**
     * Exporta listado de ofertas a Excel.
     *
     * Aplica los mismos filtros del listado.
     *
     * GET /ofertas/exportar
     */
    public function exportar(): void
    {
        require_once __DIR__.'/../../vendor/autoload.php';

        $q = trim($_GET['q'] ?? '');
        $estado = trim($_GET['estado'] ?? '');
        $moneda = trim($_GET['moneda'] ?? '');
        $fechaDesde = trim($_GET['fecha_desde'] ?? '');
        $fechaHasta = trim($_GET['fecha_hasta'] ?? '');

        $query = Oferta::with('actividad')->orderBy('creado_en', 'desc');

        if ($q !== '') {
            $query->where(function ($b) use ($q) {
                $b->where('consecutivo', 'like', "%{$q}%")
                ->orWhere('objeto', 'like', "%{$q}%")
                ->orWhere('descripcion', 'like', "%{$q}%");
            });
        }
        if ($estado !== '') {
            $query->where('estado', $estado);
        }
        if ($moneda !== '') {
            $query->where('moneda', $moneda);
        }
        if ($fechaDesde !== '') {
            $query->where('fecha_inicio', '>=', $fechaDesde);
        }
        if ($fechaHasta !== '') {
            $query->where('fecha_cierre', '<=', $fechaHasta);
        }

        $ofertas = $query->get();

        if ($q !== '') {
            $query->where(function ($b) use ($q) {
                $b->where('consecutivo', 'like', "%{$q}%")
                    ->orWhere('objeto', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%");
            });
        }
        $ofertas = $query->get();

        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Cabeceras
        $cabeceras = [
            'A' => 'Consecutivo',
            'B' => 'Objeto',
            'C' => 'Descripción',
            'D' => 'Fecha inicio',
            'E' => 'Fecha cierre',
            'F' => 'Estado',
        ];

        foreach ($cabeceras as $col => $titulo) {
            $sheet->setCellValue("{$col}1", $titulo);
        }

        // Datos
        foreach ($ofertas as $i => $o) {
            $fila = $i + 2;
            $sheet->setCellValue("A{$fila}", $o->consecutivo);
            $sheet->setCellValue("B{$fila}", $o->objeto);
            $sheet->setCellValue("C{$fila}", $o->descripcion);
            $sheet->setCellValue("D{$fila}", $o->fecha_inicio);
            $sheet->setCellValue("E{$fila}", $o->fecha_cierre);
            $sheet->setCellValue("F{$fila}", $o->estado);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="ofertas_'.date('Ymd').'.xlsx"');

        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // --------------------------------------------------------
    // Métodos privados
    // --------------------------------------------------------

    private function obtenerDatosPost(): array
    {
        return [
            'objeto' => trim($_POST['objeto'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'moneda' => trim($_POST['moneda'] ?? ''),
            'presupuesto' => trim($_POST['presupuesto'] ?? ''),
            'actividad_id' => (int) ($_POST['actividad_id'] ?? 0),
            'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
            'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
            'fecha_cierre' => trim($_POST['fecha_cierre'] ?? ''),
            'hora_cierre' => trim($_POST['hora_cierre'] ?? ''),
        ];
    }

    private function validar(array $datos): array
    {
        $v = new Validator($datos);
        $v->requerido('objeto', 'Objeto')
            ->maxLength('objeto', 150, 'Objeto')
            ->requerido('descripcion', 'Descripción')
            ->maxLength('descripcion', 400, 'Descripción')
            ->moneda('moneda')
            ->presupuesto('presupuesto')
            ->existeEn('actividad_id', 'actividades', 'Actividad')
            ->fecha('fecha_inicio', 'Fecha inicio')
            ->hora('hora_inicio', 'Hora inicio')
            ->fecha('fecha_cierre', 'Fecha cierre')
            ->hora('hora_cierre', 'Hora cierre')
            ->cronograma('fecha_inicio', 'hora_inicio', 'fecha_cierre', 'hora_cierre');

        return $v->errores();
    }
}
