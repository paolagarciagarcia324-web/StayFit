<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class ChatModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT * FROM chats WHERE cliente_id = :cliente_id LIMIT 1"; // Busca chat del cliente
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna chat
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT * FROM chats WHERE coach_id = :coach_id ORDER BY fecha_actualizacion DESC"; // Chats del coach
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna chats
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO chats 
                (cliente_id, coach_id, estado, fecha_creacion, fecha_actualizacion)
                VALUES 
                (:cliente_id, :coach_id, :estado, NOW(), NOW())"; // Crea chat

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $datos['cliente_id']); // Cliente
        $stmt->bindParam(':coach_id', $datos['coach_id']); // Coach
        $stmt->bindValue(':estado', $datos['estado'] ?? 'activo'); // Estado

        $stmt->execute(); // Ejecuta registro

        return $this->db->lastInsertId(); // Retorna ID
    }

    public function obtenerOCrear($clienteId, $coachId)
    {
        $chat = $this->obtenerPorCliente($clienteId); // Busca chat existente

        if ($chat) { // Si existe
            return $chat; // Retorna chat
        }

        $chatId = $this->crear([
            'cliente_id' => $clienteId, // Cliente
            'coach_id' => $coachId, // Coach
            'estado' => 'activo' // Estado inicial
        ]);

        return $this->obtenerPorId($chatId); // Retorna chat creado
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM chats WHERE id = :id LIMIT 1"; // Busca por ID
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna chat
    }

    public function actualizarFecha($id)
    {
        $sql = "UPDATE chats SET fecha_actualizacion = NOW() WHERE id = :id"; // Actualiza fecha
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Chat

        return $stmt->execute(); // Ejecuta actualización
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE chats SET estado = :estado WHERE id = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Nuevo estado
        $stmt->bindParam(':id', $id); // Chat

        return $stmt->execute(); // Ejecuta cambio
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Chat', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>