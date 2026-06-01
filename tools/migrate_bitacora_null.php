<?php

require_once __DIR__ . '/../config/database.php';

$db = (new Database())->conectar();

try {
    $db->exec('ALTER TABLE bitacora_busqueda MODIFY COLUMN id_usuario BIGINT NULL');
    echo "bitacora_busqueda.id_usuario permite NULL.\n";
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
