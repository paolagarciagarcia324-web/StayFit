<?php
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->conectar();
$tables = ['clientes','coaches','pagos','solicitudes_compra','user','notificaciones','instituciones','planes_cliente','planes'];
foreach ($tables as $t) {
    echo "=== $t ===\n";
    $cols = $db->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
    echo implode(', ', $cols) . "\n\n";
}
