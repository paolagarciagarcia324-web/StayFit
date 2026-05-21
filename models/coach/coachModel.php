<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class CoachModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM coaches ORDER BY id DESC"; // Consulta coaches
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function obtenerActivos()
    {
        $sql = "SELECT * FROM coaches WHERE estado = 'activo' ORDER BY nombre ASC"; // Consulta activos
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna coaches activos
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM coaches WHERE id = :id LIMIT 1"; // Busca por ID
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna coach
    }

    public function obtenerPorUsuario($usuarioId)
    {
        $sql = "SELECT * FROM coaches WHERE usuario_id = :usuario_id LIMIT 1"; // Busca por usuario
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Asigna usuario
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna coach
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO coaches 
                (usuario_id, nombre, identificacion, celular, especialidad, biografia, estado)
                VALUES
                (:usuario_id, :nombre, :identificacion, :celular, :especialidad, :biografia, :estado)"; // Crea coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindValue(':usuario_id', $datos['usuario_id'] ?? null); // Usuario relacionado
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':identificacion', $datos['identificacion']); // Documento
        $stmt->bindParam(':celular', $datos['celular']); // Celular
        $stmt->bindParam(':especialidad', $datos['especialidad']); // Especialidad
        $stmt->bindValue(':biografia', $datos['biografia'] ?? ''); // Biografía
        $stmt->bindValue(':estado', $datos['estado'] ?? 'activo'); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE coaches 
                SET nombre = :nombre, identificacion = :identificacion, celular = :celular,
                    especialidad = :especialidad, biografia = :biografia
                WHERE id = :id"; // Actualiza coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':identificacion', $datos['identificacion']); // Documento
        $stmt->bindParam(':celular', $datos['celular']); // Celular
        $stmt->bindParam(':especialidad', $datos['especialidad']); // Especialidad
        $stmt->bindValue(':biografia', $datos['biografia'] ?? ''); // Biografía
        $stmt->bindParam(':id', $datos['id']); // ID coach

        return $stmt->execute(); // Ejecuta actualización
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE coaches SET estado = :estado WHERE id = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Nuevo estado
        $stmt->bindParam(':id', $id); // ID coach

        return $stmt->execute(); // Ejecuta cambio
    }

    public function contarActivos()
    {
        $sql = "SELECT COUNT(*) AS total FROM coaches WHERE estado = 'activo'"; // Cuenta activos
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna total
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Coaches', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>