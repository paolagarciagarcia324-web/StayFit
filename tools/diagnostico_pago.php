<?php
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->conectar();
foreach (['pago', 'comprobantes'] as $t) {
    echo "=== {$t} ===\n";
    try {
        foreach ($db->query("SHOW COLUMNS FROM `{$t}`") as $c) {
            echo $c['Field'] . "\n";
        }
    } catch (PDOException $e) {
        echo $e->getMessage() . "\n";
    }
}
