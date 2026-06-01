<?php
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->conectar();
$r = $db->query("SHOW COLUMNS FROM plan_cliente LIKE 'id_coach'")->fetch(PDO::FETCH_ASSOC);
print_r($r);
