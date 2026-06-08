<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class NotificacionModel
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
        return $this->schema->tablaExiste('notificaciones');
    }

    private function normalizarFila(array $fila): array
    {
        $fila['id_usuario'] = $fila['id_usuario'] ?? $fila['id_user'] ?? null;
        $fila['contenido'] = $fila['contenido'] ?? $fila['mensaje'] ?? '';
        $fila['tipo'] = $fila['tipo'] ?? $fila['tipo_notificacion'] ?? 'SISTEMA';
        $fila['fecha_envio'] = $fila['fecha_envio'] ?? $fila['creado_en'] ?? null;

        return $fila;
    }

    private function normalizarLista(array $filas): array
    {
        return array_map([$this, 'normalizarFila'], $filas);
    }

    public function obtenerPorUsuario($usuarioId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'SELECT *, mensaje AS contenido, creado_en AS fecha_envio
                    FROM notificaciones
                    WHERE id_user = :usuario_id
                    ORDER BY creado_en DESC';
        } else {
            $sql = 'SELECT * FROM notificacion
                    WHERE id_usuario = :usuario_id
                    ORDER BY fecha_envio DESC';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerNoLeidas($usuarioId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'SELECT *, mensaje AS contenido, creado_en AS fecha_envio
                    FROM notificaciones
                    WHERE id_user = :usuario_id AND leida = 0
                    ORDER BY creado_en DESC';
        } else {
            $sql = 'SELECT * FROM notificacion
                    WHERE id_usuario = :usuario_id AND leida = 0
                    ORDER BY fecha_envio DESC';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'INSERT INTO notificaciones (id_user, titulo, mensaje, tipo_notificacion, leida, creado_en)
                    VALUES (:id_usuario, :titulo, :contenido, :tipo, 0, NOW())';
        } else {
            $sql = 'INSERT INTO notificacion (id_usuario, titulo, contenido, tipo, leida, fecha_envio)
                    VALUES (:id_usuario, :titulo, :contenido, :tipo, 0, NOW())';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $datos['id_usuario']);
        $stmt->bindParam(':titulo', $datos['titulo']);
        $stmt->bindParam(':contenido', $datos['contenido']);
        $stmt->bindValue(':tipo', $datos['tipo'] ?? 'SISTEMA');

        return $stmt->execute();
    }

    public function marcarLeida($id)
    {
        $tabla = $this->usaEsquemaNuevo() ? 'notificaciones' : 'notificacion';
        $sql = "UPDATE {$tabla} SET leida = 1, fecha_lectura = NOW() WHERE id_notificacion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function eliminar($id)
    {
        $tabla = $this->usaEsquemaNuevo() ? 'notificaciones' : 'notificacion';
        $sql = "DELETE FROM {$tabla} WHERE id_notificacion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function obtenerPorRol($rol)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;

        if ($usuarioId && (strtolower($rol) === 'admin' || strtolower($rol) === 'administrador')) {
            return $this->obtenerPorUsuario($usuarioId);
        }

        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT n.*, n.mensaje AS contenido, n.creado_en AS fecha_envio
                    FROM notificaciones n
                    INNER JOIN user_roles ur ON ur.id_user = n.id_user
                    INNER JOIN roles r ON r.id_rol = ur.id_rol
                    WHERE LOWER(r.nombre) IN ('administrador', 'admin')
                    ORDER BY n.creado_en DESC";
        } else {
            $sql = "SELECT n.*
                    FROM notificacion n
                    INNER JOIN users_roles ur ON ur.id_usuario = n.id_usuario
                    INNER JOIN rol r ON r.id_rol = ur.id_rol
                    WHERE r.nombre = 'Administrador'
                    ORDER BY n.fecha_envio DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function notificarAdministrador($titulo, $contenido)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT u.id_user AS id_usuario
                    FROM user u
                    INNER JOIN user_roles ur ON ur.id_user = u.id_user
                    INNER JOIN roles r ON r.id_rol = ur.id_rol
                    WHERE LOWER(r.nombre) IN ('administrador', 'admin') AND u.estado = 'ACTIVO'";
        } else {
            $sql = "SELECT u.id_usuario
                    FROM users u
                    INNER JOIN users_roles ur ON ur.id_usuario = u.id_usuario
                    INNER JOIN rol r ON r.id_rol = ur.id_rol
                    WHERE LOWER(r.nombre) IN ('administrador', 'admin') AND u.estado = 'ACTIVO'";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($admins as $admin) {
            if (empty($admin['id_usuario'])) {
                continue;
            }

            $this->crear([
                'id_usuario' => $admin['id_usuario'],
                'titulo'     => $titulo,
                'contenido'  => $contenido,
                'tipo'       => 'SISTEMA',
            ]);
        }

        return true;
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Notificaciones', $accion);
    }
}

?>
