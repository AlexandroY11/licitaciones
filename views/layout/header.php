<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'Licitaciones') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 600; letter-spacing: .5px; }
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .table th { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: #6c757d; }
        .badge-estado { font-size: .75rem; padding: .35em .65em; }
        [v-cloak] { display: none; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
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