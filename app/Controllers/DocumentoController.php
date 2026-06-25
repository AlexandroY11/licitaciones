<?php

require_once __DIR__.'/../../bootstrap/database.php';

use App\Helpers\Response;
use App\Models\Oferta;
use App\Models\OfertaDocumento;

/**
 * Gestión de documentos de ofertas.
 *
 * Expone endpoints para:
 * - Subir documentos (PDF/ZIP) asociados a una oferta
 * - Eliminar documentos existentes
 *
 * Reglas de negocio:
 * - Solo se permiten archivos PDF o ZIP validados por MIME y extensión
 * - Cada documento debe estar asociado a una oferta existente
 * - No se permite eliminar el último documento de una oferta
 *
 * Incluye validaciones de seguridad, control de integridad de archivos
 * y persistencia en almacenamiento local.
 */
class DocumentoController
{
    private string $uploadDir = __DIR__.'/../../public/uploads/';
    private array $tiposPermitidos = ['application/pdf', 'application/zip', 'application/x-zip-compressed'];
    private array $extensionesPermitidas = ['pdf', 'zip'];

    /**
     * Subida de documento asociado a una oferta.
     *
     * Endpoint:
     * POST /ofertas/documento/subir
     *
     * - Valida existencia de oferta.
     * - Permite solo PDF y ZIP.
     * - Guarda archivo con nombre único.
     */
    public function subir(): void
    {
        $licitacionId = (int) ($_POST['licitacion_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        // Validaciones básicas
        $errores = [];
        if (!$licitacionId) {
            $errores['licitacion_id'] = 'ID de licitación requerido.';
        }
        if ($titulo === '') {
            $errores['titulo'] = 'El título es obligatorio.';
        }
        if ($descripcion === '') {
            $errores['descripcion'] = 'La descripción es obligatoria.';
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $errores['archivo'] = 'Debe seleccionar un archivo válido.';
        }

        if (!empty($errores)) {
            Response::error('Errores de validación.', 422, $errores);
        }

        // Validar tipo y extensión
        $archivo = $_FILES['archivo'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $this->extensionesPermitidas)) {
            Response::error('Solo se permiten archivos PDF o ZIP.', 422);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $this->tiposPermitidos)) {
            Response::error('Tipo de archivo no permitido.', 422);
        }

        // Verificar que la oferta exista
        if (!Oferta::find($licitacionId)) {
            Response::error('Licitación no encontrada.', 404);
        }

        // Guardar archivo con nombre único
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $nombreArchivo = uniqid("doc_{$licitacionId}_").'.'.$extension;
        $rutaDestino = $this->uploadDir.$nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            Response::error('Error al guardar el archivo.', 500);
        }

        $documento = OfertaDocumento::create([
            'licitacion_id' => $licitacionId,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'archivo' => 'uploads/'.$nombreArchivo,
            'creado_en' => date('Y-m-d H:i:s'),
        ]);

        Response::success($documento, 'Documento agregado correctamente.');
    }

    /**
     * Eliminación de documento asociado a una oferta.
     *
     * - Evita eliminar el último documento de la oferta.
     * - Elimina archivo físico y registro en BD.
     */
    public function borrar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $documento = OfertaDocumento::find($id);

        if (!$documento) {
            Response::error('Documento no encontrado.', 404);
        }

        // No borrar si es el último documento de la oferta
        $totalDocs = OfertaDocumento::where('licitacion_id', $documento->licitacion_id)->count();
        if ($totalDocs <= 1) {
            Response::error('Debe existir al menos un documento. No se puede eliminar el último.', 422);
        }

        $rutaArchivo = __DIR__.'/../../public/'.$documento->archivo;
        if (file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }

        $documento->delete();
        Response::success([], 'Documento eliminado.');
    }
}
