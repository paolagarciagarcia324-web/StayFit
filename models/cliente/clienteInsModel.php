<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class ClienteInsModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerPorUsuario($usuarioId)
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado,
                       c.tipo_cliente, c.objetivos, c.restricciones_medicas,
                       ci.id_institucion, ci.cargo, ci.es_contacto_principal, ci.fecha_vinculacion
                FROM cliente_institucional ci
                INNER JOIN cliente c ON c.id_cliente = ci.id_cliente
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                WHERE ci.id_cliente = :usuario_id
                LIMIT 1"; // Busca por usuario

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Asigna usuario
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna cliente
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado,
                       c.tipo_cliente, c.objetivos,
                       ci.id_institucion, ci.cargo, ci.es_contacto_principal
                FROM cliente_institucional ci
                INNER JOIN cliente c ON c.id_cliente = ci.id_cliente
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                WHERE ci.id_cliente = :id
                LIMIT 1"; // Busca por ID

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna registro
    }

    public function actualizarPerfil($datos)
    {
        $sql = "UPDATE cliente 
                SET objetivos = :objetivos,
                    restricciones_medicas = :restricciones_medicas
                WHERE id_cliente = :id"; // Actualiza perfil

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindValue(':objetivos', $datos['objetivos'] ?? null); // Objetivos
        $stmt->bindValue(':restricciones_medicas', $datos['restricciones_medicas'] ?? null); // Restricciones
        $stmt->bindParam(':id', $datos['id_cliente']); // ID cliente

        return $stmt->execute(); // Ejecuta actualización
    }

    public function obtenerTodos()
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado,
                       ci.id_institucion, ci.cargo, ci.fecha_vinculacion
                FROM cliente_institucional ci
                INNER JOIN cliente c ON c.id_cliente = ci.id_cliente
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                ORDER BY u.id_usuario DESC"; // Consulta todos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE users SET estado = :estado WHERE id_usuario = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID usuario

        return $stmt->execute(); // Ejecuta cambio
    }

    public function vincularInstitucion($datos)
    {
        $sql = "INSERT INTO cliente_institucional (id_cliente, id_institucion, cargo, es_contacto_principal)
                VALUES (:id_cliente, :id_institucion, :cargo, 0)
                ON DUPLICATE KEY UPDATE cargo = :cargo_up, id_institucion = :id_institucion_up"; // Vincula

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_cliente', $datos['cliente_id']); // Cliente
        $stmt->bindParam(':id_institucion', $datos['institucion_id']); // Institución
        $stmt->bindValue(':cargo', $datos['cargo'] ?? ''); // Cargo
        $stmt->bindValue(':cargo_up', $datos['cargo'] ?? ''); // Cargo update
        $stmt->bindParam(':id_institucion_up', $datos['institucion_id']); // Institución update

        return $stmt->execute(); // Ejecuta vínculo
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Cliente Institucional', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>
