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
        $sql = "SELECT * FROM chat WHERE id_cliente = :cliente_id LIMIT 1"; // Busca chat del cliente
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna chat
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT c.*, CONCAT(u.nombre, ' ', IFNULL(u.apellido, '')) AS cliente, u.correo AS cliente_correo
                FROM chat c
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                WHERE c.id_coach = :coach_id
                ORDER BY c.fecha_creacion DESC";
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna chats
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO chat 
                (id_cliente, id_coach, fecha_creacion, es_temporal, fecha_expiracion)
                VALUES 
                (:id_cliente, :id_coach, NOW(), :es_temporal, :fecha_expiracion)"; // Crea chat

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindValue(':id_cliente', $datos['id_cliente'], PDO::PARAM_INT);
        $stmt->bindValue(':id_coach', $datos['id_coach'], PDO::PARAM_INT);
        $stmt->bindValue(':es_temporal', $datos['es_temporal'] ?? 0); // Temporal
        $stmt->bindValue(':fecha_expiracion', $datos['fecha_expiracion'] ?? null); // Expiración

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
            'id_cliente' => $clienteId, // Cliente
            'id_coach'   => $coachId,   // Coach
        ]);

        return $this->obtenerPorId($chatId); // Retorna chat creado
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM chat WHERE id_chat = :id LIMIT 1"; // Busca por ID
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna chat
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Chat', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>
