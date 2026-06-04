<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

class UsuarioModel
{
    private $db; // Conexión BD

    public function __construct(?PDO $db = null)
    {
        if ($db instanceof PDO) {
            $this->db = $db;
            return;
        }

        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    private function usaEsquemaNuevo()
    {
        static $usaNuevo = null;

        if ($usaNuevo !== null) {
            return $usaNuevo;
        }

        $tablaVieja = $this->db->query("SHOW TABLES LIKE 'users'")->fetch();
        $tablaNueva = $this->db->query("SHOW TABLES LIKE 'user'")->fetch();

        $usaNuevo = !$tablaVieja && (bool) $tablaNueva;

        return $usaNuevo;
    }

    private function normalizarUsuario($usuario)
    {
        if (!$usuario) { // Sin registro
            return false; // Retorna falso
        }

        $usuario['id'] = $usuario['id_usuario'] ?? $usuario['id'] ?? null; // ID unificado
        $usuario['id_usuario'] = $usuario['id_usuario'] ?? $usuario['id_user'] ?? $usuario['id'] ?? null;
        $usuario['nombre'] = $usuario['nombre'] ?? $usuario['nombres'] ?? '';
        $usuario['apellido'] = $usuario['apellido'] ?? $usuario['apellidos'] ?? '';
        $usuario['password'] = $usuario['hash_contrasena'] ?? $usuario['password'] ?? $usuario['password_hash'] ?? ''; // Hash unificado

        if (isset($usuario['estado'])) { // Normaliza estado
            $usuario['estado'] = strtolower($usuario['estado']); // Minúsculas para comparar
        }

        return $usuario; // Retorna usuario normalizado
    }

    public function obtenerTodos()
    {
        $sql = "SELECT u.*, r.nombre AS rol
                FROM users u
                LEFT JOIN users_roles ur ON ur.id_usuario = u.id_usuario
                LEFT JOIN rol r ON r.id_rol = ur.id_rol
                ORDER BY u.id_usuario DESC"; // Consulta usuarios

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function obtenerPorId($id)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT u.id_user AS id_usuario, u.nombres AS nombre, u.apellidos AS apellido,
                           u.documento_identidad, u.correo, u.telefono, u.password_hash AS hash_contrasena,
                           u.estado, r.nombre AS rol
                    FROM user u
                    LEFT JOIN user_roles ur ON ur.id_user = u.id_user
                    LEFT JOIN roles r ON r.id_rol = ur.id_rol
                    WHERE u.id_user = :id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $this->normalizarUsuario($stmt->fetch(PDO::FETCH_ASSOC));
        }

        $sql = "SELECT u.*, r.nombre AS rol
                FROM users u
                LEFT JOIN users_roles ur ON ur.id_usuario = u.id_usuario
                LEFT JOIN rol r ON r.id_rol = ur.id_rol
                WHERE u.id_usuario = :id
                LIMIT 1"; // Busca usuario

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID usuario
        $stmt->execute(); // Ejecuta consulta

        return $this->normalizarUsuario($stmt->fetch(PDO::FETCH_ASSOC)); // Retorna usuario
    }

    public function obtenerPorCorreo($correo)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT u.id_user AS id_usuario, u.nombres AS nombre, u.apellidos AS apellido,
                           u.documento_identidad, u.correo, u.telefono, u.password_hash AS hash_contrasena,
                           u.estado, r.nombre AS rol
                    FROM user u
                    LEFT JOIN user_roles ur ON ur.id_user = u.id_user
                    LEFT JOIN roles r ON r.id_rol = ur.id_rol
                    WHERE u.correo = :correo
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();

            return $this->normalizarUsuario($stmt->fetch(PDO::FETCH_ASSOC));
        }

        $sql = "SELECT u.*, r.nombre AS rol
                FROM users u
                LEFT JOIN users_roles ur ON ur.id_usuario = u.id_usuario
                LEFT JOIN rol r ON r.id_rol = ur.id_rol
                WHERE u.correo = :correo
                LIMIT 1"; // Busca por correo

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':correo', $correo); // Correo
        $stmt->execute(); // Ejecuta consulta

        return $this->normalizarUsuario($stmt->fetch(PDO::FETCH_ASSOC)); // Retorna usuario
    }

    public function obtenerPorDocumentoIdentidad($documento)
    {
        if ($documento === '' || $documento === null) {
            return false;
        }

        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT u.id_user AS id_usuario, u.nombres AS nombre, u.apellidos AS apellido,
                           u.documento_identidad, u.correo, u.telefono, u.password_hash AS hash_contrasena,
                           u.estado, r.nombre AS rol
                    FROM user u
                    LEFT JOIN user_roles ur ON ur.id_user = u.id_user
                    LEFT JOIN roles r ON r.id_rol = ur.id_rol
                    WHERE u.documento_identidad = :documento
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':documento', $documento);
            $stmt->execute();

            return $this->normalizarUsuario($stmt->fetch(PDO::FETCH_ASSOC));
        }

        $sql = "SELECT u.*, r.nombre AS rol
                FROM users u
                LEFT JOIN users_roles ur ON ur.id_usuario = u.id_usuario
                LEFT JOIN rol r ON r.id_rol = ur.id_rol
                WHERE u.documento_identidad = :documento
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':documento', $documento);
        $stmt->execute();

        return $this->normalizarUsuario($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function activarDesdeSolicitud($usuarioId, array $datos)
    {
        $sql = "UPDATE users
                SET nombre = :nombre, apellido = :apellido, telefono = :telefono,
                    documento_identidad = :documento_identidad, estado = 'ACTIVO'
                WHERE id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':apellido', $datos['apellido'] ?? '');
        $stmt->bindValue(':telefono', $datos['telefono'] ?? null);
        $stmt->bindValue(':documento_identidad', $datos['documento_identidad'] ?? null);
        $stmt->bindParam(':id', $usuarioId);

        return $stmt->execute();
    }

    public function crear($datos)
    {
        $passwordPlano = $datos['password'] ?? '';
        $passwordHash = contrasenaYaHasheada($passwordPlano)
            ? $passwordPlano
            : password_hash($passwordPlano, PASSWORD_DEFAULT);

        if ($this->usaEsquemaNuevo()) {
            $sql = "INSERT INTO user
                    (nombres, apellidos, documento_identidad, correo, telefono, password_hash, estado)
                    VALUES
                    (:nombre, :apellido, :documento_identidad, :correo, :telefono, :password_hash, :estado)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindValue(':apellido', $datos['apellido'] ?? '');
            $stmt->bindValue(':documento_identidad', $datos['documento_identidad'] ?? null);
            $stmt->bindParam(':correo', $datos['correo']);
            $stmt->bindValue(':telefono', $datos['telefono'] ?? null);
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindValue(':estado', strtoupper($datos['estado'] ?? 'ACTIVO'));
            $stmt->execute();

            return $this->db->lastInsertId();
        }

        $sql = "INSERT INTO users 
                (nombre, apellido, correo, hash_contrasena, estado, origen_registro, telefono, documento_identidad, fecha_registro)
                VALUES
                (:nombre, :apellido, :correo, :hash_contrasena, :estado, :origen_registro, :telefono, :documento_identidad, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':apellido', $datos['apellido'] ?? '');
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':hash_contrasena', $passwordHash);
        $stmt->bindValue(':estado', strtoupper($datos['estado'] ?? 'ACTIVO'));
        $stmt->bindValue(':origen_registro', $datos['origen_registro'] ?? 'SELF_SERVICE');
        $stmt->bindValue(':telefono', $datos['telefono'] ?? null);
        $stmt->bindValue(':documento_identidad', $datos['documento_identidad'] ?? null);

        $stmt->execute();

        return $this->db->lastInsertId();
    }

    public function asignarRol($usuarioId, $rolId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "INSERT IGNORE INTO user_roles (id_user, id_rol) VALUES (:id_user, :id_rol)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_user', $usuarioId);
            $stmt->bindParam(':id_rol', $rolId);

            return $stmt->execute();
        }

        $sql = "INSERT IGNORE INTO users_roles (id_usuario, id_rol) VALUES (:id_usuario, :id_rol)"; // Asigna rol
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_usuario', $usuarioId); // Usuario
        $stmt->bindParam(':id_rol', $rolId); // Rol

        return $stmt->execute(); // Ejecuta asignación
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE users 
                SET nombre = :nombre, correo = :correo, estado = :estado
                WHERE id_usuario = :id"; // Actualiza usuario

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':correo', $datos['correo']); // Correo
        $stmt->bindParam(':estado', $datos['estado']); // Estado
        $stmt->bindParam(':id', $datos['id_usuario']); // ID usuario

        return $stmt->execute(); // Ejecuta actualización
    }

    public function actualizarPassword($id, $password)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT); // Encripta nueva contraseña

        if ($this->usaEsquemaNuevo()) {
            $sql = "UPDATE user SET password_hash = :password_hash WHERE id_user = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        }

        $sql = "UPDATE users SET hash_contrasena = :hash_contrasena WHERE id_usuario = :id"; // Actualiza contraseña
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':hash_contrasena', $passwordHash); // Contraseña segura
        $stmt->bindParam(':id', $id); // ID usuario

        return $stmt->execute(); // Ejecuta actualización
    }

    public function validarLogin($correo, $password)
    {
        $usuario = $this->obtenerPorCorreo($correo); // Busca usuario

        if (!$usuario) { // Valida existencia
            return false; // No existe
        }

        $hash = $usuario['password'] ?? $usuario['hash_contrasena'] ?? $usuario['password_hash'] ?? '';

        if (!password_verify($password, $hash)) {
            $infoHash = password_get_info($hash);

            if (($infoHash['algo'] ?? 0) === 0 && $hash !== '' && hash_equals($hash, $password)) {
                $this->actualizarPassword($usuario['id'] ?? $usuario['id_usuario'], $password);

                return $this->obtenerPorId($usuario['id'] ?? $usuario['id_usuario']);
            }
        }

        if (!password_verify($password, $hash)) {
            return false;
        }

        return $usuario; // Retorna usuario válido
    }

    public function obtenerPorRol($rolNombre)
    {
        $sql = "SELECT u.*, r.nombre AS rol
                FROM users u
                INNER JOIN users_roles ur ON ur.id_usuario = u.id_usuario
                INNER JOIN rol r ON r.id_rol = ur.id_rol
                WHERE r.nombre = :rol
                ORDER BY u.nombre ASC"; // Usuarios por rol

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':rol', $rolNombre); // Nombre del rol
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna usuarios
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE users SET estado = :estado WHERE id_usuario = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID usuario

        return $stmt->execute(); // Ejecuta cambio
    }

    public function eliminar($id)
    {
        $id = (int) $id;

        if ($id < 1) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $this->ejecutarSiTablaExiste(
                'DELETE FROM users_roles WHERE id_usuario = :id',
                [':id' => $id]
            );
            $this->ejecutarSiTablaExiste(
                'DELETE FROM notificacion WHERE id_usuario = :id',
                [':id' => $id]
            );
            $this->ejecutarSiTablaExiste(
                'DELETE FROM token_recuperacion WHERE id_usuario = :id',
                [':id' => $id]
            );
            $this->ejecutarSiTablaExiste(
                'DELETE FROM cliente WHERE id_cliente = :id',
                [':id' => $id]
            );
            $this->ejecutarSiTablaExiste(
                'DELETE FROM coach WHERE id_coach = :id',
                [':id' => $id]
            );

            $stmt = $this->db->prepare('DELETE FROM users WHERE id_usuario = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();

            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    private function ejecutarSiTablaExiste(string $sql, array $params): void
    {
        if (!preg_match('/\b(FROM|INTO|UPDATE)\s+([a-z_]+)/i', $sql, $coincidencia)) {
            return;
        }

        $tabla = $coincidencia[2];

        try {
            $check = $this->db->query("SHOW TABLES LIKE " . $this->db->quote($tabla));

            if (!$check || !$check->fetch()) {
                return;
            }
        } catch (PDOException $e) {
            return;
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->execute();
    }

    public function contarPorRol($rolNombre)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM users u
                INNER JOIN users_roles ur ON ur.id_usuario = u.id_usuario
                INNER JOIN rol r ON r.id_rol = ur.id_rol
                WHERE r.nombre = :rol"; // Cuenta por rol

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':rol', $rolNombre); // Rol
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna total
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Usuarios', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

class_alias('UsuarioModel', 'Usuario'); // Alias compatible

?>
