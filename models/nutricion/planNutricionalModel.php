<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class PlanNutricionalModel
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
        return $this->schema->tablaExiste('planes_nutricionales');
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_plan_nutricional'] ?? $fila['id'] ?? null;
        $fila['nombre'] = $fila['nombre'] ?? $fila['titulo'] ?? '';
        $fila['objetivo'] = $fila['objetivo'] ?? '';
        $fila['descripcion'] = $fila['descripcion']
            ?? $fila['recomendaciones_generales']
            ?? $fila['recomendaciones_adicionales']
            ?? '';
        $fila['estado'] = strtolower($fila['estado_plan'] ?? $fila['estado_nutricional'] ?? 'activo');
        $fila['recomendaciones_adicionales'] = $fila['recomendaciones_adicionales']
            ?? $fila['recomendaciones_generales'] ?? '';

        return $fila;
    }

    private function sqlPorCliente(): string
    {
        if ($this->usaEsquemaNuevo()) {
            return 'SELECT pn.* FROM planes_nutricionales pn WHERE pn.id_cliente = :cliente_id';
        }

        return 'SELECT pn.* FROM plan_nutricional pn
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id';
    }

    public function obtenerTodos()
    {
        $tabla = $this->usaEsquemaNuevo() ? 'planes_nutricionales' : 'plan_nutricional';
        $sql = "SELECT * FROM {$tabla} ORDER BY id_plan_nutricional DESC";
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
        $tabla = $this->usaEsquemaNuevo() ? 'planes_nutricionales' : 'plan_nutricional';
        $sql = "SELECT * FROM {$tabla} WHERE id_plan_nutricional = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function listarPorCliente($clienteId)
    {
        $sql = $this->sqlPorCliente() . ' ORDER BY id_plan_nutricional DESC';
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
            $sql = $this->sqlPorCliente() . " AND pn.estado_nutricional = 'ACTIVO'
                    ORDER BY pn.id_plan_nutricional DESC LIMIT 1";
        } else {
            $sql = $this->sqlPorCliente() . " AND pn.estado_plan = 'ACTIVO'
                    ORDER BY pn.id_plan_nutricional DESC LIMIT 1";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function obtenerPorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT pn.*, CONCAT(u.nombres, ' ', IFNULL(u.apellidos, '')) AS cliente
                    FROM planes_nutricionales pn
                    INNER JOIN clientes c ON c.id_cliente = pn.id_cliente
                    INNER JOIN user u ON u.id_user = c.id_user
                    WHERE pn.id_coach = :coach_id
                    ORDER BY pn.id_plan_nutricional DESC";
        } else {
            $sql = "SELECT pn.*, u.nombre AS cliente
                    FROM plan_nutricional pn
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                    INNER JOIN users u ON u.id_usuario = pc.id_cliente
                    WHERE pc.id_coach = :coach_id
                    ORDER BY pn.id_plan_nutricional DESC";
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
        $recomendaciones = trim((string) (
            $datos['descripcion']
            ?? $datos['recomendaciones_adicionales']
            ?? $datos['recomendaciones_generales']
            ?? ''
        ));

        if ($this->usaEsquemaNuevo()) {
            $clienteId = (int) ($datos['id_cliente'] ?? $datos['cliente_id'] ?? 0);
            if ($clienteId > 0) {
                $this->finalizarPlanesCliente($clienteId);
            }

            $sql = 'INSERT INTO planes_nutricionales
                    (id_cliente, id_coach, titulo, objetivo, recomendaciones_generales, estado_nutricional, fecha_inicio)
                    VALUES
                    (:id_cliente, :id_coach, :titulo, :objetivo, :recomendaciones, :estado, CURDATE())';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_cliente', $clienteId, PDO::PARAM_INT);
            $stmt->bindValue(':id_coach', $datos['id_coach'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':titulo', $datos['nombre'] ?? $datos['titulo'] ?? 'Plan nutricional');
            $stmt->bindValue(':objetivo', $datos['objetivo'] ?? '');
            $stmt->bindValue(':recomendaciones', $recomendaciones);
            $stmt->bindValue(':estado', strtoupper($datos['estado_plan'] ?? $datos['estado'] ?? 'ACTIVO'));

            if (!$stmt->execute()) {
                return false;
            }

            return (int) $this->db->lastInsertId();
        }

        $sql = 'INSERT INTO plan_nutricional
                (id_plan_cliente, nombre, objetivo, estado_plan, recomendaciones_adicionales)
                VALUES
                (:id_plan_cliente, :nombre, :objetivo, :estado_plan, :recomendaciones_adicionales)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':objetivo', $datos['objetivo']);
        $stmt->bindValue(':estado_plan', strtoupper($datos['estado_plan'] ?? $datos['estado'] ?? 'ACTIVO'));
        $stmt->bindValue(':recomendaciones_adicionales', $recomendaciones);

        return $stmt->execute();
    }

    public function actualizar($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE planes_nutricionales
                    SET titulo = :titulo, objetivo = :objetivo,
                        recomendaciones_generales = :recomendaciones, estado_nutricional = :estado
                    WHERE id_plan_nutricional = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':titulo', $datos['nombre'] ?? $datos['titulo'] ?? '');
            $stmt->bindParam(':objetivo', $datos['objetivo']);
            $stmt->bindValue(':recomendaciones', $datos['recomendaciones_adicionales'] ?? $datos['recomendaciones_generales'] ?? '');
            $stmt->bindValue(':estado', strtoupper($datos['estado_plan'] ?? $datos['estado'] ?? 'ACTIVO'));
            $stmt->bindParam(':id', $datos['id']);

            return $stmt->execute();
        }

        $sql = 'UPDATE plan_nutricional
                SET nombre = :nombre, objetivo = :objetivo,
                    estado_plan = :estado_plan, recomendaciones_adicionales = :recomendaciones_adicionales
                WHERE id_plan_nutricional = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':objetivo', $datos['objetivo']);
        $stmt->bindParam(':estado_plan', $datos['estado_plan']);
        $stmt->bindValue(':recomendaciones_adicionales', $datos['recomendaciones_adicionales'] ?? '');
        $stmt->bindParam(':id', $datos['id']);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        $estadoBd = strtoupper($estado);

        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE planes_nutricionales SET estado_nutricional = :estado WHERE id_plan_nutricional = :id';
        } else {
            $sql = 'UPDATE plan_nutricional SET estado_plan = :estado WHERE id_plan_nutricional = :id';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':estado', $estadoBd);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function finalizarPlanesCliente($clienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "UPDATE planes_nutricionales SET estado_nutricional = 'FINALIZADO'
                    WHERE id_cliente = :cliente_id AND estado_nutricional = 'ACTIVO'";
        } else {
            $sql = "UPDATE plan_nutricional pn
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                    SET pn.estado_plan = 'FINALIZADO'
                    WHERE pc.id_cliente = :cliente_id AND pn.estado_plan = 'ACTIVO'";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);

        return $stmt->execute();
    }

    public function reportePorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT pn.estado_nutricional AS estado, COUNT(*) AS total
                    FROM planes_nutricionales pn
                    WHERE pn.id_coach = :coach_id
                    GROUP BY pn.estado_nutricional";
        } else {
            $sql = "SELECT pn.estado_plan AS estado, COUNT(*) AS total
                    FROM plan_nutricional pn
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                    WHERE pc.id_coach = :coach_id
                    GROUP BY pn.estado_plan";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Plan nutricional', $accion);
    }
}

?>
