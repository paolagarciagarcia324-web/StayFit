<?php

require_once __DIR__ . '/../config/database.php';

$db = (new Database())->conectar();

echo "=== solicitud_ingreso ===\n";
try {
    foreach ($db->query('SHOW COLUMNS FROM solicitud_ingreso') as $c) {
        echo $c['Field'] . ' (' . $c['Type'] . ")\n";
    }
    $n = (int) $db->query('SELECT COUNT(*) FROM solicitud_ingreso')->fetchColumn();
    echo "Filas: {$n}\n";
    if ($n > 0) {
        print_r($db->query('SELECT * FROM solicitud_ingreso ORDER BY id_solicitud DESC LIMIT 3')->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (PDOException $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}

echo "\n=== bitacora_busqueda ===\n";
try {
    $n = (int) $db->query('SELECT COUNT(*) FROM bitacora_busqueda')->fetchColumn();
    echo "Filas: {$n}\n";
} catch (PDOException $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
