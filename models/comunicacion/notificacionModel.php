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
        $sql = "SELECT * FROM notificaciones 
                WHERE usuario_id = :usuario_id 
                ORDER BY fecha DESC"; // Notificaciones usuario

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna notificaciones
    }

    public function obtenerNoLeidas($usuarioId)
    {
        $sql = "SELECT * FROM notificaciones 
                WHERE usuario_id = :usuario_id 
                AND estado = 'no_leida'
                ORDER BY fecha DESC"; // Notificaciones pendientes

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna pendientes
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO notificaciones 
                (usuario_id, titulo, mensaje, tipo, estado, fecha)
                VALUES 
                (:usuario_id, :titulo, :mensaje, :tipo, :estado, NOW())"; // Crea notificación

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $datos['usuario_id']); // Usuario destino
        $stmt->bindParam(':titulo', $datos['titulo']); // Título
        $stmt->bindParam(':mensaje', $datos['mensaje']); // Mensaje
        $stmt->bindValue(':tipo', $datos['tipo'] ?? 'general'); // Tipo
        $stmt->bindValue(':estado', $datos['estado'] ?? 'no_leida'); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function marcarLeida($id)
    {
        $sql = "UPDATE notificaciones SET estado = 'leida' WHERE id = :id"; // Marca leída
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Notificación

        return $stmt->execute(); // Ejecuta actualización
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM notificaciones WHERE id = :id"; // Elimina notificación
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Notificación

        return $stmt->execute(); // Ejecuta eliminación
    }

    public function notificarAdministrador($titulo, $mensaje)
    {
        $sql = "SELECT id FROM usuarios WHERE rol = 'administrador' AND estado = 'activo'"; // Busca admins
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene admins

        foreach ($admins as $admin) { // Recorre admins
            $this->crear([
                'usuario_id' => $admin['id'], // Usuario destino
                'titulo' => $titulo, // Título
                'mensaje' => $mensaje, // Mensaje
                'tipo' => 'sistema' // Tipo
            ]);
        }

        return true; // Finaliza proceso
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Notificaciones', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>