<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class ProgramaModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodos()
    {
        try { // Tabla programas puede no existir en el esquema actual
            $sql = "SELECT * FROM programas ORDER BY id DESC"; // Consulta programas
            $stmt = $this->db->prepare($sql); // Prepara consulta
            $stmt->execute(); // Ejecuta consulta

            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
        } catch (PDOException $e) {
            return []; // Sin tabla programas
        }
    }

    public function obtenerActivos()
    {
        $sql = "SELECT * FROM programas 
                WHERE estado = 'activo'
                ORDER BY nombre ASC"; // Consulta activos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna programas activos
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM programas WHERE id = :id LIMIT 1"; // Busca programa
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna programa
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO programas 
                (nombre, descripcion, precio, duracion, modalidad, estado)
                VALUES
                (:nombre, :descripcion, :precio, :duracion, :modalidad, :estado)"; // Crea programa

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? ''); // Descripción
        $stmt->bindValue(':precio', $datos['precio'] ?? 0); // Precio
        $stmt->bindValue(':duracion', $datos['duracion'] ?? ''); // Duración
        $stmt->bindValue(':modalidad', $datos['modalidad'] ?? 'virtual'); // Modalidad
        $stmt->bindValue(':estado', $datos['estado'] ?? 'activo'); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE programas 
                SET nombre = :nombre, descripcion = :descripcion, precio = :precio,
                    duracion = :duracion, modalidad = :modalidad, estado = :estado
                WHERE id = :id"; // Actualiza programa

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? ''); // Descripción
        $stmt->bindValue(':precio', $datos['precio'] ?? 0); // Precio
        $stmt->bindValue(':duracion', $datos['duracion'] ?? ''); // Duración
        $stmt->bindValue(':modalidad', $datos['modalidad'] ?? 'virtual'); // Modalidad
        $stmt->bindParam(':estado', $datos['estado']); // Estado
        $stmt->bindParam(':id', $datos['id']); // ID programa

        return $stmt->execute(); // Ejecuta actualización
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE programas SET estado = :estado WHERE id = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID programa

        return $stmt->execute(); // Ejecuta cambio
    }

    public function reporteGeneral()
    {
        $sql = "SELECT modalidad, COUNT(*) AS total
                FROM programas
                GROUP BY modalidad"; // Reporte programas

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna reporte
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Programas', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>
