<?php

require_once __DIR__ . '/../../config/database.php';

class CategoriaVideoModel
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
            $usaNuevo = (bool) $this->db->query("SHOW TABLES LIKE 'categorias_video'")->fetch(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            $usaNuevo = false;
        }

        return $usaNuevo;
    }

    private function tablaCategoria(): string
    {
        return $this->usaEsquemaNuevo() ? 'categorias_video' : 'categoria_video';
    }

    private function columnaId(): string
    {
        return $this->usaEsquemaNuevo() ? 'id_categoria' : 'id_categoria_video';
    }

    private function esActivo($datos): bool
    {
        if (isset($datos['estado'])) {
            return $datos['estado'] === 'activo';
        }

        return !empty($datos['activo']);
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_categoria_video'] ?? $fila['id_categoria'] ?? $fila['id'] ?? null;
        $fila['id_categoria_video'] = $fila['id'];

        if (isset($fila['estado_categoria'])) {
            $fila['activo'] = strtoupper((string) $fila['estado_categoria']) === 'ACTIVA';
        } else {
            $fila['activo'] = (bool) ($fila['activo'] ?? true);
        }

        return $fila;
    }

    public function obtenerTodas()
    {
        try {
            $tabla = $this->tablaCategoria();
            $colId = $this->columnaId();
            $sql = "SELECT * FROM {$tabla} ORDER BY nombre ASC, {$colId} ASC";
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
            $tabla = $this->tablaCategoria();
            $colId = $this->columnaId();

            if ($this->usaEsquemaNuevo()) {
                $sql = "SELECT * FROM {$tabla} WHERE estado_categoria = 'ACTIVA' ORDER BY nombre ASC, {$colId} ASC";
            } else {
                $sql = "SELECT * FROM {$tabla} WHERE activo = 1 ORDER BY nombre ASC, {$colId} ASC";
            }

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
            $tabla = $this->tablaCategoria();
            $colId = $this->columnaId();
            $sql = "SELECT * FROM {$tabla} WHERE {$colId} = :id LIMIT 1";
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
        if ($this->usaEsquemaNuevo()) {
            $sql = 'INSERT INTO categorias_video (nombre, descripcion, orden, estado_categoria)
                    VALUES (:nombre, :descripcion, :orden, :estado_categoria)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
            $stmt->bindValue(':orden', (int) ($datos['orden'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(
                ':estado_categoria',
                $this->esActivo($datos) ? 'ACTIVA' : 'INACTIVA'
            );

            return $stmt->execute();
        }

        $sql = 'INSERT INTO categoria_video (nombre, descripcion, activo)
                VALUES (:nombre, :descripcion, :activo)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
        $stmt->bindValue(':activo', $this->esActivo($datos) ? 1 : 0, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function actualizar($datos)
    {
        $id = $datos['id'] ?? $datos['id_categoria_video'] ?? $datos['id_categoria'] ?? null;

        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE categorias_video
                    SET nombre = :nombre, descripcion = :descripcion, orden = :orden, estado_categoria = :estado_categoria
                    WHERE id_categoria = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
            $stmt->bindValue(':orden', (int) ($datos['orden'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(
                ':estado_categoria',
                $this->esActivo($datos) ? 'ACTIVA' : 'INACTIVA'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        $sql = 'UPDATE categoria_video SET nombre = :nombre, descripcion = :descripcion, activo = :activo
                WHERE id_categoria_video = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
        $stmt->bindValue(':activo', $this->esActivo($datos) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        if ($this->usaEsquemaNuevo()) {
            $valor = ($estado === 'activo' || $estado === 1 || $estado === true) ? 'ACTIVA' : 'INACTIVA';
            $stmt = $this->db->prepare(
                'UPDATE categorias_video SET estado_categoria = :estado WHERE id_categoria = :id'
            );
            $stmt->bindValue(':estado', $valor);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        $activo = ($estado === 'activo' || $estado === 1 || $estado === true) ? 1 : 0;
        $stmt = $this->db->prepare(
            'UPDATE categoria_video SET activo = :activo WHERE id_categoria_video = :id'
        );
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
