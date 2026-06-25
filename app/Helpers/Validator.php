<?php

namespace App\Helpers;

class Validator
{
    private array $errores = [];
    private array $datos = [];

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function requerido(string $campo, string $label): self
    {
        $valor = trim($this->datos[$campo] ?? '');
        if ($valor === '') {
            $this->errores[$campo] = "{$label} es obligatorio.";
        }

        return $this;
    }

    public function maxLength(string $campo, int $max, string $label): self
    {
        $valor = trim($this->datos[$campo] ?? '');
        if (strlen($valor) > $max) {
            $this->errores[$campo] = "{$label} no puede superar {$max} caracteres.";
        }

        return $this;
    }

    public function moneda(string $campo): self
    {
        $valor = $this->datos[$campo] ?? '';
        if (!in_array($valor, ['COP', 'USD', 'EUR'])) {
            $this->errores[$campo] = 'Moneda inválida. Use COP, USD o EUR.';
        }

        return $this;
    }

    public function presupuesto(string $campo): self
    {
        $valor = $this->datos[$campo] ?? '';
        if (!is_numeric($valor) || $valor <= 0) {
            $this->errores[$campo] = 'El presupuesto debe ser un número mayor a 0.';
        } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $valor)) {
            $this->errores[$campo] = 'El presupuesto acepta máximo 2 decimales.';
        }

        return $this;
    }

    public function fecha(string $campo, string $label): self
    {
        $valor = $this->datos[$campo] ?? '';
        $d = \DateTime::createFromFormat('Y-m-d', $valor);
        if (!$d || $d->format('Y-m-d') !== $valor) {
            $this->errores[$campo] = "{$label} debe tener formato YYYY-MM-DD.";
        }

        return $this;
    }

    public function hora(string $campo, string $label): self
    {
        $valor = $this->datos[$campo] ?? '';
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $valor)) {
            $this->errores[$campo] = "{$label} debe tener formato HH:mm (24h).";
        }

        return $this;
    }

    /**
     * Valida que fecha/hora inicio sea estrictamente menor a fecha/hora cierre.
     */
    public function cronograma(string $fi, string $hi, string $fc, string $hc): self
    {
        $inicio = strtotime(($this->datos[$fi] ?? '').' '.($this->datos[$hi] ?? ''));
        $cierre = strtotime(($this->datos[$fc] ?? '').' '.($this->datos[$hc] ?? ''));

        if ($inicio && $cierre && $inicio >= $cierre) {
            $this->errores['cronograma'] = 'La fecha/hora de cierre debe ser posterior a la de inicio.';
        }

        return $this;
    }

    public function tieneErrores(): bool
    {
        return count($this->errores) > 0;
    }

    public function errores(): array
    {
        return $this->errores;
    }

    public function existeEn(string $campo, string $tabla, string $label): self
    {
        $valor = (int) ($this->datos[$campo] ?? 0);
        if ($valor <= 0) {
            $this->errores[$campo] = "{$label} es obligatorio.";

            return $this;
        }

        $existe = \Illuminate\Database\Capsule\Manager::table($tabla)
            ->where('id', $valor)
            ->exists();

        if (!$existe) {
            $this->errores[$campo] = "{$label} seleccionado no es válido.";
        }

        return $this;
    }
}
