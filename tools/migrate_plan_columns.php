<?php

require_once __DIR__ . '/../config/database.php';

$db = (new Database())->conectar();

$alteraciones = [
    "ALTER TABLE plan ADD COLUMN modalidad ENUM('PRESENCIAL','VIRTUAL','MIXTA') DEFAULT 'VIRTUAL'",
    "ALTER TABLE plan ADD COLUMN requiere_coach TINYINT(1) DEFAULT 0",
    "ALTER TABLE plan ADD COLUMN incluye_entrenamiento TINYINT(1) DEFAULT 1",
    "ALTER TABLE plan ADD COLUMN incluye_nutricion TINYINT(1) DEFAULT 0",
    "ALTER TABLE plan ADD COLUMN incluye_videos TINYINT(1) DEFAULT 0",
    "ALTER TABLE plan ADD COLUMN incluye_sesiones TINYINT(1) DEFAULT 0",
];

foreach ($alteraciones as $sql) {
    try {
        $db->exec($sql);
        echo "OK: {$sql}\n";
    } catch (PDOException $e) {
        if (stripos($e->getMessage(), 'Duplicate column') !== false) {
            echo "Ya existe (omitido): {$sql}\n";
        } else {
            echo "Error: {$e->getMessage()}\n";
        }
    }
}

echo "Migración de columnas en plan finalizada.\n";
