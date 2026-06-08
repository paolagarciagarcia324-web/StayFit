<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class InstitutionModel
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
        return $this->schema->tablaExiste('instituciones');
    }

    private function tabla(): string
    {
        return $this->usaEsquemaNuevo() ? 'instituciones' : 'institucion';
    }

    private function esEstadoActivo($estado): bool
    {
        $valor = strtolower(trim((string) $estado));

        return in_array($valor, ['activo', 'activa', '1', 'true'], true);
    }

    private function estadoParaDb($estado): string
    {
        if ($this->usaEsquemaNuevo()) {
            return $this->esEstadoActivo($estado) ? 'ACTIVA' : 'INACTIVA';
        }

        return $this->esEstadoActivo($estado) ? '1' : '0';
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_institucion'] ?? $fila['id'] ?? null;
        $fila['correo'] = $fila['correo_contacto'] ?? $fila['correo'] ?? '';
        $fila['telefono'] = $fila['telefono_contacto'] ?? $fila['telefono'] ?? '';

        if ($this->usaEsquemaNuevo()) {
            $fila['estado'] = $this->esEstadoActivo($fila['estado'] ?? 'ACTIVA') ? 'activo' : 'inactivo';
        } else {
            $fila['estado'] = ($fila['activo'] ?? 1) ? 'activo' : 'inactivo';
        }

        return $fila;
    }

    public function obtenerTodos()
    {
        $tabla = $this->tabla();
        $sql = "SELECT * FROM {$tabla} ORDER BY id_institucion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerActivas()
    {
        $tabla = $this->tabla();

        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT * FROM {$tabla} WHERE estado = 'ACTIVA' ORDER BY nombre ASC";
        } else {
            $sql = "SELECT * FROM {$tabla} WHERE activo = 1 ORDER BY nombre ASC";
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
        $sql = "SELECT * FROM {$tabla} WHERE id_institucion = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "INSERT INTO instituciones
                    (nombre, nit, telefono_contacto, correo_contacto, direccion, estado, creado_en)
                    VALUES
                    (:nombre, :nit, :telefono, :correo, :direccion, :estado, NOW())";
            $estado = $this->estadoParaDb($datos['estado'] ?? 'activo');
        } else {
            $sql = "INSERT INTO institucion (nombre, nit, telefono, correo_contacto, direccion, activo)
                    VALUES (:nombre, :nit, :telefono, :correo, :direccion, :activo)";
            $estado = (int) $this->estadoParaDb($datos['estado'] ?? 'activo');
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':nit', $datos['nit'] ?? null);
        $stmt->bindValue(':telefono', $datos['telefono'] ?? null);
        $stmt->bindValue(':correo', $datos['correo'] ?? $datos['correo_contacto'] ?? null);
        $stmt->bindValue(':direccion', $datos['direccion'] ?? null);

        if ($this->usaEsquemaNuevo()) {
            $stmt->bindParam(':estado', $estado);
        } else {
            $stmt->bindParam(':activo', $estado, PDO::PARAM_INT);
        }

        return $stmt->execute();
    }

    public function actualizar($datos)
    {
        $id = $datos['id'] ?? $datos['id_institucion'];

        if ($this->usaEsquemaNuevo()) {
            $sql = "UPDATE instituciones
                    SET nombre = :nombre, nit = :nit, telefono_contacto = :telefono,
                        correo_contacto = :correo, direccion = :direccion, estado = :estado
                    WHERE id_institucion = :id";
            $estado = $this->estadoParaDb($datos['estado'] ?? 'activo');
        } else {
            $sql = "UPDATE institucion
                    SET nombre = :nombre, nit = :nit, telefono = :telefono,
                        correo_contacto = :correo, direccion = :direccion, activo = :estado
                    WHERE id_institucion = :id";
            $estado = (int) $this->estadoParaDb($datos['estado'] ?? 'activo');
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':nit', $datos['nit'] ?? null);
        $stmt->bindValue(':telefono', $datos['telefono'] ?? null);
        $stmt->bindValue(':correo', $datos['correo'] ?? null);
        $stmt->bindValue(':direccion', $datos['direccion'] ?? null);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        if ($this->usaEsquemaNuevo()) {
            $valor = $this->estadoParaDb($estado);
            $sql = 'UPDATE instituciones SET estado = :estado WHERE id_institucion = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado', $valor);
        } else {
            $valor = ($estado === 'activo') ? 1 : 0;
            $sql = 'UPDATE institucion SET activo = :activo WHERE id_institucion = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':activo', $valor, PDO::PARAM_INT);
        }

        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Instituciones', $accion);
    }
}

class_alias('InstitutionModel', 'InstitucionModel');

?>
