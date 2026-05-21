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
        $sql = "SELECT * FROM clientes_institucionales WHERE usuario_id = :usuario_id LIMIT 1"; // Busca por usuario
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Asigna usuario
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna cliente
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM clientes_institucionales WHERE id = :id LIMIT 1"; // Busca por ID
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna registro
    }

    public function actualizarPerfil($datos)
    {
        $sql = "UPDATE clientes_institucionales 
                SET identificacion = :identificacion, edad = :edad, celular = :celular
                WHERE id = :id"; // Actualiza perfil

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':identificacion', $datos['identificacion']); // Documento
        $stmt->bindParam(':edad', $datos['edad']); // Edad
        $stmt->bindParam(':celular', $datos['celular']); // Celular
        $stmt->bindParam(':id', $datos['id']); // ID cliente

        return $stmt->execute(); // Ejecuta actualización
    }

    public function obtenerConvenio($clienteId)
    {
        $sql = "SELECT c.* 
                FROM convenios c
                INNER JOIN clientes_institucionales ci ON ci.institucion_id = c.institucion_id
                WHERE ci.id = :cliente_id
                ORDER BY c.id DESC
                LIMIT 1"; // Consulta convenio activo

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna convenio
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM clientes_institucionales ORDER BY id DESC"; // Consulta todos
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE clientes_institucionales SET estado = :estado WHERE id = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID cliente

        return $stmt->execute(); // Ejecuta cambio
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Cliente Institucional', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>