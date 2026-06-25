<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina ?? 'Licitaciones'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/licitaciones/public/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg mb-4">
    <div class="container">
        <a class="navbar-brand" href="/licitaciones/public/ofertas">
            <i class="bi bi-file-earmark-text me-2"></i>Licitaciones
        </a>
        <a href="/licitaciones/public/ofertas/crear" class="btn btn-light btn-sm ms-auto">
            <i class="bi bi-plus-lg me-1"></i>Nueva oferta
        </a>
    </div>
</nav>
<div class="container pb-5">