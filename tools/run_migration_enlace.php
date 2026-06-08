<?php
require_once __DIR__ . '/../config/database.php';

$db = (new Database())->conectar();
$sql = file_get_contents(__DIR__ . '/../sql/migrations/001_enlaces_registro_institucional.sql');
$db->exec($sql);
echo "Migration OK\n";
