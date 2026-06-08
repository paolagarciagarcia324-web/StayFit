<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';
require_once __DIR__ . '/../../config/helpers.php';

class RolModel
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
        return $this->schema->tablaExiste('roles');
    }

    private function tabla(): string
    {
        return $this->usaEsquemaNuevo() ? 'roles' : 'rol';
    }

    private function normalizarFila(array $fila): array
    {
        $fila['activo'] = $this->usaEsquemaNuevo()
            ? (strtoupper($fila['estado'] ?? 'ACTIVO') === 'ACTIVO' ? 1 : 0)
            : (int) ($fila['activo'] ?? 1);

        return $fila;
    }

    public function obtenerTodos()
    {
        $tabla = $this->tabla();
        $sql = "SELECT * FROM {$tabla} ORDER BY id_rol ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return array_map([$this, 'normalizarFila'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerActivos()
    {
        $tabla = $this->tabla();

        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT * FROM {$tabla} WHERE estado = 'ACTIVO' ORDER BY nombre ASC";
        } else {
            $sql = "SELECT * FROM {$tabla} WHERE activo = 1 ORDER BY nombre ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return array_map([$this, 'normalizarFila'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerPorId($id)
    {
        $tabla = $this->tabla();
        $sql = "SELECT * FROM {$tabla} WHERE id_rol = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->normalizarFila($fila) : null;
    }

    public function obtenerPorNombre($nombre)
    {
        $tabla = $this->tabla();
        $sql = "SELECT * FROM {$tabla} WHERE nombre = :nombre OR codigo = :codigo LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindValue(':codigo', strtoupper($nombre));
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->normalizarFila($fila) : null;
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Roles', $accion);
    }
}

?>
