<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/chatModel.php';
require_once __DIR__ . '/../cliente/clienteModel.php';

class MensajeModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
    }

    private function normalizarMensaje(array $fila, $clienteId = null, $coachId = null)
    {
        $fila['mensaje'] = $fila['contenido'] ?? $fila['mensaje'] ?? '';
        $fila['fecha'] = $fila['fecha_envio'] ?? $fila['fecha'] ?? '';

        if ($clienteId !== null && $coachId === null) {
            $fila['emisor'] = ((int) ($fila['id_usuario_remitente'] ?? 0) === (int) $clienteId) ? 'cliente' : 'coach';
        } elseif ($coachId !== null && $clienteId === null) {
            $fila['emisor'] = ((int) ($fila['id_usuario_remitente'] ?? 0) === (int) $coachId) ? 'coach' : 'cliente';
        } elseif ($clienteId !== null && $coachId !== null) {
            $remitente = (int) ($fila['id_usuario_remitente'] ?? 0);
            if ($remitente === (int) $coachId) {
                $fila['emisor'] = 'coach';
            } elseif ($remitente === (int) $clienteId) {
                $fila['emisor'] = 'cliente';
            } else {
                $fila['emisor'] = $fila['emisor'] ?? 'usuario';
            }
        } else {
            $fila['emisor'] = $fila['emisor'] ?? 'coach';
        }

        return $fila;
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT m.*
                FROM mensaje m
                INNER JOIN chat c ON c.id_chat = m.id_chat
                WHERE c.id_cliente = :cliente_id
                ORDER BY m.fecha_envio ASC"; // Mensajes del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute();

        $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];

        foreach ($lista as $fila) {
            $resultado[] = $this->normalizarMensaje($fila, $clienteId);
        }

        return $resultado;
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT m.*, c.id_cliente, c.id_coach
                FROM mensaje m
                INNER JOIN chat c ON c.id_chat = m.id_chat
                WHERE c.id_coach = :coach_id
                ORDER BY m.fecha_envio DESC
                LIMIT 200";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];

        foreach ($lista as $fila) {
            $resultado[] = $this->normalizarMensaje(
                $fila,
                (int) ($fila['id_cliente'] ?? 0),
                (int) $coachId
            );
        }

        return $resultado;
    }

    public function obtenerNoLeidosPorCoach($coachId)
    {
        $sql = "SELECT m.*
                FROM mensaje m
                INNER JOIN chat c ON c.id_chat = m.id_chat
                WHERE c.id_coach = :coach_id
                AND m.id_usuario_remitente <> :coach_id2
                AND m.leido = 0
                ORDER BY m.fecha_envio DESC"; // Mensajes no leídos del coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->bindParam(':coach_id2', $coachId); // Coach (segundo bind)
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna mensajes no leídos
    }

    public function obtenerPorChat($chatId)
    {
        $sql = "SELECT * FROM mensaje 
                WHERE id_chat = :chat_id 
                ORDER BY fecha_envio ASC"; // Mensajes del chat

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':chat_id', $chatId); // Chat
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna mensajes
    }

    public function obtenerNoLeidosPorChat($chatId, $usuarioId)
    {
        $sql = "SELECT * FROM mensaje 
                WHERE id_chat = :chat_id 
                AND id_usuario_remitente <> :usuario_id
                AND leido = 0
                ORDER BY fecha_envio DESC"; // Mensajes no leídos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':chat_id', $chatId); // Chat
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario actual
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna no leídos
    }

    public function crear($datos)
    {
        if (empty($datos['id_chat']) && !empty($datos['coach_id']) && !empty($datos['cliente_id'])) {
            return $this->crearDesdeCoach($datos);
        }

        if (empty($datos['id_chat']) && (!empty($datos['cliente_id']) || !empty($datos['mensaje']))) {
            return $this->crearDesdeCliente($datos);
        }

        $sql = "INSERT INTO mensaje 
                (id_chat, id_usuario_remitente, contenido, tipo_mensaje, url_adjunto, fecha_envio, leido)
                VALUES 
                (:id_chat, :id_usuario_remitente, :contenido, :tipo_mensaje, :url_adjunto, NOW(), 0)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_chat', $datos['id_chat']);
        $stmt->bindParam(':id_usuario_remitente', $datos['id_usuario_remitente']);
        $stmt->bindParam(':contenido', $datos['contenido']);
        $stmt->bindValue(':tipo_mensaje', $datos['tipo_mensaje'] ?? 'TEXTO');
        $stmt->bindValue(':url_adjunto', $datos['url_adjunto'] ?? null);

        return $stmt->execute();
    }

    public function crearDesdeCliente(array $datos)
    {
        $clienteId = (int) ($datos['cliente_id'] ?? $datos['id_cliente'] ?? 0);
        $contenido = trim($datos['contenido'] ?? $datos['mensaje'] ?? '');
        $remitenteId = (int) ($datos['id_usuario_remitente'] ?? $datos['usuario_id'] ?? $clienteId);

        if ($clienteId < 1 || $contenido === '') {
            return false;
        }

        $clienteModel = new ClienteModel();
        $coachId = (int) ($datos['coach_id'] ?? $clienteModel->obtenerIdCoachAsignado($clienteId) ?? 0);

        if ($coachId < 1) {
            throw new RuntimeException('Aún no tienes un coach asignado. Contacta al administrador.');
        }

        $chatModel = new ChatModel();
        $chat = $chatModel->obtenerOCrear($clienteId, $coachId);
        $chatId = (int) ($chat['id_chat'] ?? 0);

        if ($chatId < 1) {
            return false;
        }

        return $this->crear([
            'id_chat' => $chatId,
            'id_usuario_remitente' => $remitenteId,
            'contenido' => $contenido,
            'tipo_mensaje' => $datos['tipo_mensaje'] ?? 'TEXTO',
            'url_adjunto' => $datos['url_adjunto'] ?? null,
        ]);
    }

    public function crearDesdeCoach(array $datos)
    {
        $coachId = (int) ($datos['coach_id'] ?? 0);
        $clienteId = (int) ($datos['cliente_id'] ?? 0);
        $contenido = trim($datos['contenido'] ?? $datos['mensaje'] ?? '');
        $remitenteId = (int) ($datos['id_usuario_remitente'] ?? $datos['usuario_id'] ?? $coachId);

        if ($coachId < 1 || $clienteId < 1 || $contenido === '') {
            return false;
        }

        $chatModel = new ChatModel();
        $chat = $chatModel->obtenerOCrear($clienteId, $coachId);
        $chatId = (int) ($chat['id_chat'] ?? 0);

        if ($chatId < 1) {
            return false;
        }

        return $this->crear([
            'id_chat' => $chatId,
            'id_usuario_remitente' => $remitenteId,
            'contenido' => $contenido,
            'tipo_mensaje' => $datos['tipo_mensaje'] ?? 'TEXTO',
            'url_adjunto' => $datos['url_adjunto'] ?? null,
        ]);
    }

    public function marcarLeidosPorChat($chatId, $usuarioId)
    {
        $sql = "UPDATE mensaje 
                SET leido = 1, fecha_lectura = NOW()
                WHERE id_chat = :chat_id
                AND id_usuario_remitente <> :usuario_id
                AND leido = 0"; // Marca leídos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':chat_id', $chatId); // Chat
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario actual

        return $stmt->execute(); // Ejecuta actualización
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $id = $usuarioId ? (int) $usuarioId : null;

        return registrarBitacora($this->db, $id, 'Mensajes', $accion);
    }
}

?>
