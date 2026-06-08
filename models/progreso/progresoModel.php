<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class ProgresoModel
{
    private $db;
    private SchemaHelper $schema;
    private $rutaBase;

    public function __construct()
    {
        $this->db = (new Database())->conectar();
        $this->schema = new SchemaHelper($this->db);
        $this->rutaBase = __DIR__ . '/../../public/uploads/progresos/';
    }

    private function usaEsquemaNuevo(): bool
    {
        return $this->schema->tablaExiste('registros_progreso');
    }

    private function tabla(): string
    {
        return $this->usaEsquemaNuevo() ? 'registros_progreso' : 'registro_progreso';
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_registro_progreso'] ?? $fila['id'] ?? null;
        $fila['fecha'] = $fila['fecha_registro'] ?? $fila['fecha'] ?? '';
        $fila['peso'] = $fila['peso_kg'] ?? $fila['peso'] ?? null;
        $fila['cintura'] = $fila['cintura_cm'] ?? $fila['cintura'] ?? null;
        $fila['cadera'] = $fila['cadera_cm'] ?? $fila['cadera'] ?? null;
        $fila['brazos'] = $fila['brazo_cm'] ?? $fila['brazos'] ?? null;
        $fila['piernas'] = $fila['pierna_cm'] ?? $fila['piernas'] ?? null;
        $fila['fotos_evolucion'] = $fila['foto_url'] ?? $fila['fotos_evolucion'] ?? null;
        $fila['observacion'] = $fila['observaciones_cliente'] ?? $fila['observacion_cliente'] ?? '';

        return $fila;
    }

    private function sqlPorCliente(): string
    {
        if ($this->usaEsquemaNuevo()) {
            return 'SELECT rp.* FROM registros_progreso rp WHERE rp.id_cliente = :cliente_id';
        }

        return 'SELECT rp.* FROM registro_progreso rp
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = rp.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id';
    }

    public function obtenerTodos()
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT rp.*, CONCAT(u.nombres, ' ', IFNULL(u.apellidos, '')) AS cliente
                    FROM registros_progreso rp
                    LEFT JOIN clientes c ON c.id_cliente = rp.id_cliente
                    LEFT JOIN user u ON u.id_user = c.id_user
                    ORDER BY rp.fecha_registro DESC";
        } else {
            $sql = "SELECT rp.*, u.nombre AS cliente
                    FROM registro_progreso rp
                    LEFT JOIN plan_cliente pc ON pc.id_plan_cliente = rp.id_plan_cliente
                    LEFT JOIN users u ON u.id_usuario = pc.id_cliente
                    ORDER BY rp.fecha DESC";
        }

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
        $sql = "SELECT * FROM {$tabla} WHERE id_registro_progreso = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function obtenerPorCliente($clienteId)
    {
        $orden = $this->usaEsquemaNuevo() ? 'rp.fecha_registro DESC' : 'rp.fecha DESC';
        $sql = $this->sqlPorCliente() . " ORDER BY {$orden}";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerUltimoPorCliente($clienteId)
    {
        $orden = $this->usaEsquemaNuevo() ? 'rp.fecha_registro DESC' : 'rp.fecha DESC';
        $sql = $this->sqlPorCliente() . " ORDER BY {$orden} LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function obtenerPorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT rp.*, CONCAT(u.nombres, ' ', IFNULL(u.apellidos, '')) AS cliente
                    FROM registros_progreso rp
                    INNER JOIN clientes c ON c.id_cliente = rp.id_cliente
                    INNER JOIN user u ON u.id_user = c.id_user
                    WHERE c.id_coach = :coach_id
                    ORDER BY rp.fecha_registro DESC";
        } else {
            $sql = "SELECT rp.*, u.nombre AS cliente
                    FROM registro_progreso rp
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = rp.id_plan_cliente
                    INNER JOIN users u ON u.id_usuario = pc.id_cliente
                    WHERE pc.id_coach = :coach_id
                    ORDER BY rp.fecha DESC";
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

    public function registrar($datos)
    {
        $foto = $this->guardarFoto($datos);

        if ($this->usaEsquemaNuevo()) {
            $sql = 'INSERT INTO registros_progreso
                    (id_cliente, fecha_registro, peso_kg, cintura_cm, cadera_cm, brazo_cm, pierna_cm, foto_url, observaciones_cliente)
                    VALUES
                    (:id_cliente, :fecha, :peso, :cintura, :cadera, :brazos, :piernas, :foto, :observacion)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_cliente', $datos['cliente_id'] ?? $datos['id_cliente']);
            $stmt->bindValue(':fecha', $datos['fecha'] ?? date('Y-m-d'));
            $stmt->bindParam(':peso', $datos['peso']);
            $stmt->bindValue(':cintura', $datos['cintura'] ?? null);
            $stmt->bindValue(':cadera', $datos['cadera'] ?? null);
            $stmt->bindValue(':brazos', $datos['brazos'] ?? null);
            $stmt->bindValue(':piernas', $datos['piernas'] ?? null);
            $stmt->bindValue(':foto', $foto);
            $stmt->bindValue(':observacion', $datos['observacion'] ?? '');

            return $stmt->execute();
        }

        $sql = 'INSERT INTO registro_progreso
                (id_plan_cliente, fecha, peso, cintura, cadera, brazos, piernas, fotos_evolucion, observacion_cliente)
                VALUES
                (:id_plan_cliente, :fecha, :peso, :cintura, :cadera, :brazos, :piernas, :fotos_evolucion, :observacion_cliente)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']);
        $stmt->bindValue(':fecha', $datos['fecha'] ?? date('Y-m-d'));
        $stmt->bindParam(':peso', $datos['peso']);
        $stmt->bindValue(':cintura', $datos['cintura'] ?? null);
        $stmt->bindValue(':cadera', $datos['cadera'] ?? null);
        $stmt->bindValue(':brazos', $datos['brazos'] ?? null);
        $stmt->bindValue(':piernas', $datos['piernas'] ?? null);
        $stmt->bindValue(':fotos_evolucion', $foto);
        $stmt->bindValue(':observacion_cliente', $datos['observacion'] ?? '');

        return $stmt->execute();
    }

    public function actualizar($datos)
    {
        $foto = $this->guardarFoto($datos);

        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE registros_progreso
                    SET peso_kg = :peso, cintura_cm = :cintura, cadera_cm = :cadera,
                        brazo_cm = :brazos, pierna_cm = :piernas,
                        foto_url = COALESCE(:foto, foto_url),
                        observaciones_cliente = :observacion
                    WHERE id_registro_progreso = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':peso', $datos['peso']);
            $stmt->bindValue(':cintura', $datos['cintura'] ?? null);
            $stmt->bindValue(':cadera', $datos['cadera'] ?? null);
            $stmt->bindValue(':brazos', $datos['brazos'] ?? null);
            $stmt->bindValue(':piernas', $datos['piernas'] ?? null);
            $stmt->bindValue(':foto', $foto);
            $stmt->bindValue(':observacion', $datos['observacion'] ?? '');
            $stmt->bindParam(':id', $datos['id']);

            return $stmt->execute();
        }

        $sql = 'UPDATE registro_progreso
                SET peso = :peso, cintura = :cintura, cadera = :cadera,
                    brazos = :brazos, piernas = :piernas,
                    fotos_evolucion = COALESCE(:fotos_evolucion, fotos_evolucion),
                    observacion_cliente = :observacion_cliente
                WHERE id_registro_progreso = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':peso', $datos['peso']);
        $stmt->bindValue(':cintura', $datos['cintura'] ?? null);
        $stmt->bindValue(':cadera', $datos['cadera'] ?? null);
        $stmt->bindValue(':brazos', $datos['brazos'] ?? null);
        $stmt->bindValue(':piernas', $datos['piernas'] ?? null);
        $stmt->bindValue(':fotos_evolucion', $foto);
        $stmt->bindValue(':observacion_cliente', $datos['observacion'] ?? '');
        $stmt->bindParam(':id', $datos['id']);

        return $stmt->execute();
    }

    public function guardarObservacionCoach($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'INSERT INTO registros_progreso (id_cliente, fecha_registro, observaciones_coach)
                    VALUES (:id_cliente, :fecha, :observacion_coach)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_cliente', $datos['cliente_id'] ?? $datos['id_cliente']);
            $stmt->bindValue(':fecha', $datos['fecha'] ?? date('Y-m-d'));
            $stmt->bindParam(':observacion_coach', $datos['observacion']);

            return $stmt->execute();
        }

        $sql = 'INSERT INTO registro_progreso (id_plan_cliente, fecha, observacion_coach)
                VALUES (:id_plan_cliente, :fecha, :observacion_coach)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']);
        $stmt->bindValue(':fecha', $datos['fecha'] ?? date('Y-m-d'));
        $stmt->bindParam(':observacion_coach', $datos['observacion']);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        if ($this->usaEsquemaNuevo()) {
            return true;
        }

        $sql = 'UPDATE registro_progreso SET estado = :estado WHERE id_registro_progreso = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function eliminar($id)
    {
        $tabla = $this->tabla();
        $sql = "DELETE FROM {$tabla} WHERE id_registro_progreso = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function reporteGeneral()
    {
        $tabla = $this->tabla();

        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT 'registrado' AS estado, COUNT(*) AS total FROM {$tabla}";
        } else {
            $sql = "SELECT estado, COUNT(*) AS total FROM {$tabla} GROUP BY estado";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reportePorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT 'registrado' AS estado, COUNT(*) AS total
                    FROM registros_progreso rp
                    INNER JOIN clientes c ON c.id_cliente = rp.id_cliente
                    WHERE c.id_coach = :coach_id";
        } else {
            $sql = "SELECT rp.estado, COUNT(*) AS total
                    FROM registro_progreso rp
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = rp.id_plan_cliente
                    WHERE pc.id_coach = :coach_id
                    GROUP BY rp.estado";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function guardarFoto($datos)
    {
        if (empty($datos['foto_tmp']) || empty($datos['foto_nombre'])) {
            return null;
        }

        if (!is_dir($this->rutaBase)) {
            mkdir($this->rutaBase, 0777, true);
        }

        $extension = pathinfo($datos['foto_nombre'], PATHINFO_EXTENSION);
        $nombreSeguro = 'progreso_' . time() . '_' . uniqid() . '.' . $extension;
        $rutaCompleta = $this->rutaBase . $nombreSeguro;
        $rutaRelativa = 'public/uploads/progresos/' . $nombreSeguro;

        if (is_uploaded_file($datos['foto_tmp'])) {
            move_uploaded_file($datos['foto_tmp'], $rutaCompleta);

            return $rutaRelativa;
        }

        return null;
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Progreso', $accion);
    }
}

?>
