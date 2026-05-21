<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class MensajeModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT * FROM mensajes 
                WHERE cliente_id = :cliente_id 
                ORDER BY fecha ASC"; // Mensajes del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna mensajes
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT * FROM mensajes 
                WHERE coach_id = :coach_id 
                ORDER BY fecha DESC"; // Mensajes del coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna mensajes
    }

    public function obtenerNoLeidosPorCoach($coachId)
    {
        $sql = "SELECT * FROM mensajes 
                WHERE coach_id = :coach_id 
                AND emisor <> 'coach'
                AND estado = 'no_leido'
                ORDER BY fecha DESC"; // Mensajes pendientes

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna no leídos
    }

    public function crear($datos)
    {
        $coachId = $datos['coach_id'] ?? $this->obtenerCoachCliente($datos['cliente_id']); // Obtiene coach

        $sql = "INSERT INTO mensajes 
                (cliente_id, coach_id, mensaje, emisor, estado, fecha)
                VALUES 
                (:cliente_id, :coach_id, :mensaje, :emisor, :estado, NOW())"; // Crea mensaje

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $datos['cliente_id']); // Cliente
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->bindParam(':mensaje', $datos['mensaje']); // Mensaje
        $stmt->bindParam(':emisor', $datos['emisor']); // Emisor
        $stmt->bindValue(':estado', $datos['estado'] ?? 'no_leido'); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    private function obtenerCoachCliente($clienteId)
    {
        $sql = "SELECT coach_id FROM clientes WHERE id = :cliente_id LIMIT 1"; // Busca coach cliente
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        $cliente = $stmt->fetch(PDO::FETCH_ASSOC); // Obtiene cliente

        return $cliente['coach_id'] ?? null; // Retorna coach
    }

    public function marcarLeidosCliente($clienteId)
    {
        $sql = "UPDATE mensajes 
                SET estado = 'leido'
                WHERE cliente_id = :cliente_id"; // Marca leídos cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente

        return $stmt->execute(); // Ejecuta actualización
    }

    public function marcarLeidosCoach($coachId)
    {
        $sql = "UPDATE mensajes 
                SET estado = 'leido'
                WHERE coach_id = :coach_id"; // Marca leídos coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach

        return $stmt->execute(); // Ejecuta actualización
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Mensajes', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>
