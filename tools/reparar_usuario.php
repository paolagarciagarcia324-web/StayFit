<?php

/**
 * Repara datos de un usuario existente (teléfono, documento, contraseña).
 * Uso: php tools/reparar_usuario.php correo@ejemplo.com nuevaClave123 1234567890 3001112233
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/usuario/usuarioModel.php';

$correo = $argv[1] ?? '';
$password = $argv[2] ?? '';
$documento = $argv[3] ?? '';
$telefono = $argv[4] ?? '';

if ($correo === '' || $password === '') {
    echo "Uso: php tools/reparar_usuario.php correo contrasena [documento] [telefono]\n";
    exit(1);
}

$db = (new Database())->conectar();
$model = new UsuarioModel();
$usuario = $model->obtenerPorCorreo($correo);

if (!$usuario) {
    echo "Usuario no encontrado: $correo\n";
    exit(1);
}

$id = $usuario['id'] ?? $usuario['id_usuario'];
$model->actualizarPassword($id, $password);

$sql = 'UPDATE users SET documento_identidad = :doc, telefono = :tel, origen_registro = :origen WHERE id_usuario = :id';
$stmt = $db->prepare($sql);
$stmt->bindValue(':doc', $documento !== '' ? $documento : null);
$stmt->bindValue(':tel', $telefono !== '' ? $telefono : null);
$stmt->bindValue(':origen', 'ADMINISTRATIVO');
$stmt->bindParam(':id', $id);
$stmt->execute();

echo "Usuario reparado: $correo (id $id)\n";
