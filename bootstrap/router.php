<?php

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Quita el prefijo si el proyecto corre en subdirectorio
$base = '/licitaciones/public';
$uri  = str_starts_with($uri, $base)
    ? substr($uri, strlen($base))
    : $uri;

$uri = trim($uri, '/') ?: 'ofertas';

// -------------------------------------------------------
// Rutas
// -------------------------------------------------------
$routes = [
    'GET' => [
        'ofertas'                  => ['OfertaController', 'index'],
        'ofertas/crear'            => ['OfertaController', 'crear'],
        'ofertas/detalle'          => ['OfertaController', 'detalle'],
        'ofertas/editar'           => ['OfertaController', 'editar'],
        'ofertas/exportar'         => ['OfertaController', 'exportar'],
        'api/actividades'          => ['ActividadController', 'listar'],
    ],
    'POST' => [
        'ofertas/guardar'          => ['OfertaController', 'guardar'],
        'ofertas/actualizar'       => ['OfertaController', 'actualizar'],
        'ofertas/documento/subir'  => ['DocumentoController', 'subir'],
        'ofertas/documento/borrar' => ['DocumentoController', 'borrar'],
    ],
];

$controllerDir = __DIR__ . '/../app/Controllers/';

if (isset($routes[$method][$uri])) {
    [$clase, $metodo] = $routes[$method][$uri];
    require_once $controllerDir . $clase . '.php';
    $ctrl = new $clase();
    $ctrl->$metodo();
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada']);
}
