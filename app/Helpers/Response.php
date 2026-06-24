<?php

namespace App\Helpers;

class Response
{
    public static function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($data = [], string $mensaje = 'OK'): void
    {
        self::json(['success' => true, 'mensaje' => $mensaje, 'data' => $data]);
    }

    public static function error(string $mensaje, int $code = 400, array $errores = []): void
    {
        self::json(['success' => false, 'mensaje' => $mensaje, 'errores' => $errores], $code);
    }
}