<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class EjercicioModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM ejercicios ORDER BY id DESC"; // Consulta ejercicios
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM ejercicios WHERE id = :id LIMIT 1"; // Busca ejercicio
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna ejercicio
    }

    public function obtenerPorRutina($rutinaId)
    {
        $sql = "SELECT * FROM ejercicios 
                WHERE rutina_id = :rutina_id 
                ORDER BY id ASC"; // Ejercicios de rutina

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':rutina_id', $rutinaId); // Rutina
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna ejercicios
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT e.* 
                FROM ejercicios e
                INNER JOIN rutinas r ON r.id = e.rutina_id
                WHERE r.coach_id = :coach_id
                ORDER BY e.id DESC"; // Ejercicios del coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna ejercicios
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO ejercicios 
                (rutina_id, nombre, descripcion, series, repeticiones, descanso, estado)
                VALUES 
                (:rutina_id, :nombre, :descripcion, :series, :repeticiones, :descanso, :estado)"; // Crea ejercicio

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':rutina_id', $datos['rutina_id']); // Rutina
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':descripcion', $datos['descripcion']); // Descripción
        $stmt->bindParam(':series', $datos['series']); // Series
        $stmt->bindParam(':repeticiones', $datos['repeticiones']); // Repeticiones
        $stmt->bindParam(':descanso', $datos['descanso']); // Descanso
        $stmt->bindValue(':estado', $datos['estado'] ?? 'activo'); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE ejercicios 
                SET nombre = :nombre, descripcion = :descripcion, series = :series,
                    repeticiones = :repeticiones, descanso = :descanso, estado = :estado
                WHERE id = :id"; // Actualiza ejercicio

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':descripcion', $datos['descripcion']); // Descripción
        $stmt->bindParam(':series', $datos['series']); // Series
        $stmt->bindParam(':repeticiones', $datos['repeticiones']); // Repeticiones
        $stmt->bindParam(':descanso', $datos['descanso']); // Descanso
        $stmt->bindParam(':estado', $datos['estado']); // Estado
        $stmt->bindParam(':id', $datos['id']); // ID ejercicio

        return $stmt->execute(); // Ejecuta actualización
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE ejercicios SET estado = :estado WHERE id = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Nuevo estado
        $stmt->bindParam(':id', $id); // ID ejercicio

        return $stmt->execute(); // Ejecuta cambio
    }

    public function guardarMaterial($datos)
    {
        $sql = "INSERT INTO materiales_entrenamiento 
                (ejercicio_id, titulo, tipo, url, estado, fecha_creacion)
                VALUES
                (:ejercicio_id, :titulo, :tipo, :url, :estado, NOW())"; // Guarda material

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':ejercicio_id', $datos['ejercicio_id']); // Ejercicio
        $stmt->bindParam(':titulo', $datos['titulo']); // Título
        $stmt->bindParam(':tipo', $datos['tipo']); // Tipo
        $stmt->bindParam(':url', $datos['url']); // Ruta o enlace
        $stmt->bindValue(':estado', $datos['estado'] ?? 'activo'); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function obtenerMaterialPorCoach($coachId)
    {
        $sql = "SELECT m.*, e.nombre AS ejercicio
                FROM materiales_entrenamiento m
                INNER JOIN ejercicios e ON e.id = m.ejercicio_id
                INNER JOIN rutinas r ON r.id = e.rutina_id
                WHERE r.coach_id = :coach_id
                ORDER BY m.id DESC"; // Material del coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna materiales
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Ejercicios', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>