<?php

require_once __DIR__ . '/../config/database.php';

$db = (new Database())->conectar();

try {
    $db->exec('ALTER TABLE users ADD COLUMN documento_identidad VARCHAR(60) NULL AFTER telefono');
    echo "Columna documento_identidad agregada.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Columna documento_identidad ya existe.\n";
    } else {
        throw $e;
    }
}
