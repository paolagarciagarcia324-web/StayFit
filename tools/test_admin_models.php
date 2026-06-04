<?php
/**
 * Verifica que los modelos admin carguen sin error PDO.
 */
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['rol'] = 'administrador';
$_SESSION['nombre'] = 'Admin Test';

require_once __DIR__ . '/../config/database.php';

$tests = [
    'Solicitudes' => function () {
        require_once __DIR__ . '/../models/solicitud/solicitudIngresoModel.php';
        $m = new SolicitudIngresoModel();
        return count($m->obtenerTodas());
    },
    'Pagos' => function () {
        require_once __DIR__ . '/../models/pago/pagoModel.php';
        $m = new PagoModel();
        return count($m->obtenerTodos());
    },
    'Clientes' => function () {
        require_once __DIR__ . '/../models/cliente/clienteModel.php';
        $m = new ClienteModel();
        return count($m->obtenerTodos());
    },
    'Clientes activos' => function () {
        require_once __DIR__ . '/../models/cliente/clienteModel.php';
        $m = new ClienteModel();
        return count($m->obtenerClientesActivos());
    },
    'Coaches' => function () {
        require_once __DIR__ . '/../models/coach/coachModel.php';
        $m = new CoachModel();
        return count($m->obtenerTodos());
    },
    'Instituciones' => function () {
        require_once __DIR__ . '/../models/institucion/institucionModel.php';
        $m = new InstitutionModel();
        return count($m->obtenerTodos());
    },
    'Notificaciones' => function () {
        require_once __DIR__ . '/../models/comunicacion/notificacionModel.php';
        $m = new NotificacionModel();
        return count($m->obtenerPorRol('admin'));
    },
    'Usuarios' => function () {
        require_once __DIR__ . '/../models/usuario/usuarioModel.php';
        $m = new UsuarioModel();
        return count($m->obtenerTodos());
    },
    'Reporte clientes' => function () {
        require_once __DIR__ . '/../models/cliente/clienteModel.php';
        $m = new ClienteModel();
        return count($m->reporteGeneral());
    },
    'Reporte pagos' => function () {
        require_once __DIR__ . '/../models/pago/pagoModel.php';
        $m = new PagoModel();
        return count($m->reporteGeneral());
    },
    'Reporte progreso' => function () {
        require_once __DIR__ . '/../models/progreso/progresoModel.php';
        $m = new ProgresoModel();
        return count($m->reporteGeneral());
    },
];

foreach ($tests as $nombre => $fn) {
    try {
        $result = $fn();
        echo "[OK] {$nombre}: {$result} registros\n";
    } catch (Throwable $e) {
        echo "[FAIL] {$nombre}: " . $e->getMessage() . "\n";
    }
}
