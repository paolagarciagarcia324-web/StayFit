<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class PlanEntrenamientoModel
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
        return $this->schema->tablaExiste('planes_entrenamiento');
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_plan_entrenamiento'] ?? $fila['id'] ?? null;
        $fila['nombre'] = $fila['nombre'] ?? $fila['titulo'] ?? '';
        $fila['estado'] = strtolower($fila['estado_plan'] ?? $fila['estado_entrenamiento'] ?? 'activo');
        $fila['nivel'] = $fila['nivel_dificultad'] ?? $fila['nivel'] ?? '';
        $fila['duracion'] = $fila['duracion_total_dias'] ?? $fila['duracion'] ?? null;

        return $fila;
    }

    private function sqlPorCliente(): string
    {
        if ($this->usaEsquemaNuevo()) {
            return 'SELECT pe.* FROM planes_entrenamiento pe WHERE pe.id_cliente = :cliente_id';
        }

        return 'SELECT pe.* FROM plan_entrenamiento pe
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id';
    }

    public function obtenerTodos()
    {
        $tabla = $this->usaEsquemaNuevo() ? 'planes_entrenamiento' : 'plan_entrenamiento';
        $sql = "SELECT * FROM {$tabla} ORDER BY id_plan_entrenamiento DESC";
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
        $tabla = $this->usaEsquemaNuevo() ? 'planes_entrenamiento' : 'plan_entrenamiento';
        $sql = "SELECT * FROM {$tabla} WHERE id_plan_entrenamiento = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function listarPorCliente($clienteId)
    {
        $sql = $this->sqlPorCliente() . ' ORDER BY id_plan_entrenamiento DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPorCliente($clienteId)
    {
        $activo = $this->obtenerActivoPorCliente($clienteId);

        if ($activo) {
            return $activo;
        }

        $lista = $this->listarPorCliente($clienteId);

        return $lista[0] ?? false;
    }

    public function obtenerActivoPorCliente($clienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = $this->sqlPorCliente() . " AND pe.estado_entrenamiento = 'ACTIVO'
                    ORDER BY pe.id_plan_entrenamiento DESC LIMIT 1";
        } else {
            $sql = $this->sqlPorCliente() . " AND pe.estado_plan = 'ACTIVO'
                    ORDER BY pe.id_plan_entrenamiento DESC LIMIT 1";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function obtenerPorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT pe.*, CONCAT(u.nombres, ' ', IFNULL(u.apellidos, '')) AS cliente
                    FROM planes_entrenamiento pe
                    INNER JOIN clientes c ON c.id_cliente = pe.id_cliente
                    INNER JOIN user u ON u.id_user = c.id_user
                    WHERE pe.id_coach = :coach_id
                    ORDER BY pe.id_plan_entrenamiento DESC";
        } else {
            $sql = "SELECT pe.*, u.nombre AS cliente
                    FROM plan_entrenamiento pe
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                    INNER JOIN users u ON u.id_usuario = pc.id_cliente
                    WHERE pc.id_coach = :coach_id
                    ORDER BY pe.id_plan_entrenamiento DESC";
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

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'INSERT INTO planes_entrenamiento
                    (id_cliente, id_plan, id_coach, titulo, objetivo, nivel, estado_entrenamiento)
                    VALUES
                    (:id_cliente, :id_plan, :id_coach, :titulo, :objetivo, :nivel, :estado)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_cliente', $datos['id_cliente'] ?? $datos['cliente_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':id_plan', $datos['id_plan'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':id_coach', $datos['id_coach'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':titulo', $datos['nombre'] ?? $datos['titulo'] ?? 'Plan de entrenamiento');
            $stmt->bindValue(':objetivo', $datos['objetivo'] ?? '');
            $stmt->bindValue(':nivel', strtoupper($datos['nivel_dificultad'] ?? $datos['nivel'] ?? 'PRINCIPIANTE'));
            $stmt->bindValue(':estado', strtoupper($datos['estado_plan'] ?? $datos['estado'] ?? 'ACTIVO'));

            return $stmt->execute();
        }

        $sql = 'INSERT INTO plan_entrenamiento
                (id_plan_cliente, nombre, objetivo, nivel_dificultad, duracion_total_dias, estado_plan)
                VALUES
                (:id_plan_cliente, :nombre, :objetivo, :nivel_dificultad, :duracion_total_dias, :estado_plan)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':objetivo', $datos['objetivo']);
        $stmt->bindValue(':nivel_dificultad', $datos['nivel_dificultad'] ?? $datos['nivel'] ?? '');
        $stmt->bindValue(':duracion_total_dias', $datos['duracion_total_dias'] ?? $datos['duracion'] ?? null);
        $stmt->bindValue(':estado_plan', strtoupper($datos['estado_plan'] ?? $datos['estado'] ?? 'ACTIVO'));

        return $stmt->execute();
    }

    public function actualizar($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE planes_entrenamiento
                    SET titulo = :titulo, objetivo = :objetivo, nivel = :nivel, estado_entrenamiento = :estado
                    WHERE id_plan_entrenamiento = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':titulo', $datos['nombre'] ?? $datos['titulo'] ?? '');
            $stmt->bindParam(':objetivo', $datos['objetivo']);
            $stmt->bindValue(':nivel', strtoupper($datos['nivel_dificultad'] ?? $datos['nivel'] ?? 'PRINCIPIANTE'));
            $stmt->bindValue(':estado', strtoupper($datos['estado_plan'] ?? $datos['estado'] ?? 'ACTIVO'));
            $stmt->bindParam(':id', $datos['id']);

            return $stmt->execute();
        }

        $sql = 'UPDATE plan_entrenamiento
                SET nombre = :nombre, objetivo = :objetivo, nivel_dificultad = :nivel_dificultad,
                    duracion_total_dias = :duracion_total_dias, estado_plan = :estado_plan
                WHERE id_plan_entrenamiento = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':objetivo', $datos['objetivo']);
        $stmt->bindParam(':nivel_dificultad', $datos['nivel_dificultad'] ?? $datos['nivel']);
        $stmt->bindValue(':duracion_total_dias', $datos['duracion_total_dias'] ?? $datos['duracion']);
        $stmt->bindParam(':estado_plan', $datos['estado_plan'] ?? $datos['estado']);
        $stmt->bindParam(':id', $datos['id']);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        $estadoBd = strtoupper($estado);

        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE planes_entrenamiento SET estado_entrenamiento = :estado WHERE id_plan_entrenamiento = :id';
            $col = ':estado';
        } else {
            $sql = 'UPDATE plan_entrenamiento SET estado_plan = :estado WHERE id_plan_entrenamiento = :id';
            $col = ':estado';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue($col, $estadoBd);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function finalizarPlanesCliente($clienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "UPDATE planes_entrenamiento SET estado_entrenamiento = 'FINALIZADO'
                    WHERE id_cliente = :cliente_id AND estado_entrenamiento = 'ACTIVO'";
        } else {
            $sql = "UPDATE plan_entrenamiento pe
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                    SET pe.estado_plan = 'FINALIZADO'
                    WHERE pc.id_cliente = :cliente_id AND pe.estado_plan = 'ACTIVO'";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);

        return $stmt->execute();
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Plan entrenamiento', $accion);
    }
}

?>
