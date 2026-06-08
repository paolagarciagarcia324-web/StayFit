<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class AgendaModel
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
        return $this->schema->tablaExiste('sesiones');
    }

    public function obtenerPorCliente($clienteId)
    {
        if (!$this->usaEsquemaNuevo()) {
            $sql = "SELECT a.*, a.fecha_hora_inicio,
                           DATE_FORMAT(a.fecha_hora_inicio, '%Y-%m-%d') AS fecha,
                           DATE_FORMAT(a.fecha_hora_inicio, '%H:%i') AS hora,
                           a.descripcion AS titulo
                    FROM agenda a
                    INNER JOIN sesion s ON s.id_coach = a.id_coach
                    INNER JOIN sesion_participante sp ON sp.id_sesion = s.id_sesion
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = sp.id_plan_cliente
                    WHERE pc.id_cliente = :cliente_id
                    AND a.fecha_hora_inicio >= NOW()
                    ORDER BY a.fecha_hora_inicio ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = "SELECT s.titulo, s.descripcion, s.fecha_inicio AS fecha_hora_inicio,
                       DATE_FORMAT(s.fecha_inicio, '%Y-%m-%d') AS fecha,
                       DATE_FORMAT(s.fecha_inicio, '%H:%i') AS hora,
                       s.estado_sesion AS estado, 'sesion' AS tipo
                FROM sesiones s
                LEFT JOIN sesion_participantes sp ON sp.id_sesion = s.id_sesion AND sp.id_cliente = :cliente_id2
                WHERE (s.id_cliente = :cliente_id OR sp.id_cliente = :cliente_id3)
                  AND s.fecha_inicio >= NOW()
                  AND s.estado_sesion = 'PROGRAMADA'
                UNION ALL
                SELECT e.titulo, e.descripcion, e.fecha_inicio AS fecha_hora_inicio,
                       DATE_FORMAT(e.fecha_inicio, '%Y-%m-%d') AS fecha,
                       DATE_FORMAT(e.fecha_inicio, '%H:%i') AS hora,
                       e.estado_evento AS estado, 'evento' AS tipo
                FROM eventos e
                INNER JOIN evento_participantes ep ON ep.id_evento = e.id_evento AND ep.id_cliente = :cliente_id4
                WHERE e.fecha_inicio >= NOW()
                  AND e.estado_evento = 'PUBLICADO'
                ORDER BY fecha_hora_inicio ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->bindParam(':cliente_id2', $clienteId);
        $stmt->bindParam(':cliente_id3', $clienteId);
        $stmt->bindParam(':cliente_id4', $clienteId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT s.*, s.titulo, s.fecha_inicio AS fecha_hora_inicio,
                           DATE_FORMAT(s.fecha_inicio, '%Y-%m-%d') AS fecha,
                           DATE_FORMAT(s.fecha_inicio, '%H:%i') AS hora
                    FROM sesiones s
                    WHERE s.id_coach = :coach_id
                    ORDER BY s.fecha_inicio ASC";
        } else {
            $sql = 'SELECT * FROM agenda WHERE id_coach = :coach_id ORDER BY fecha_hora_inicio ASC';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDisponiblesPorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT s.*, s.titulo, s.fecha_inicio AS fecha_hora_inicio
                    FROM sesiones s
                    WHERE s.id_coach = :coach_id
                    AND s.estado_sesion = 'PROGRAMADA'
                    AND s.fecha_inicio >= NOW()
                    ORDER BY s.fecha_inicio ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':coach_id', $coachId);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = "SELECT * FROM agenda
                WHERE id_coach = :coach_id
                AND disponible = 1
                AND fecha_hora_inicio >= NOW()
                ORDER BY fecha_hora_inicio ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'INSERT INTO sesiones
                    (id_coach, titulo, descripcion, fecha_inicio, fecha_fin, estado_sesion, modalidad)
                    VALUES
                    (:id_coach, :titulo, :descripcion, :fecha_inicio, :fecha_fin, :estado, :modalidad)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_coach', $datos['id_coach']);
            $stmt->bindValue(':titulo', $datos['descripcion'] ?? $datos['titulo'] ?? 'Sesión');
            $stmt->bindValue(':descripcion', $datos['descripcion'] ?? null);
            $stmt->bindParam(':fecha_inicio', $datos['fecha_hora_inicio']);
            $stmt->bindParam(':fecha_fin', $datos['fecha_hora_fin']);
            $stmt->bindValue(':estado', 'PROGRAMADA');
            $stmt->bindValue(':modalidad', 'VIRTUAL');

            return $stmt->execute();
        }

        $sql = 'INSERT INTO agenda (id_coach, fecha_hora_inicio, fecha_hora_fin, disponible, descripcion)
                VALUES (:id_coach, :fecha_hora_inicio, :fecha_hora_fin, :disponible, :descripcion)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_coach', $datos['id_coach']);
        $stmt->bindParam(':fecha_hora_inicio', $datos['fecha_hora_inicio']);
        $stmt->bindParam(':fecha_hora_fin', $datos['fecha_hora_fin']);
        $stmt->bindValue(':disponible', $datos['disponible'] ?? 1);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? null);

        return $stmt->execute();
    }

    public function cambiarDisponibilidad($id, $disponible)
    {
        if ($this->usaEsquemaNuevo()) {
            $estado = $disponible ? 'PROGRAMADA' : 'CANCELADA';
            $sql = 'UPDATE sesiones SET estado_sesion = :estado WHERE id_sesion = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado', $estado);
        } else {
            $sql = 'UPDATE agenda SET disponible = :disponible WHERE id_agenda = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':disponible', $disponible);
        }

        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Agenda', $accion);
    }
}

?>
