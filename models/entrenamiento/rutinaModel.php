<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class RutinaModel
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
        return $this->schema->tablaExiste('rutinas');
    }

    private function tabla(): string
    {
        return $this->usaEsquemaNuevo() ? 'rutinas' : 'rutina';
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_rutina'] ?? $fila['id'] ?? null;
        $fila['nombre'] = $fila['nombre'] ?? $fila['titulo'] ?? '';
        $fila['estado'] = strtolower($fila['estado_rutina'] ?? $fila['estado'] ?? 'activa');
        $fila['dias_semana'] = $fila['dias_semana'] ?? $fila['dia_semana'] ?? '';

        return $fila;
    }

    public function obtenerTodos()
    {
        $tabla = $this->tabla();
        $sql = "SELECT * FROM {$tabla} ORDER BY id_rutina DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPorId($id)
    {
        $tabla = $this->tabla();
        $sql = "SELECT * FROM {$tabla} WHERE id_rutina = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function obtenerPorCliente($clienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT r.*, r.titulo AS nombre, pe.titulo AS plan_nombre, r.estado_rutina AS estado
                    FROM rutinas r
                    INNER JOIN planes_entrenamiento pe ON pe.id_plan_entrenamiento = r.id_plan_entrenamiento
                    WHERE pe.id_cliente = :cliente_id
                    ORDER BY r.id_rutina DESC";
        } else {
            $sql = "SELECT r.*, pe.nombre AS plan_nombre
                    FROM rutina r
                    INNER JOIN plan_entrenamiento pe ON pe.id_plan_entrenamiento = r.id_plan_entrenamiento
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                    WHERE pc.id_cliente = :cliente_id
                    ORDER BY r.id_rutina DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT r.*, r.titulo AS nombre,
                           CONCAT(u.nombres, ' ', IFNULL(u.apellidos, '')) AS cliente
                    FROM rutinas r
                    INNER JOIN planes_entrenamiento pe ON pe.id_plan_entrenamiento = r.id_plan_entrenamiento
                    INNER JOIN clientes c ON c.id_cliente = pe.id_cliente
                    INNER JOIN user u ON u.id_user = c.id_user
                    WHERE pe.id_coach = :coach_id
                    ORDER BY r.id_rutina DESC";
        } else {
            $sql = "SELECT r.*, u.nombre AS cliente
                    FROM rutina r
                    INNER JOIN plan_entrenamiento pe ON pe.id_plan_entrenamiento = r.id_plan_entrenamiento
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                    INNER JOIN users u ON u.id_usuario = pc.id_cliente
                    WHERE pc.id_coach = :coach_id
                    ORDER BY r.id_rutina DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPendientesPorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT r.*, r.titulo AS nombre,
                           CONCAT(u.nombres, ' ', IFNULL(u.apellidos, '')) AS cliente,
                           rr.estado_registro
                    FROM rutinas r
                    INNER JOIN planes_entrenamiento pe ON pe.id_plan_entrenamiento = r.id_plan_entrenamiento
                    INNER JOIN clientes c ON c.id_cliente = pe.id_cliente
                    INNER JOIN user u ON u.id_user = c.id_user
                    LEFT JOIN registro_rutinas rr ON rr.id_rutina = r.id_rutina
                        AND rr.id_cliente = c.id_cliente
                        AND rr.fecha_registro = CURDATE()
                    WHERE pe.id_coach = :coach_id
                      AND pe.estado_entrenamiento = 'ACTIVO'
                      AND r.estado_rutina = 'ACTIVA'
                      AND (rr.estado_registro IS NULL OR rr.estado_registro = 'PENDIENTE')
                    ORDER BY r.id_rutina ASC";
        } else {
            $sql = "SELECT r.*, u.nombre AS cliente, rr.estado AS estado_registro
                    FROM rutina r
                    INNER JOIN plan_entrenamiento pe ON pe.id_plan_entrenamiento = r.id_plan_entrenamiento
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                    INNER JOIN users u ON u.id_usuario = pc.id_cliente
                    LEFT JOIN registro_rutina rr ON rr.id_rutina = r.id_rutina
                        AND rr.fecha = CURDATE()
                    WHERE pc.id_coach = :coach_id
                      AND pc.estado = 'ACTIVO'
                      AND (rr.estado IS NULL OR rr.estado = 'PENDIENTE')
                    ORDER BY r.id_rutina ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPorPlanEntrenamiento($planEntrenamientoId)
    {
        $tabla = $this->tabla();
        $orden = $this->usaEsquemaNuevo() ? 'orden ASC, id_rutina ASC' : 'id_rutina ASC';
        $sql = "SELECT * FROM {$tabla}
                WHERE id_plan_entrenamiento = :plan_entrenamiento_id
                ORDER BY {$orden}";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':plan_entrenamiento_id', $planEntrenamientoId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'INSERT INTO rutinas
                    (id_plan_entrenamiento, titulo, descripcion, dia_semana, orden, estado_rutina)
                    VALUES
                    (:id_plan_entrenamiento, :titulo, :descripcion, :dia_semana, :orden, :estado_rutina)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_plan_entrenamiento', $datos['id_plan_entrenamiento'] ?? $datos['plan_entrenamiento_id']);
            $stmt->bindValue(':titulo', $datos['nombre'] ?? $datos['titulo'] ?? 'Rutina');
            $stmt->bindValue(':descripcion', $datos['descripcion'] ?? $datos['observaciones'] ?? null);
            $stmt->bindValue(':dia_semana', strtoupper($datos['dia_semana'] ?? $datos['dias_semana'] ?? 'LUNES'));
            $stmt->bindValue(':orden', (int) ($datos['orden'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':estado_rutina', strtoupper($datos['estado_rutina'] ?? $datos['estado'] ?? 'ACTIVA'));

            return $stmt->execute();
        }

        $sql = 'INSERT INTO rutina
                (id_plan_entrenamiento, nombre, dias_semana, duracion_minutos, version, observaciones)
                VALUES
                (:id_plan_entrenamiento, :nombre, :dias_semana, :duracion_minutos, :version, :observaciones)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_plan_entrenamiento', $datos['id_plan_entrenamiento'] ?? $datos['plan_entrenamiento_id']);
        $stmt->bindValue(':nombre', $datos['nombre'] ?? $datos['titulo'] ?? 'Rutina');
        $stmt->bindValue(':dias_semana', $datos['dias_semana'] ?? $datos['dia_semana'] ?? '');
        $stmt->bindValue(':duracion_minutos', $datos['duracion_minutos'] ?? $datos['duracion'] ?? null);
        $stmt->bindValue(':version', $datos['version'] ?? 1);
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? $datos['descripcion'] ?? '');

        return $stmt->execute();
    }

    public function actualizar($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE rutinas
                    SET titulo = :titulo, descripcion = :descripcion, dia_semana = :dia_semana,
                        orden = :orden, estado_rutina = :estado_rutina
                    WHERE id_rutina = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':titulo', $datos['nombre'] ?? $datos['titulo'] ?? '');
            $stmt->bindValue(':descripcion', $datos['descripcion'] ?? $datos['observaciones'] ?? null);
            $stmt->bindValue(':dia_semana', strtoupper($datos['dia_semana'] ?? $datos['dias_semana'] ?? 'LUNES'));
            $stmt->bindValue(':orden', (int) ($datos['orden'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':estado_rutina', strtoupper($datos['estado_rutina'] ?? $datos['estado'] ?? 'ACTIVA'));
            $stmt->bindValue(':id', $datos['id'] ?? $datos['id_rutina'], PDO::PARAM_INT);

            return $stmt->execute();
        }

        $sql = 'UPDATE rutina
                SET nombre = :nombre, dias_semana = :dias_semana, duracion_minutos = :duracion_minutos,
                    version = :version, observaciones = :observaciones
                WHERE id_rutina = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nombre', $datos['nombre'] ?? $datos['titulo'] ?? '');
        $stmt->bindValue(':dias_semana', $datos['dias_semana'] ?? $datos['dia_semana'] ?? '');
        $stmt->bindValue(':duracion_minutos', $datos['duracion_minutos'] ?? $datos['duracion'] ?? null);
        $stmt->bindValue(':version', $datos['version'] ?? 1);
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? $datos['descripcion'] ?? '');
        $stmt->bindValue(':id', $datos['id'] ?? $datos['id_rutina']);

        return $stmt->execute();
    }

    public function eliminar($id)
    {
        $tabla = $this->tabla();
        $sql = "DELETE FROM {$tabla} WHERE id_rutina = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function reportePorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT r.estado_rutina AS estado, COUNT(*) AS total
                    FROM rutinas r
                    INNER JOIN planes_entrenamiento pe ON pe.id_plan_entrenamiento = r.id_plan_entrenamiento
                    WHERE pe.id_coach = :coach_id
                    GROUP BY r.estado_rutina";
        } else {
            $sql = "SELECT 'activa' AS estado, COUNT(*) AS total
                    FROM rutina r
                    INNER JOIN plan_entrenamiento pe ON pe.id_plan_entrenamiento = r.id_plan_entrenamiento
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                    WHERE pc.id_coach = :coach_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Rutinas', $accion);
    }
}

?>
