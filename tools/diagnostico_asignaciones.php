<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/cliente/clienteModel.php';

$db = (new Database())->conectar();
$model = new ClienteModel();

echo "=== Diagnóstico asignaciones ===\n\n";

$tablas = ['users', 'cliente', 'coach', 'plan', 'plan_cliente', 'programa_virtual'];
foreach ($tablas as $t) {
    try {
        $n = (int) $db->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        echo "Tabla {$t}: {$n} filas\n";
    } catch (PDOException $e) {
        echo "Tabla {$t}: NO EXISTE o error - {$e->getMessage()}\n";
    }
}

echo "\n--- plan_cliente (últimos 5) ---\n";
try {
    $rows = $db->query('SELECT * FROM plan_cliente ORDER BY id_plan_cliente DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (PDOException $e) {
    echo $e->getMessage() . "\n";
}

echo "\n--- Clientes activos (dropdown) ---\n";
$clientes = $model->obtenerClientesActivos();
foreach (array_slice($clientes, 0, 5) as $c) {
    echo "id={$c['id']} nombre={$c['nombre']}\n";
}

echo "\n--- obtenerAsignaciones() ---\n";
$asig = $model->obtenerAsignaciones();
echo 'Filas devueltas: ' . count($asig) . "\n";
print_r(array_slice($asig, 0, 3));

echo "\n--- Planes en catálogo ---\n";
try {
    $planes = $db->query('SELECT id_plan, nombre, estado_plan FROM plan LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
    print_r($planes);
} catch (PDOException $e) {
    try {
        $planes = $db->query('SELECT id_plan, nombre FROM plan LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
        print_r($planes);
    } catch (PDOException $e2) {
        echo $e2->getMessage() . "\n";
    }
}

echo "\n--- Test JOIN historial (SQL crudo) ---\n";
try {
    $sql = "SELECT pc.id_plan_cliente, pc.id_cliente, pc.id_coach,
                   u.nombre AS user_nombre, pl.nombre AS plan_nombre
            FROM plan_cliente pc
            LEFT JOIN users u ON u.id_usuario = pc.id_cliente
            LEFT JOIN plan pl ON pl.id_plan = pc.id_plan
            ORDER BY pc.id_plan_cliente DESC LIMIT 5";
    print_r($db->query($sql)->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo $e->getMessage() . "\n";
}
