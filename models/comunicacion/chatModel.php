<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class ChatModel
{
    private $db;
    private SchemaHelper $schema;

    public function __construct()
    {
        $this->db = (new Database())->conectar();
        $this->schema = new SchemaHelper($this->db);
    }

    private function usaEsquemaNuevo(): bool
    {
        return $this->schema->tablaExiste('chats');
    }

    private function tabla(): string
    {
        return $this->usaEsquemaNuevo() ? 'chats' : 'chat';
    }

    public function obtenerPorCliente($clienteId)
    {
        $tabla = $this->tabla();
        $sql = "SELECT * FROM {$tabla} WHERE id_cliente = :cliente_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT c.*, CONCAT(u.nombres, ' ', IFNULL(u.apellidos, '')) AS cliente, u.correo AS cliente_correo
                    FROM chats c
                    INNER JOIN clientes cl ON cl.id_cliente = c.id_cliente
                    INNER JOIN user u ON u.id_user = cl.id_user
                    WHERE c.id_coach = :coach_id
                    ORDER BY c.creado_en DESC";
        } else {
            $sql = "SELECT c.*, CONCAT(u.nombre, ' ', IFNULL(u.apellido, '')) AS cliente, u.correo AS cliente_correo
                    FROM chat c
                    INNER JOIN users u ON u.id_usuario = c.id_cliente
                    WHERE c.id_coach = :coach_id
                    ORDER BY c.fecha_creacion DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "INSERT INTO chats (tipo_chat, id_cliente, id_coach, estado_chat, creado_en)
                    VALUES ('CLIENTE_COACH', :id_cliente, :id_coach, 'ACTIVO', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_cliente', $datos['id_cliente'], PDO::PARAM_INT);
            $stmt->bindValue(':id_coach', $datos['id_coach'], PDO::PARAM_INT);
            $stmt->execute();

            return (int) $this->db->lastInsertId();
        }

        $sql = "INSERT INTO chat (id_cliente, id_coach, fecha_creacion, es_temporal, fecha_expiracion)
                VALUES (:id_cliente, :id_coach, NOW(), :es_temporal, :fecha_expiracion)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_cliente', $datos['id_cliente'], PDO::PARAM_INT);
        $stmt->bindValue(':id_coach', $datos['id_coach'], PDO::PARAM_INT);
        $stmt->bindValue(':es_temporal', $datos['es_temporal'] ?? 0);
        $stmt->bindValue(':fecha_expiracion', $datos['fecha_expiracion'] ?? null);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function obtenerOCrear($clienteId, $coachId)
    {
        $chat = $this->obtenerPorCliente($clienteId);

        if ($chat) {
            return $chat;
        }

        $chatId = $this->crear([
            'id_cliente' => $clienteId,
            'id_coach' => $coachId,
        ]);

        return $this->obtenerPorId($chatId);
    }

    public function obtenerPorId($id)
    {
        $tabla = $this->tabla();
        $sql = "SELECT * FROM {$tabla} WHERE id_chat = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Chat', $accion);
    }
}

?>
