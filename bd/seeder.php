<?php
/**
 * Seeder de ofertas
 * Genera 20 ofertas de prueba con datos realistas.
 *
 * Uso: php bd/seeder.php
 */

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../bootstrap/database.php';

use App\Models\Actividad;
use App\Models\Oferta;
use Illuminate\Database\Capsule\Manager as DB;

// ── Verificar que haya actividades cargadas ──────────────────
$totalActividades = Actividad::count();
if ($totalActividades === 0) {
    exit("❌ No hay actividades en la BD. Ejecuta primero: php bd/importar_actividades.php\n");
}

echo "✓ {$totalActividades} actividades disponibles.\n";
echo "Generando 20 ofertas de prueba...\n\n";

// ── Datos de prueba realistas ────────────────────────────────
$objetos = [
    'Adquisición de equipos de cómputo para sedes regionales',
    'Contratación de servicio de mantenimiento preventivo y correctivo',
    'Suministro de papelería y útiles de oficina',
    'Prestación de servicios de seguridad y vigilancia',
    'Adquisición de mobiliario para oficinas administrativas',
    'Contratación de servicio de aseo y cafetería',
    'Suministro e instalación de equipos de aire acondicionado',
    'Prestación de servicios de consultoría en tecnología',
    'Adquisición de vehículos para transporte institucional',
    'Contratación de servicio de impresión y fotocopiado',
    'Suministro de medicamentos e insumos médicos',
    'Adquisición de software de gestión empresarial',
    'Contratación de servicios de capacitación y formación',
    'Suministro de combustible para flota vehicular',
    'Prestación de servicios de telecomunicaciones',
    'Adquisición de equipos de laboratorio',
    'Contratación de obra civil para adecuación de instalaciones',
    'Suministro de dotación e indumentaria institucional',
    'Prestación de servicios de auditoría externa',
    'Adquisición de equipos de seguridad electrónica',
];

$descripciones = [
    'Adquisición de 150 equipos portátiles con garantía extendida de 3 años y soporte técnico on-site para todas las sedes a nivel nacional.',
    'Servicio integral de mantenimiento preventivo mensual y correctivo según demanda para todos los equipos e instalaciones de la entidad.',
    'Suministro trimestral de papelería, tóneres, folders y demás insumos de oficina para las dependencias administrativas y operativas.',
    'Servicio de vigilancia armada 24/7 en las instalaciones principales y sedes alternas, incluyendo monitoreo por CCTV.',
    'Suministro e instalación de escritorios, sillas ergonómicas, archivadores y demás mobiliario para las nuevas oficinas administrativas.',
    'Servicio de aseo diario, cafetería y suministro de insumos de limpieza para todas las instalaciones de la entidad.',
    'Suministro, instalación y puesta en marcha de sistemas de climatización para salas de servidores y áreas de trabajo.',
    'Consultoría especializada en transformación digital, arquitectura de software y gestión de proyectos tecnológicos.',
    'Adquisición de 10 vehículos tipo sedán para transporte de funcionarios y comisiones institucionales.',
    'Servicio administrado de impresión, incluye equipos, insumos, mantenimiento y soporte técnico bajo modalidad pago por página.',
    'Suministro periódico de medicamentos, dispositivos médicos e insumos para el programa de bienestar del talento humano.',
    'Licenciamiento e implementación de plataforma ERP para gestión financiera, contable y de talento humano.',
    'Diseño e implementación de programas de capacitación en competencias digitales, liderazgo y gestión por procesos.',
    'Suministro de combustible tipo corriente y extra para la flota vehicular institucional mediante sistema de abastecimiento por convenio.',
    'Prestación del servicio de telefonía fija, móvil, internet dedicado y videoconferencia para todas las sedes.',
    'Adquisición de equipos especializados de laboratorio para análisis físicoquímicos y microbiológicos.',
    'Adecuación y remodelación de espacios físicos en la sede principal, incluyendo obra civil, eléctrica y de datos.',
    'Suministro anual de dotación de trabajo, calzado e indumentaria institucional para el personal operativo.',
    'Contratación de firma auditora para la realización de auditoría financiera y de gestión del año fiscal.',
    'Suministro e instalación de cámaras IP, control de acceso biométrico y sistemas de alarma perimetral.',
];

$monedas = ['COP', 'COP', 'COP', 'USD', 'COP', 'COP', 'COP', 'USD', 'COP', 'EUR'];
$estados = ['activo', 'activo', 'activo', 'activo', 'activo', 'inactivo', 'cerrado', 'activo', 'activo', 'activo'];

$presupuestos = [
    450000000, 85000000, 12000000, 320000000, 95000000,
    48000000,  180000000, 75000,    650000000, 35000000,
    28000000,  120000,    45000000, 95000000,  88000000,
    250000000, 380000000, 22000000, 18000000,  165000000,
];

// IDs de actividades aleatorios de la BD
$actividadIds = Actividad::inRandomOrder()->limit(20)->pluck('id')->toArray();

// ── Limpiar ofertas anteriores del seeder ────────────────────
$eliminadas = Oferta::whereRaw("consecutivo LIKE 'O-%-25'")->count();
if ($eliminadas > 0) {
    echo "⚠ Se encontraron {$eliminadas} ofertas existentes. ¿Deseas reemplazarlas? (s/n): ";
    $respuesta = trim(fgets(STDIN));
    if (strtolower($respuesta) === 's') {
        Oferta::whereRaw("consecutivo LIKE 'O-%-25'")->delete();
        echo "✓ Ofertas anteriores eliminadas.\n\n";
    } else {
        echo "Seeder cancelado.\n";
        exit(0);
    }
}

// ── Generar ofertas ──────────────────────────────────────────
$anio = date('y');
$creadas = 0;

for ($i = 0; $i < 20; ++$i) {
    // Fechas coherentes — algunas pasadas, algunas futuras, algunas en curso
    $diasOffset = rand(-60, 90);
    $duracion = rand(10, 45);
    $fechaInicio = date('Y-m-d', strtotime("{$diasOffset} days"));
    $fechaCierre = date('Y-m-d', strtotime("{$diasOffset} days + {$duracion} days"));
    $horaInicio = sprintf('%02d:00', rand(7, 10));
    $horaCierre = sprintf('%02d:00', rand(14, 18));

    // Estado coherente con las fechas
    $fechaCierreTs = strtotime($fechaCierre);
    if ($fechaCierreTs < time()) {
        $estado = 'cerrado';
    } elseif ($i === 5) {
        $estado = 'inactivo';
    } else {
        $estado = 'activo';
    }

    $moneda = $monedas[$i % count($monedas)];
    $presupuesto = $presupuestos[$i];
    $actividadId = $actividadIds[$i % count($actividadIds)];

    try {
        DB::transaction(function () use (
            $i, $anio, $objetos, $descripciones,
            $moneda, $presupuesto, $actividadId,
            $fechaInicio, $horaInicio, $fechaCierre, $horaCierre, $estado
        ) {
            $numero = str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $consecutivo = "O-{$numero}-{$anio}";

            Oferta::create([
                'consecutivo' => $consecutivo,
                'objeto' => $objetos[$i],
                'descripcion' => $descripciones[$i],
                'moneda' => $moneda,
                'presupuesto' => $presupuesto,
                'actividad_id' => $actividadId,
                'fecha_inicio' => $fechaInicio,
                'hora_inicio' => $horaInicio,
                'fecha_cierre' => $fechaCierre,
                'hora_cierre' => $horaCierre,
                'estado' => $estado,
                'creado_en' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
                'actualizado_en' => date('Y-m-d H:i:s'),
            ]);
        });

        $numero = str_pad($i + 1, 4, '0', STR_PAD_LEFT);
        echo "  ✓ O-{$numero}-{$anio} — {$objetos[$i]}\n";
        ++$creadas;
    } catch (Exception $e) {
        echo '  ✗ Error en oferta '.($i + 1).': '.$e->getMessage()."\n";
    }
}

echo "\n✓ Seeder completado: {$creadas}/20 ofertas creadas.\n";
echo '  → Activas:   '.Oferta::where('estado', 'activo')->count()."\n";
echo '  → Inactivas: '.Oferta::where('estado', 'inactivo')->count()."\n";
echo '  → Cerradas:  '.Oferta::where('estado', 'cerrado')->count()."\n";
