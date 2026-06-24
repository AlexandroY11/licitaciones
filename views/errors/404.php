<?php $tituloPagina = 'Página no encontrada'; ?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex flex-column align-items-center justify-content-center py-5 text-center">
    <h1 class="display-1 fw-bold text-primary mb-0">404</h1>
    <p class="fs-4 fw-semibold text-dark mb-1">Página no encontrada</p>
    <p class="text-muted mb-4">La ruta que buscas no existe o fue movida.</p>
    <a href="/licitaciones/public/ofertas" class="btn btn-primary px-4">
        <i class="bi bi-arrow-left me-2"></i>Volver al inicio
    </a>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>