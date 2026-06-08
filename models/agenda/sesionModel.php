<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class SesionModel
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

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['fecha_hora_inicio'] = $fila['fecha_inicio'] ?? $fila['fecha_hora_inicio'] ?? null;
        $fila['fecha'] = $fila['fecha'] ?? (
            $fila['fecha_hora_inicio']
                ? date('Y-m-d', strtotime($fila['fecha_hora_inicio']))
                : ''
        );
        $fila['hora'] = $fila['hora'] ?? (
            $fila['fecha_hora_inicio']
                ? date('H:i', strtotime($fila['fecha_hora_inicio']))
                : ''
        );
        $fila['estado'] = strtolower($fila['estado_sesion'] ?? $fila['estado'] ?? 'programada');
        $fila['id'] = $fila['id_sesion'] ?? $fila['id'] ?? null;

        return $fila;
    }

    public function obtenerPorCliente($clienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT s.*
                    FROM sesiones s
                    LEFT JOIN sesion_participantes sp ON sp.id_sesion = s.id_sesion AND sp.id_cliente = :cliente_id2
                    WHERE s.id_cliente = :cliente_id OR sp.id_cliente = :cliente_id3
                    ORDER BY s.fecha_inicio ASC";
        } else {
            $sql = "SELECT s.*
                    FROM sesion s
                    INNER JOIN sesion_participante sp ON sp.id_sesion = s.id_sesion
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = sp.id_plan_cliente
                    WHERE pc.id_cliente = :cliente_id
                    ORDER BY s.fecha_hora_inicio ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        if ($this->usaEsquemaNuevo()) {
            $stmt->bindParam(':cliente_id2', $clienteId);
            $stmt->bindParam(':cliente_id3', $clienteId);
        }
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerTodos()
    {
        $tabla = $this->usaEsquemaNuevo() ? 'sesiones' : 'sesion';
        $orden = $this->usaEsquemaNuevo() ? 'fecha_inicio' : 'fecha_hora_inicio';
        $sql = "SELECT * FROM {$tabla} ORDER BY {$orden} DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPorCoach($coachId)
    {
        $tabla = $this->usaEsquemaNuevo() ? 'sesiones' : 'sesion';
        $orden = $this->usaEsquemaNuevo() ? 'fecha_inicio' : 'fecha_hora_inicio';
        $sql = "SELECT * FROM {$tabla} WHERE id_coach = :coach_id ORDER BY {$orden} ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerProximasPorCoach($coachId)
    {
        $tabla = $this->usaEsquemaNuevo() ? 'sesiones' : 'sesion';
        $colFecha = $this->usaEsquemaNuevo() ? 'fecha_inicio' : 'fecha_hora_inicio';
        $sql = "SELECT * FROM {$tabla}
                WHERE id_coach = :coach_id AND {$colFecha} >= NOW()
                ORDER BY {$colFecha} ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPorPlanCliente($planClienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT s.*
                    FROM sesiones s
                    INNER JOIN planes_cliente pc ON pc.id_plan = s.id_plan
                    WHERE pc.id_plan_cliente = :plan_cliente_id
                    ORDER BY s.fecha_inicio ASC";
        } else {
            $sql = "SELECT s.*
                    FROM sesion s
                    INNER JOIN sesion_participante sp ON sp.id_sesion = s.id_sesion
                    WHERE sp.id_plan_cliente = :plan_cliente_id
                    ORDER BY s.fecha_hora_inicio ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':plan_cliente_id', $planClienteId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerGrupalesPorPlanCliente($planClienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT s.*
                    FROM sesiones s
                    INNER JOIN planes_cliente pc ON pc.id_plan = s.id_plan
                    WHERE pc.id_plan_cliente = :plan_cliente_id AND s.tipo_sesion = 'GRUPAL'
                    ORDER BY s.fecha_inicio ASC";
        } else {
            $sql = "SELECT s.*
                    FROM sesion s
                    INNER JOIN sesion_participante sp ON sp.id_sesion = s.id_sesion
                    WHERE sp.id_plan_cliente = :plan_cliente_id AND s.tipo = 'GRUPAL'
                    ORDER BY s.fecha_hora_inicio ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':plan_cliente_id', $planClienteId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerGrupalesPorCliente($clienteId)
    {
        $clienteId = (int) $clienteId;
        if ($clienteId < 1) {
            return [];
        }

        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT DISTINCT s.*, sp.estado_asistencia
                    FROM sesiones s
                    LEFT JOIN sesion_participantes sp
                           ON sp.id_sesion = s.id_sesion AND sp.id_cliente = :cliente_id
                    LEFT JOIN clientes c ON c.id_cliente = :cliente_id2
                    LEFT JOIN planes_cliente pc
                           ON pc.id_cliente = c.id_cliente
                          AND pc.id_plan = s.id_plan
                          AND pc.estado_plan_cliente = 'ACTIVO'
                    WHERE s.tipo_sesion IN ('GRUPAL', 'INSTITUCIONAL')
                      AND s.estado_sesion <> 'CANCELADA'
                      AND (
                          s.id_cliente = :cliente_id3
                          OR sp.id_cliente = :cliente_id4
                          OR (c.id_institucion IS NOT NULL AND s.id_institucion = c.id_institucion)
                          OR pc.id_plan_cliente IS NOT NULL
                      )
                    ORDER BY s.fecha_inicio ASC";
        } else {
            $sql = "SELECT DISTINCT s.*
                    FROM sesion s
                    INNER JOIN sesion_participante sp ON sp.id_sesion = s.id_sesion
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = sp.id_plan_cliente
                    WHERE pc.id_cliente = :cliente_id
                      AND s.tipo = 'GRUPAL'
                      AND s.estado <> 'CANCELADA'
                    ORDER BY s.fecha_hora_inicio ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);

        if ($this->usaEsquemaNuevo()) {
            $stmt->bindValue(':cliente_id2', $clienteId, PDO::PARAM_INT);
            $stmt->bindValue(':cliente_id3', $clienteId, PDO::PARAM_INT);
            $stmt->bindValue(':cliente_id4', $clienteId, PDO::PARAM_INT);
        }

        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function confirmarAsistencia($datos)
    {
        $estado = strtolower(trim((string) ($datos['estado'] ?? 'confirmada')));
        $estadoAsistencia = match ($estado) {
            'confirmada', 'confirmado', 'inscrita', 'inscrito' => $this->usaEsquemaNuevo() ? 'INSCRITA' : 'INSCRITO',
            'asistio', 'asistió', 'asistio' => $this->usaEsquemaNuevo() ? 'ASISTIO' : 'ASISTIO',
            default => $this->usaEsquemaNuevo() ? 'INSCRITA' : 'INSCRITO',
        };

        $payload = [
            'id_sesion' => $datos['sesion_id'] ?? $datos['id_sesion'] ?? null,
            'id_cliente' => $datos['cliente_id'] ?? $datos['id_cliente'] ?? null,
            'cliente_id' => $datos['cliente_id'] ?? $datos['id_cliente'] ?? null,
            'estado_asistencia' => $estadoAsistencia,
            'observaciones' => $datos['observaciones'] ?? null,
        ];

        if ($this->usaEsquemaNuevo()) {
            $existe = $this->db->prepare(
                'SELECT id_sesion_participante FROM sesion_participantes
                 WHERE id_sesion = :id_sesion AND id_cliente = :id_cliente LIMIT 1'
            );
            $existe->bindValue(':id_sesion', (int) $payload['id_sesion'], PDO::PARAM_INT);
            $existe->bindValue(':id_cliente', (int) $payload['id_cliente'], PDO::PARAM_INT);
            $existe->execute();

            if (!$existe->fetch(PDO::FETCH_ASSOC)) {
                $insert = $this->db->prepare(
                    'INSERT INTO sesion_participantes (id_sesion, id_cliente, estado_asistencia, observaciones)
                     VALUES (:id_sesion, :id_cliente, :estado_asistencia, :observaciones)'
                );
                $insert->bindValue(':id_sesion', (int) $payload['id_sesion'], PDO::PARAM_INT);
                $insert->bindValue(':id_cliente', (int) $payload['id_cliente'], PDO::PARAM_INT);
                $insert->bindValue(':estado_asistencia', $estadoAsistencia);
                $insert->bindValue(':observaciones', $payload['observaciones']);
                return $insert->execute();
            }
        }

        return $this->marcarAsistencia($payload);
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'INSERT INTO sesiones
                    (id_coach, id_cliente, titulo, descripcion, fecha_inicio, fecha_fin, tipo_sesion, modalidad, estado_sesion, cupo_maximo, enlace_virtual, ubicacion)
                    VALUES
                    (:id_coach, :id_cliente, :titulo, :descripcion, :fecha_inicio, :fecha_fin, :tipo, :modalidad, :estado, :cupo_maximo, :enlace_virtual, :ubicacion)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_coach', $datos['id_coach']);
            $stmt->bindValue(':id_cliente', $datos['id_cliente'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':titulo', $datos['titulo'] ?? 'Sesión');
            $stmt->bindValue(':descripcion', $datos['descripcion'] ?? null);
            $stmt->bindParam(':fecha_inicio', $datos['fecha_hora_inicio'] ?? $datos['fecha_inicio']);
            $stmt->bindParam(':fecha_fin', $datos['fecha_hora_fin'] ?? $datos['fecha_fin']);
            $stmt->bindValue(':tipo', $datos['tipo'] ?? 'INDIVIDUAL');
            $stmt->bindValue(':modalidad', $datos['modalidad'] ?? 'VIRTUAL');
            $stmt->bindValue(':estado', $datos['estado'] ?? 'PROGRAMADA');
            $stmt->bindValue(':cupo_maximo', $datos['cupo_maximo'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':enlace_virtual', $datos['enlace_virtual'] ?? null);
            $stmt->bindValue(':ubicacion', $datos['ubicacion'] ?? null);

            return $stmt->execute();
        }

        $sql = 'INSERT INTO sesion
                (id_coach, titulo, descripcion, fecha_hora_inicio, fecha_hora_fin,
                 duracion_minutos, tipo, modalidad, estado, cupo_maximo, enlace_virtual, ubicacion)
                VALUES
                (:id_coach, :titulo, :descripcion, :fecha_hora_inicio, :fecha_hora_fin,
                 :duracion_minutos, :tipo, :modalidad, :estado, :cupo_maximo, :enlace_virtual, :ubicacion)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_coach', $datos['id_coach']);
        $stmt->bindValue(':titulo', $datos['titulo'] ?? null);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? null);
        $stmt->bindParam(':fecha_hora_inicio', $datos['fecha_hora_inicio']);
        $stmt->bindParam(':fecha_hora_fin', $datos['fecha_hora_fin']);
        $stmt->bindValue(':duracion_minutos', $datos['duracion_minutos'] ?? null);
        $stmt->bindValue(':tipo', $datos['tipo'] ?? 'INDIVIDUAL');
        $stmt->bindValue(':modalidad', $datos['modalidad'] ?? 'VIRTUAL');
        $stmt->bindValue(':estado', $datos['estado'] ?? 'PROGRAMADA');
        $stmt->bindValue(':cupo_maximo', $datos['cupo_maximo'] ?? null);
        $stmt->bindValue(':enlace_virtual', $datos['enlace_virtual'] ?? null);
        $stmt->bindValue(':ubicacion', $datos['ubicacion'] ?? null);

        return $stmt->execute();
    }

    public function inscribirParticipante($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $stmtCliente = $this->db->prepare(
                'SELECT id_cliente FROM planes_cliente WHERE id_plan_cliente = :id LIMIT 1'
            );
            $stmtCliente->bindValue(':id', $datos['id_plan_cliente'] ?? 0, PDO::PARAM_INT);
            $stmtCliente->execute();
            $clienteId = $stmtCliente->fetchColumn();

            if (!$clienteId) {
                return false;
            }

            $sql = 'INSERT INTO sesion_participantes (id_sesion, id_cliente, estado_asistencia, observaciones)
                    VALUES (:id_sesion, :id_cliente, :estado_asistencia, :observaciones)
                    ON DUPLICATE KEY UPDATE estado_asistencia = VALUES(estado_asistencia), observaciones = VALUES(observaciones)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_sesion', $datos['id_sesion']);
            $stmt->bindValue(':id_cliente', $clienteId);
            $stmt->bindValue(':estado_asistencia', $datos['estado_asistencia'] ?? 'INSCRITA');
            $stmt->bindValue(':observaciones', $datos['observaciones'] ?? null);

            return $stmt->execute();
        }

        $sql = 'INSERT INTO sesion_participante (id_sesion, id_plan_cliente, estado_asistencia, observaciones)
                VALUES (:id_sesion, :id_plan_cliente, :estado_asistencia, :observaciones)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_sesion', $datos['id_sesion']);
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']);
        $stmt->bindValue(':estado_asistencia', $datos['estado_asistencia'] ?? 'INSCRITO');
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? null);

        return $stmt->execute();
    }

    public function marcarAsistencia($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE sesion_participantes
                    SET estado_asistencia = :estado_asistencia, observaciones = :observaciones
                    WHERE id_sesion = :id_sesion AND id_cliente = :id_cliente';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado_asistencia', $datos['estado_asistencia']);
            $stmt->bindValue(':observaciones', $datos['observaciones'] ?? null);
            $stmt->bindParam(':id_sesion', $datos['id_sesion']);
            $stmt->bindValue(':id_cliente', $datos['id_cliente'] ?? $datos['cliente_id']);

            return $stmt->execute();
        }

        $sql = 'UPDATE sesion_participante
                SET estado_asistencia = :estado_asistencia, observaciones = :observaciones
                WHERE id_sesion = :id_sesion AND id_plan_cliente = :id_plan_cliente';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado_asistencia', $datos['estado_asistencia']);
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? null);
        $stmt->bindParam(':id_sesion', $datos['id_sesion']);
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE sesiones SET estado_sesion = :estado WHERE id_sesion = :id';
        } else {
            $sql = 'UPDATE sesion SET estado = :estado WHERE id_sesion = :id';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function reportePorCoach($coachId)
    {
        $tabla = $this->usaEsquemaNuevo() ? 'sesiones' : 'sesion';
        $colEstado = $this->usaEsquemaNuevo() ? 'estado_sesion' : 'estado';
        $sql = "SELECT {$colEstado} AS estado, COUNT(*) AS total
                FROM {$tabla}
                WHERE id_coach = :coach_id
                GROUP BY {$colEstado}";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Sesiones', $accion);
    }
}

?>
