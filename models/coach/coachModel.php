<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class CoachModel
{
    private $db;
    private SchemaHelper $schema;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
        $this->schema = new SchemaHelper($this->db);
    }

    private function usaEsquemaNuevo(): bool
    {
        return $this->schema->usaEsquemaNuevo();
    }

    private function sqlBaseSelect(): string
    {
        if ($this->usaEsquemaNuevo()) {
            return "SELECT c.id_coach, u.id_user AS id_usuario, u.nombres AS nombre, u.apellidos AS apellido,
                           u.correo, u.estado, u.telefono,
                           c.especialidad, c.certificaciones AS credencial, c.biografia
                    FROM coaches c
                    INNER JOIN user u ON u.id_user = c.id_user";
        }

        return "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado, u.telefono,
                       c.especialidad, c.credencial, c.biografia
                FROM coach c
                INNER JOIN users u ON u.id_usuario = c.id_coach";
    }

    private function normalizarLista(array $lista): array
    {
        foreach ($lista as &$fila) {
            $fila['id'] = $fila['id_coach'] ?? $fila['id_usuario'] ?? $fila['id'] ?? null;
            $fila['estado'] = strtolower($fila['estado'] ?? $fila['estado_coach'] ?? 'activo');
            if (!empty($fila['apellido'])) {
                $fila['nombre_completo'] = trim(($fila['nombre'] ?? '') . ' ' . ($fila['apellido'] ?? ''));
            }
        }

        return $lista;
    }

    public function obtenerTodos()
    {
        $sql = $this->sqlBaseSelect() . ' ORDER BY ' . ($this->usaEsquemaNuevo() ? 'u.id_user' : 'u.id_usuario') . ' DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerActivos()
    {
        $sql = $this->sqlBaseSelect() . ' WHERE u.estado = \'ACTIVO\' ORDER BY ' . ($this->usaEsquemaNuevo() ? 'u.nombres' : 'u.nombre') . ' ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerPorId($id)
    {
        $campo = $this->usaEsquemaNuevo() ? 'c.id_coach' : 'c.id_coach';
        $sql = $this->sqlBaseSelect() . " WHERE {$campo} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->normalizarLista([$fila])[0] : null;
    }

    public function obtenerPorUsuario($usuarioId)
    {
        $campo = $this->usaEsquemaNuevo() ? 'c.id_user' : 'c.id_coach';
        $sql = $this->sqlBaseSelect() . " WHERE {$campo} = :usuario_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->normalizarLista([$fila])[0] : null;
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "INSERT INTO coaches (id_user, especialidad, certificaciones, biografia, estado_coach, creado_en)
                    VALUES (:id_user, :especialidad, :credencial, :biografia, 'ACTIVO', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_user', $datos['id_coach']);
        } else {
            $sql = "INSERT INTO coach (id_coach, especialidad, credencial, biografia)
                    VALUES (:id_coach, :especialidad, :credencial, :biografia)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_coach', $datos['id_coach']);
        }

        $stmt->bindValue(':especialidad', $datos['especialidad'] ?? null);
        $stmt->bindValue(':credencial', $datos['credencial'] ?? null);
        $stmt->bindValue(':biografia', $datos['biografia'] ?? null);

        return $stmt->execute();
    }

    public function actualizar($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "UPDATE coaches
                    SET especialidad = :especialidad, certificaciones = :credencial, biografia = :biografia
                    WHERE id_coach = :id";
        } else {
            $sql = "UPDATE coach
                    SET especialidad = :especialidad, credencial = :credencial, biografia = :biografia
                    WHERE id_coach = :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':especialidad', $datos['especialidad'] ?? null);
        $stmt->bindValue(':credencial', $datos['credencial'] ?? null);
        $stmt->bindValue(':biografia', $datos['biografia'] ?? null);
        $stmt->bindParam(':id', $datos['id_coach']);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        $mapa = ['activo' => 'ACTIVO', 'inactivo' => 'INACTIVO', 'suspendido' => 'SUSPENDIDO'];
        $estadoBd = $mapa[strtolower($estado)] ?? strtoupper($estado);

        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE user SET estado = :estado WHERE id_user = :id';
        } else {
            $sql = 'UPDATE users SET estado = :estado WHERE id_usuario = :id';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estadoBd);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function contarActivos()
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT COUNT(*) AS total FROM coaches c INNER JOIN user u ON u.id_user = c.id_user WHERE u.estado = 'ACTIVO'";
        } else {
            $sql = "SELECT COUNT(*) AS total FROM coach c INNER JOIN users u ON u.id_usuario = c.id_coach WHERE u.estado = 'ACTIVO'";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerClientesAsignados($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT c.id_cliente AS id, u.nombres AS nombre, u.correo,
                           'INDIVIDUAL' AS tipo_cliente, pc.estado_plan_cliente AS estado_plan
                    FROM clientes c
                    INNER JOIN user u ON u.id_user = c.id_user
                    LEFT JOIN planes_cliente pc ON pc.id_cliente = c.id_cliente AND pc.estado_plan_cliente = 'ACTIVO'
                    WHERE c.id_coach = :coach_id
                    ORDER BY u.nombres ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':coach_id', $coachId);
        } else {
            $sql = "SELECT u.id_usuario AS id, u.nombre, u.correo, c.tipo_cliente, pc.estado AS estado_plan
                    FROM plan_cliente pc
                    INNER JOIN cliente c ON c.id_cliente = pc.id_cliente
                    INNER JOIN users u ON u.id_usuario = c.id_cliente
                    WHERE pc.id_coach = :coach_id
                    ORDER BY u.nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':coach_id', $coachId);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Coaches', $accion);
    }
}

?>
