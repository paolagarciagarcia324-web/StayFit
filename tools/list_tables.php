<?php
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->conectar();
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
sort($tables);
foreach ($tables as $t) {
    echo $t . PHP_EOL;
}
