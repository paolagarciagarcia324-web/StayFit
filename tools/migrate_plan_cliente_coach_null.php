<?php

require_once __DIR__ . '/../config/database.php';

$db = (new Database())->conectar();

try {
    $db->exec('ALTER TABLE plan_cliente MODIFY COLUMN id_coach BIGINT NULL');
    echo "Columna id_coach ahora permite NULL.\n";
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
