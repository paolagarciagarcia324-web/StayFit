<?php
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->conectar();
print_r($db->query("SHOW COLUMNS FROM bitacora_busqueda LIKE 'id_usuario'")->fetch(PDO::FETCH_ASSOC));
$admins = $db->query("SELECT u.id_usuario, r.nombre FROM users u INNER JOIN users_roles ur ON ur.id_usuario=u.id_usuario INNER JOIN rol r ON r.id_rol=ur.id_rol")->fetchAll(PDO::FETCH_ASSOC);
print_r($admins);
