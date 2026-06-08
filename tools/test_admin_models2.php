<?php
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['rol'] = 'administrador';

require_once __DIR__ . '/../config/database.php';

$tests = [
    'Asignaciones' => function () {
        require_once __DIR__ . '/../models/cliente/clienteModel.php';
        return count((new ClienteModel())->obtenerAsignaciones());
    },
    'Clientes institucionales' => function () {
        require_once __DIR__ . '/../models/cliente/clienteInsModel.php';
        return count((new ClienteInsModel())->obtenerTodos());
    },
    'Roles' => function () {
        require_once __DIR__ . '/../models/usuario/rolModel.php';
        return count((new RolModel())->obtenerTodos());
    },
];

foreach ($tests as $nombre => $fn) {
    try {
        $n = $fn();
        echo "[OK] {$nombre}: {$n}\n";
    } catch (Throwable $e) {
        echo "[FAIL] {$nombre}: " . $e->getMessage() . "\n";
    }
}
