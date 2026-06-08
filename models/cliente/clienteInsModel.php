<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';
require_once __DIR__ . '/../../config/helpers.php';

class ClienteInsModel
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
            return "SELECT c.id_cliente, u.id_user AS id_usuario, u.nombres AS nombre, u.apellidos AS apellido,
                           u.correo, u.estado, 'INSTITUCIONAL' AS tipo_cliente,
                           c.objetivo_principal AS objetivos, c.id_institucion,
                           i.nombre AS institucion,
                           NULL AS cargo, 0 AS es_contacto_principal, c.fecha_alta AS fecha_vinculacion
                    FROM clientes c
                    INNER JOIN user u ON u.id_user = c.id_user
                    LEFT JOIN instituciones i ON i.id_institucion = c.id_institucion
                    WHERE c.id_institucion IS NOT NULL";
        }

        return "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado,
                       c.tipo_cliente, c.objetivos,
                       ci.id_institucion, ci.cargo, ci.es_contacto_principal, ci.fecha_vinculacion
                FROM cliente_institucional ci
                INNER JOIN cliente c ON c.id_cliente = ci.id_cliente
                INNER JOIN users u ON u.id_usuario = c.id_cliente";
    }

    private function normalizarFila(?array $fila): ?array
    {
        if (!$fila) {
            return null;
        }

        $fila['id'] = $fila['id_cliente'] ?? $fila['id_usuario'] ?? null;

        $nombre = trim((string) ($fila['nombre'] ?? ''));
        $apellido = trim((string) ($fila['apellido'] ?? ''));
        $fila['nombre_completo'] = trim($nombre . ' ' . $apellido);
        $fila['cliente'] = $fila['nombre_completo'] !== '' ? $fila['nombre_completo'] : ($fila['correo'] ?? 'Clienta');

        if (empty($fila['cargo']) && !empty($fila['objetivos']) && str_starts_with((string) $fila['objetivos'], 'Cargo:')) {
            $fila['cargo'] = trim(substr((string) $fila['objetivos'], strlen('Cargo:')));
        }

        if (empty($fila['institucion']) && !empty($fila['id_institucion'])) {
            $fila['institucion'] = 'Institución #' . (int) $fila['id_institucion'];
        }

        return $fila;
    }

    public function obtenerPorUsuario($usuarioId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = str_replace(
                'WHERE c.id_institucion IS NOT NULL',
                'WHERE c.id_user = :usuario_id AND c.id_institucion IS NOT NULL',
                $this->sqlBaseSelect()
            ) . ' LIMIT 1';
        } else {
            $sql = $this->sqlBaseSelect() . ' WHERE ci.id_cliente = :usuario_id LIMIT 1';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function obtenerPorId($id)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = str_replace(
                'WHERE c.id_institucion IS NOT NULL',
                'WHERE c.id_cliente = :id AND c.id_institucion IS NOT NULL',
                $this->sqlBaseSelect()
            ) . ' LIMIT 1';
        } else {
            $sql = $this->sqlBaseSelect() . ' WHERE ci.id_cliente = :id LIMIT 1';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function actualizarPerfil($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE clientes SET objetivo_principal = :objetivos WHERE id_cliente = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':objetivos', $datos['objetivos'] ?? null);
            $stmt->bindParam(':id', $datos['id_cliente']);
        } else {
            $sql = 'UPDATE cliente SET objetivos = :objetivos, restricciones_medicas = :restricciones_medicas WHERE id_cliente = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':objetivos', $datos['objetivos'] ?? null);
            $stmt->bindValue(':restricciones_medicas', $datos['restricciones_medicas'] ?? null);
            $stmt->bindParam(':id', $datos['id_cliente']);
        }

        return $stmt->execute();
    }

    public function obtenerTodos()
    {
        $sql = $this->sqlBaseSelect() . ($this->usaEsquemaNuevo()
            ? ' ORDER BY c.id_cliente DESC'
            : ' ORDER BY u.id_usuario DESC');

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPorInstitucion($idInstitucion)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT c.id_cliente, u.id_user AS id_usuario, u.nombres AS nombre, u.apellidos AS apellido,
                           u.correo, u.estado, 'INSTITUCIONAL' AS tipo_cliente,
                           c.objetivo_principal AS objetivos, c.id_institucion,
                           i.nombre AS institucion, c.fecha_alta AS fecha_vinculacion
                    FROM clientes c
                    INNER JOIN user u ON u.id_user = c.id_user
                    INNER JOIN instituciones i ON i.id_institucion = c.id_institucion
                    WHERE c.id_institucion = :id_institucion
                    ORDER BY c.fecha_alta DESC";
        } else {
            $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado,
                           c.tipo_cliente, c.objetivos,
                           ci.id_institucion, ci.cargo, ci.fecha_vinculacion,
                           ins.nombre AS institucion
                    FROM cliente_institucional ci
                    INNER JOIN cliente c ON c.id_cliente = ci.id_cliente
                    INNER JOIN users u ON u.id_usuario = c.id_cliente
                    INNER JOIN institucion ins ON ins.id_institucion = ci.id_institucion
                    WHERE ci.id_institucion = :id_institucion
                    ORDER BY ci.fecha_vinculacion DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_institucion', $idInstitucion);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $normalizada = $this->normalizarFila($fila);
            if ($normalizada) {
                $normalizada['cliente'] = trim(($normalizada['nombre'] ?? '') . ' ' . ($normalizada['apellido'] ?? ''));
                $lista[] = $normalizada;
            }
        }

        return $lista;
    }

    public function obtenerConvenio($clienteId)
    {
        $clienteId = (int) $clienteId;
        if ($clienteId < 1) {
            return null;
        }

        if ($this->usaEsquemaNuevo()) {
            $joinEnlace = $this->schema->tablaExiste('enlaces_registro_institucional')
                ? 'LEFT JOIN enlaces_registro_institucional e ON e.id_institucion = c.id_institucion'
                : '';

            $sql = "SELECT pc.fecha_inicio, pc.fecha_fin, pc.estado_plan_cliente,
                           p.nombre AS plan_nombre, p.descripcion AS plan_descripcion,
                           p.modalidad, p.tipo_cliente,
                           i.nombre AS institucion_nombre, i.estado AS institucion_estado
                    FROM clientes c
                    LEFT JOIN instituciones i ON i.id_institucion = c.id_institucion
                    LEFT JOIN planes_cliente pc
                           ON pc.id_cliente = c.id_cliente
                          AND pc.estado_plan_cliente = 'ACTIVO'
                    LEFT JOIN planes p ON p.id_plan = pc.id_plan
                    {$joinEnlace}
                    WHERE c.id_cliente = :cliente_id
                    ORDER BY pc.id_plan_cliente DESC
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila || empty($fila['plan_nombre'])) {
                return null;
            }

            $beneficios = [];
            if (!empty($fila['plan_descripcion'])) {
                $beneficios[] = $fila['plan_descripcion'];
            }
            if (!empty($fila['modalidad'])) {
                $beneficios[] = 'Modalidad: ' . ucfirst(strtolower((string) $fila['modalidad']));
            }
            if (!empty($fila['institucion_nombre'])) {
                $beneficios[] = 'Institución: ' . $fila['institucion_nombre'];
            }

            $estadoPlan = strtoupper((string) ($fila['estado_plan_cliente'] ?? 'ACTIVO'));

            return [
                'tipo' => $fila['plan_nombre'],
                'fecha_inicio' => $fila['fecha_inicio'] ?? null,
                'fecha_fin' => $fila['fecha_fin'] ?? null,
                'beneficios' => implode(' · ', $beneficios),
                'estado' => strtolower($estadoPlan === 'ACTIVO' ? 'activo' : $estadoPlan),
                'plan_nombre' => $fila['plan_nombre'],
                'institucion' => $fila['institucion_nombre'] ?? null,
            ];
        }

        $sql = "SELECT pc.fecha_inicio, pc.fecha_fin, pc.estado AS estado_plan,
                       pl.nombre AS plan_nombre, pl.descripcion AS plan_descripcion,
                       ins.nombre AS institucion_nombre
                FROM cliente_institucional ci
                INNER JOIN plan_cliente pc ON pc.id_cliente = ci.id_cliente AND pc.estado = 'ACTIVO'
                INNER JOIN plan pl ON pl.id_plan = pc.id_plan
                INNER JOIN institucion ins ON ins.id_institucion = ci.id_institucion
                WHERE ci.id_cliente = :cliente_id
                ORDER BY pc.id_plan_cliente DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return [
            'tipo' => $fila['plan_nombre'] ?? 'Convenio institucional',
            'fecha_inicio' => $fila['fecha_inicio'] ?? null,
            'fecha_fin' => $fila['fecha_fin'] ?? null,
            'beneficios' => $fila['plan_descripcion'] ?? 'Plan institucional activo',
            'estado' => strtolower((string) ($fila['estado_plan'] ?? 'activo')),
            'plan_nombre' => $fila['plan_nombre'] ?? null,
            'institucion' => $fila['institucion_nombre'] ?? null,
        ];
    }

    public function cambiarEstado($id, $estado)
    {
        $estado = strtoupper($estado);

        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE user SET estado = :estado WHERE id_user = :id';
        } else {
            $sql = 'UPDATE users SET estado = :estado WHERE id_usuario = :id';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function vincularInstitucion($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE clientes
                    SET id_institucion = :id_institucion
                    WHERE id_cliente = :id_cliente';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_cliente', $datos['cliente_id']);
            $stmt->bindParam(':id_institucion', $datos['institucion_id']);

            return $stmt->execute();
        }

        $sql = "INSERT INTO cliente_institucional (id_cliente, id_institucion, cargo, es_contacto_principal)
                VALUES (:id_cliente, :id_institucion, :cargo, 0)
                ON DUPLICATE KEY UPDATE cargo = :cargo_up, id_institucion = :id_institucion_up";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_cliente', $datos['cliente_id']);
        $stmt->bindParam(':id_institucion', $datos['institucion_id']);
        $stmt->bindValue(':cargo', $datos['cargo'] ?? '');
        $stmt->bindValue(':cargo_up', $datos['cargo'] ?? '');
        $stmt->bindParam(':id_institucion_up', $datos['institucion_id']);

        return $stmt->execute();
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Cliente Institucional', $accion);
    }
}

?>
