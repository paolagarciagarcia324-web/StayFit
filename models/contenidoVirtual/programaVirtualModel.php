<?php

require_once __DIR__ . '/../../config/database.php';

class ProgramaVirtualModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->conectar();
    }

    private function usaEsquemaNuevo(): bool
    {
        static $usaNuevo = null;

        if ($usaNuevo !== null) {
            return $usaNuevo;
        }

        try {
            $usaNuevo = (bool) $this->db->query("SHOW TABLES LIKE 'programas_virtuales'")->fetch(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            $usaNuevo = false;
        }

        return $usaNuevo;
    }

    private function tablaPrograma(): string
    {
        return $this->usaEsquemaNuevo() ? 'programas_virtuales' : 'programa_virtual';
    }

    private function tablaPlan(): string
    {
        return $this->usaEsquemaNuevo() ? 'planes' : 'plan';
    }

    private function columnaIdPrograma(): string
    {
        return $this->usaEsquemaNuevo() ? 'id_programa' : 'id_programa_virtual';
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_programa_virtual'] ?? $fila['id_programa'] ?? $fila['id'] ?? null;
        $fila['id_programa_virtual'] = $fila['id'];

        if (isset($fila['estado_programa'])) {
            $fila['activo'] = strtoupper((string) $fila['estado_programa']) === 'ACTIVO';
        } else {
            $fila['activo'] = (bool) ($fila['activo'] ?? true);
        }

        return $fila;
    }

    private function sqlSelectBase(): string
    {
        $tablaPrograma = $this->tablaPrograma();
        $tablaPlan = $this->tablaPlan();
        $colId = $this->columnaIdPrograma();

        return "SELECT pv.*, pl.nombre AS plan_nombre
                FROM {$tablaPrograma} pv
                INNER JOIN {$tablaPlan} pl ON pl.id_plan = pv.id_plan";
    }

    public function obtenerTodos()
    {
        try {
            $colId = $this->columnaIdPrograma();
            $sql = $this->sqlSelectBase() . " ORDER BY pv.{$colId} DESC";
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
        if (!$this->usaEsquemaNuevo()) {
            return $this->obtenerTodos();
        }

        try {
            $colId = $this->columnaIdPrograma();
            $sql = $this->sqlSelectBase() . " WHERE pv.estado_programa = 'ACTIVO' ORDER BY pv.{$colId} DESC";
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

    public function obtenerPorId($id)
    {
        try {
            $colId = $this->columnaIdPrograma();
            $sql = $this->sqlSelectBase() . " WHERE pv.{$colId} = :id LIMIT 1";
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
            $tabla = $this->tablaPrograma();
            $sql = "SELECT * FROM {$tabla} WHERE id_plan = :plan_id LIMIT 1";
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
            'descripcion' => 'Contenido del plan virtual FigueFit.',
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
        if ($this->usaEsquemaNuevo()) {
            $sql = "INSERT INTO programas_virtuales (id_plan, nombre, descripcion, estado_programa, creado_en)
                    VALUES (:id_plan, :nombre, :descripcion, :estado_programa, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_plan', $datos['id_plan'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
            $stmt->bindValue(
                ':estado_programa',
                !empty($datos['activo']) ? 'ACTIVO' : 'INACTIVO'
            );
            $stmt->execute();

            return (int) $this->db->lastInsertId();
        }

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
        $id = $datos['id'] ?? $datos['id_programa_virtual'] ?? $datos['id_programa'] ?? null;

        if ($this->usaEsquemaNuevo()) {
            $sql = "UPDATE programas_virtuales
                    SET nombre = :nombre,
                        descripcion = :descripcion,
                        estado_programa = :estado_programa
                    WHERE id_programa = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
            $stmt->bindValue(
                ':estado_programa',
                !empty($datos['activo']) ? 'ACTIVO' : 'INACTIVO'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        $sql = "UPDATE programa_virtual
                SET nombre = :nombre, descripcion = :descripcion, nivel = :nivel, activo = :activo
                WHERE id_programa_virtual = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
        $stmt->bindValue(':nivel', $datos['nivel'] ?? 'General');
        $stmt->bindValue(':activo', !empty($datos['activo']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        if ($this->usaEsquemaNuevo()) {
            $valor = ($estado === 'activo' || $estado === 1 || $estado === true) ? 'ACTIVO' : 'INACTIVO';
            $stmt = $this->db->prepare(
                'UPDATE programas_virtuales SET estado_programa = :estado WHERE id_programa = :id'
            );
            $stmt->bindValue(':estado', $valor);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        $activo = ($estado === 'activo' || $estado === 1 || $estado === true) ? 1 : 0;
        $stmt = $this->db->prepare(
            'UPDATE programa_virtual SET activo = :activo WHERE id_programa_virtual = :id'
        );
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
