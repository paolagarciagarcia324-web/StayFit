<?php
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->conectar();
foreach ($db->query('SHOW COLUMNS FROM plan_cliente') as $c) {
    echo $c['Field'] . "\n";
}
