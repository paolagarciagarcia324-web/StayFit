<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class NotificacionModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerPorUsuario($usuarioId)
    {
        $sql = "SELECT * FROM notificacion 
                WHERE id_usuario = :usuario_id 
                ORDER BY fecha_envio DESC"; // Notificaciones usuario

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna notificaciones
    }

    public function obtenerNoLeidas($usuarioId)
    {
        $sql = "SELECT * FROM notificacion 
                WHERE id_usuario = :usuario_id 
                AND leida = 0
                ORDER BY fecha_envio DESC"; // Notificaciones pendientes

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna pendientes
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO notificacion 
                (id_usuario, titulo, contenido, tipo, leida, fecha_envio)
                VALUES 
                (:id_usuario, :titulo, :contenido, :tipo, 0, NOW())"; // Crea notificación

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_usuario', $datos['id_usuario']); // Usuario destino
        $stmt->bindParam(':titulo', $datos['titulo']); // Título
        $stmt->bindParam(':contenido', $datos['contenido']); // Contenido
        $stmt->bindValue(':tipo', $datos['tipo'] ?? 'SISTEMA'); // Tipo

        return $stmt->execute(); // Ejecuta registro
    }

    public function marcarLeida($id)
    {
        $sql = "UPDATE notificacion SET leida = 1, fecha_lectura = NOW() WHERE id_notificacion = :id"; // Marca leída
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Notificación

        return $stmt->execute(); // Ejecuta actualización
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM notificacion WHERE id_notificacion = :id"; // Elimina notificación
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Notificación

        return $stmt->execute(); // Ejecuta eliminación
    }

    public function obtenerPorRol($rol)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null; // Usuario en sesión

        if ($usuarioId && (strtolower($rol) === 'admin' || strtolower($rol) === 'administrador')) { // Admin actual
            return $this->obtenerPorUsuario($usuarioId); // Notificaciones del admin logueado
        }

        $sql = "SELECT n.*
                FROM notificacion n
                INNER JOIN users_roles ur ON ur.id_usuario = n.id_usuario
                INNER JOIN rol r ON r.id_rol = ur.id_rol
                WHERE r.nombre = 'Administrador'
                ORDER BY n.fecha_envio DESC"; // Notificaciones de administradores

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function notificarAdministrador($titulo, $contenido)
    {
        $sql = "SELECT u.id_usuario
                FROM users u
                INNER JOIN users_roles ur ON ur.id_usuario = u.id_usuario
                INNER JOIN rol r ON r.id_rol = ur.id_rol
                WHERE LOWER(r.nombre) IN ('administrador', 'admin') AND u.estado = 'ACTIVO'";

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene admins

        foreach ($admins as $admin) {
            if (empty($admin['id_usuario'])) {
                continue;
            }

            $this->crear([
                'id_usuario' => $admin['id_usuario'],
                'titulo'     => $titulo,
                'contenido'  => $contenido,
                'tipo'       => 'SISTEMA',
            ]);
        }

        return true; // Finaliza proceso
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Notificaciones', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>
