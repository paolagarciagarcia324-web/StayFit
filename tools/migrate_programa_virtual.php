<?php

require_once __DIR__ . '/../config/database.php';

$sqlFile = __DIR__ . '/../sql/migrations/002_programa_virtual.sql';
$sql = file_get_contents($sqlFile);

if ($sql === false) {
    die("No se pudo leer {$sqlFile}\n");
}

$db = (new Database())->conectar();

try {
    $db->exec($sql);
    echo "Tabla programa_virtual creada o ya existía.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "Tabla programa_virtual ya existe.\n";
    } else {
        throw $e;
    }
}
