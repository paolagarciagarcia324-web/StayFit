<?php

require_once __DIR__ . '/../../config/database.php';

class ProgramaVirtualModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->conectar();
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_programa_virtual'] ?? $fila['id'] ?? null;
        $fila['activo'] = (bool) ($fila['activo'] ?? true);

        return $fila;
    }

    public function obtenerTodos()
    {
        try {
            $sql = "SELECT pv.*, pl.nombre AS plan_nombre
                    FROM programa_virtual pv
                    INNER JOIN plan pl ON pl.id_plan = pv.id_plan
                    ORDER BY pv.id_programa_virtual DESC";
            $stmt = $this->db->query($sql);
            $lista = [];

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $lista[] = $this->normalizarFila($fila);
            }

            return $lista;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerActivos()
    {
        return $this->obtenerTodos();
    }

    public function obtenerPorId($id)
    {
        try {
            $sql = "SELECT pv.*, pl.nombre AS plan_nombre
                    FROM programa_virtual pv
                    INNER JOIN plan pl ON pl.id_plan = pv.id_plan
                    WHERE pv.id_programa_virtual = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return null;
        }
    }

    public function obtenerPorPlan($planId)
    {
        try {
            $sql = "SELECT * FROM programa_virtual WHERE id_plan = :plan_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':plan_id', $planId, PDO::PARAM_INT);
            $stmt->execute();

            return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return null;
        }
    }

    public function obtenerOcrearPorPlan($planId, ?string $nombrePlan = null)
    {
        $existente = $this->obtenerPorPlan($planId);

        if ($existente) {
            return $existente;
        }

        $nombre = $nombrePlan ? 'Programa ' . $nombrePlan : 'Programa virtual';

        $id = $this->crear([
            'id_plan' => $planId,
            'nombre' => $nombre,
            'descripcion' => 'Contenido del plan virtual StayFit.',
            'nivel' => 'General',
            'activo' => 1,
        ]);

        return $this->obtenerPorId($id);
    }

    public function asignarCliente($clienteId, $planId)
    {
        require_once __DIR__ . '/../cliente/clienteModel.php';

        $clienteModel = new ClienteModel();

        return $clienteModel->asignarPlanCliente($clienteId, $planId, null);
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO programa_virtual (id_plan, nombre, descripcion, nivel, activo)
                VALUES (:id_plan, :nombre, :descripcion, :nivel, :activo)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_plan', $datos['id_plan'], PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
        $stmt->bindValue(':nivel', $datos['nivel'] ?? 'General');
        $stmt->bindValue(':activo', !empty($datos['activo']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE programa_virtual
                SET nombre = :nombre, descripcion = :descripcion, nivel = :nivel, activo = :activo
                WHERE id_programa_virtual = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
        $stmt->bindValue(':nivel', $datos['nivel'] ?? 'General');
        $stmt->bindValue(':activo', !empty($datos['activo']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $datos['id'] ?? $datos['id_programa_virtual'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        $activo = ($estado === 'activo' || $estado === 1 || $estado === true) ? 1 : 0;
        $stmt = $this->db->prepare('UPDATE programa_virtual SET activo = :activo WHERE id_programa_virtual = :id');
        $stmt->bindValue(':activo', $activo, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Programa virtual', $accion);
    }
}

?>
