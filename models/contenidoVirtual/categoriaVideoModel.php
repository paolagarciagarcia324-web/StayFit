<?php

require_once __DIR__ . '/../../config/database.php';

class CategoriaVideoModel
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

        $fila['id'] = $fila['id_categoria_video'] ?? $fila['id'] ?? null;

        return $fila;
    }

    public function obtenerTodas()
    {
        try {
            $sql = 'SELECT * FROM categoria_video ORDER BY nombre ASC';
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

    public function obtenerTodos()
    {
        return $this->obtenerTodas();
    }

    public function obtenerActivas()
    {
        try {
            $sql = 'SELECT * FROM categoria_video WHERE activo = 1 ORDER BY nombre ASC';
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
            $sql = 'SELECT * FROM categoria_video WHERE id_categoria_video = :id LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return null;
        }
    }

    public function crear($datos)
    {
        $sql = 'INSERT INTO categoria_video (nombre, descripcion, activo)
                VALUES (:nombre, :descripcion, :activo)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
        $activo = ($datos['estado'] ?? 'activo') === 'activo' || !empty($datos['activo']) ? 1 : 0;
        $stmt->bindValue(':activo', $activo, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function actualizar($datos)
    {
        $sql = 'UPDATE categoria_video SET nombre = :nombre, descripcion = :descripcion, activo = :activo
                WHERE id_categoria_video = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
        $activo = ($datos['estado'] ?? 'activo') === 'activo' || !empty($datos['activo']) ? 1 : 0;
        $stmt->bindValue(':activo', $activo, PDO::PARAM_INT);
        $stmt->bindValue(':id', $datos['id'] ?? $datos['id_categoria_video'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        $activo = ($estado === 'activo' || $estado === 1) ? 1 : 0;
        $stmt = $this->db->prepare('UPDATE categoria_video SET activo = :activo WHERE id_categoria_video = :id');
        $stmt->bindValue(':activo', $activo, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Categorías de video', $accion);
    }
}

?>
