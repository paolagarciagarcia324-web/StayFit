<?php

require_once __DIR__ . '/../../config/database.php';

class VideoModel
{
    private $db;
    private $columnasExtra;

    public function __construct()
    {
        $this->db = (new Database())->conectar();
        $this->columnasExtra = $this->detectarColumnasExtra();
    }

    private function detectarColumnasExtra(): bool
    {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM video LIKE 'tipo_media'");

            return (bool) $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_video'] ?? $fila['id'] ?? null;
        $fila['programa_virtual_id'] = $fila['id_programa_virtual'] ?? null;
        $fila['categoria_id'] = $fila['id_categoria_video'] ?? null;

        if (empty($fila['tipo_media'])) {
            $url = $fila['url_video'] ?? '';
            if (preg_match('/^https?:\/\//i', $url)) {
                $fila['tipo_media'] = 'ENLACE';
            } elseif (preg_match('/\.(jpg|jpeg|png|gif|webp|bmp)$/i', $url)) {
                $fila['tipo_media'] = 'IMAGEN';
            } else {
                $fila['tipo_media'] = 'VIDEO';
            }
        }

        $fila['estado_progreso'] = strtolower($fila['estado_progreso'] ?? 'pendiente');

        return $fila;
    }

    private function sqlSelectBase(): string
    {
        $extra = $this->columnasExtra
            ? ', v.tipo_media, v.id_subido_por, v.fecha_subida'
            : '';

        return "SELECT v.*, c.nombre AS categoria_nombre, pv.nombre AS programa_nombre{$extra}";
    }

    public function obtenerTodos()
    {
        try {
            $sql = $this->sqlSelectBase() . "
                    FROM video v
                    LEFT JOIN categoria_video c ON c.id_categoria_video = v.id_categoria_video
                    LEFT JOIN programa_virtual pv ON pv.id_programa_virtual = v.id_programa_virtual
                    ORDER BY v.orden ASC, v.id_video DESC";
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
            $sql = $this->sqlSelectBase() . "
                    FROM video v
                    LEFT JOIN categoria_video c ON c.id_categoria_video = v.id_categoria_video
                    LEFT JOIN programa_virtual pv ON pv.id_programa_virtual = v.id_programa_virtual
                    WHERE v.id_video = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return null;
        }
    }

    public function obtenerPorPrograma($programaId, bool $soloActivos = false)
    {
        try {
            $filtro = $soloActivos ? ' AND v.activo = 1' : '';
            $sql = $this->sqlSelectBase() . "
                    FROM video v
                    LEFT JOIN categoria_video c ON c.id_categoria_video = v.id_categoria_video
                    LEFT JOIN programa_virtual pv ON pv.id_programa_virtual = v.id_programa_virtual
                    WHERE v.id_programa_virtual = :programa_id{$filtro}
                    ORDER BY v.orden ASC, v.id_video ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':programa_id', $programaId, PDO::PARAM_INT);
            $stmt->execute();
            $lista = [];

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $lista[] = $this->normalizarFila($fila);
            }

            return $lista;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorCliente($clienteId)
    {
        try {
            $extra = $this->columnasExtra
                ? ', v.tipo_media, v.id_subido_por, v.fecha_subida'
                : '';

            $sql = "SELECT v.*, c.nombre AS categoria_nombre, pv.nombre AS programa_nombre, pv.descripcion AS programa_descripcion,
                           pl.nombre AS plan_nombre, pl.id_plan,
                           COALESCE(pvg.estado, 'PENDIENTE') AS estado_progreso,
                           COALESCE(pvg.porcentaje_avance, 0) AS porcentaje_avance{$extra}
                    FROM video v
                    INNER JOIN programa_virtual pv ON pv.id_programa_virtual = v.id_programa_virtual
                    INNER JOIN plan pl ON pl.id_plan = pv.id_plan
                    INNER JOIN plan_cliente pc ON pc.id_plan = pl.id_plan
                    LEFT JOIN categoria_video c ON c.id_categoria_video = v.id_categoria_video
                    LEFT JOIN progreso_video pvg ON pvg.id_video = v.id_video AND pvg.id_cliente = :cliente_id2
                    WHERE pc.id_cliente = :cliente_id AND pc.estado = 'ACTIVO' AND v.activo = 1 AND pv.activo = 1
                    ORDER BY v.orden ASC, v.id_video ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->bindValue(':cliente_id2', $clienteId, PDO::PARAM_INT);
            $stmt->execute();
            $lista = [];

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $lista[] = $this->normalizarFila($fila);
            }

            return $lista;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function crear($datos)
    {
        if ($this->columnasExtra) {
            $sql = "INSERT INTO video
                    (id_programa_virtual, id_categoria_video, titulo, descripcion, url_video, tipo_media,
                     id_subido_por, duracion_minutos, orden, activo)
                    VALUES
                    (:id_programa_virtual, :id_categoria_video, :titulo, :descripcion, :url_video, :tipo_media,
                     :id_subido_por, :duracion_minutos, :orden, :activo)";
        } else {
            $sql = "INSERT INTO video
                    (id_programa_virtual, id_categoria_video, titulo, descripcion, url_video, duracion_minutos, orden, activo)
                    VALUES
                    (:id_programa_virtual, :id_categoria_video, :titulo, :descripcion, :url_video, :duracion_minutos, :orden, :activo)";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_programa_virtual', $datos['id_programa_virtual'] ?? $datos['programa_virtual_id'], PDO::PARAM_INT);
        $cat = $datos['id_categoria_video'] ?? $datos['categoria_id'] ?? null;
        $stmt->bindValue(':id_categoria_video', $cat ?: null, $cat ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindParam(':titulo', $datos['titulo']);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
        $stmt->bindParam(':url_video', $datos['url_video']);

        if ($this->columnasExtra) {
            $stmt->bindValue(':tipo_media', $datos['tipo_media'] ?? 'ENLACE');
            $subido = $datos['id_subido_por'] ?? null;
            $stmt->bindValue(':id_subido_por', $subido, $subido ? PDO::PARAM_INT : PDO::PARAM_NULL);
        }

        $stmt->bindValue(':duracion_minutos', $datos['duracion_minutos'] ?? $datos['duracion'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':orden', (int) ($datos['orden'] ?? 1), PDO::PARAM_INT);
        $stmt->bindValue(':activo', !empty($datos['activo']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function actualizar($datos)
    {
        if ($this->columnasExtra) {
            $sql = "UPDATE video SET
                    id_programa_virtual = :id_programa_virtual, id_categoria_video = :id_categoria_video,
                    titulo = :titulo, descripcion = :descripcion, url_video = :url_video, tipo_media = :tipo_media,
                    duracion_minutos = :duracion_minutos, orden = :orden, activo = :activo
                    WHERE id_video = :id";
        } else {
            $sql = "UPDATE video SET
                    id_programa_virtual = :id_programa_virtual, id_categoria_video = :id_categoria_video,
                    titulo = :titulo, descripcion = :descripcion, url_video = :url_video,
                    duracion_minutos = :duracion_minutos, orden = :orden, activo = :activo
                    WHERE id_video = :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_programa_virtual', $datos['id_programa_virtual'] ?? $datos['programa_virtual_id'], PDO::PARAM_INT);
        $cat = $datos['id_categoria_video'] ?? $datos['categoria_id'] ?? null;
        $stmt->bindValue(':id_categoria_video', $cat ?: null, $cat ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindParam(':titulo', $datos['titulo']);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
        $stmt->bindParam(':url_video', $datos['url_video']);

        if ($this->columnasExtra) {
            $stmt->bindValue(':tipo_media', $datos['tipo_media'] ?? 'ENLACE');
        }

        $stmt->bindValue(':duracion_minutos', $datos['duracion_minutos'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':orden', (int) ($datos['orden'] ?? 1), PDO::PARAM_INT);
        $stmt->bindValue(':activo', !empty($datos['activo']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $datos['id'] ?? $datos['id_video'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function eliminar($id)
    {
        $stmt = $this->db->prepare('DELETE FROM progreso_video WHERE id_video = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $this->db->prepare('DELETE FROM video WHERE id_video = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        $activo = ($estado === 'activo' || $estado === 1) ? 1 : 0;
        $stmt = $this->db->prepare('UPDATE video SET activo = :activo WHERE id_video = :id');
        $stmt->bindValue(':activo', $activo, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Videos virtuales', $accion);
    }
}

?>
