<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class RolModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM rol ORDER BY id_rol ASC"; // Consulta roles
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function obtenerActivos()
    {
        $sql = "SELECT * FROM rol WHERE activo = 1 ORDER BY nombre ASC"; // Roles activos
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna activos
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM rol WHERE id_rol = :id LIMIT 1"; // Busca rol
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna rol
    }

    public function obtenerPorNombre($nombre)
    {
        $sql = "SELECT * FROM rol WHERE nombre = :nombre LIMIT 1"; // Busca por nombre
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $nombre); // Asigna nombre
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna rol
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Roles', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>
