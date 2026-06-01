<?php

require_once __DIR__ . '/../config/database.php';

$db = (new Database())->conectar();

$alteraciones = [
    "ALTER TABLE video ADD COLUMN tipo_media ENUM('VIDEO','IMAGEN','ENLACE') NOT NULL DEFAULT 'ENLACE' AFTER url_video",
    "ALTER TABLE video ADD COLUMN id_subido_por BIGINT NULL AFTER tipo_media",
    "ALTER TABLE video ADD COLUMN fecha_subida TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER id_subido_por",
];

foreach ($alteraciones as $sql) {
    try {
        $db->exec($sql);
        echo "OK: {$sql}\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "Ya existe columna: {$sql}\n";
        } else {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

$dir = __DIR__ . '/../public/uploads/contenido_virtual';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
    echo "Carpeta contenido_virtual creada.\n";
}

echo "Migración contenido virtual finalizada.\n";
