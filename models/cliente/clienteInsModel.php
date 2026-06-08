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
                           NULL AS cargo, 0 AS es_contacto_principal, c.fecha_alta AS fecha_vinculacion
                    FROM clientes c
                    INNER JOIN user u ON u.id_user = c.id_user
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

        if (!empty($fila['apellido'])) {
            $fila['nombre_completo'] = trim(($fila['nombre'] ?? '') . ' ' . ($fila['apellido'] ?? ''));
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
