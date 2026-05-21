<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class DisponibilidadModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT * FROM disponibilidades 
                WHERE coach_id = :coach_id
                ORDER BY dia ASC, hora_inicio ASC"; // Consulta disponibilidad

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna horarios
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO disponibilidades 
                (coach_id, dia, hora_inicio, hora_fin, modalidad, estado)
                VALUES
                (:coach_id, :dia, :hora_inicio, :hora_fin, :modalidad, :estado)"; // Crea disponibilidad

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $datos['coach_id']); // Coach
        $stmt->bindParam(':dia', $datos['dia']); // Día
        $stmt->bindParam(':hora_inicio', $datos['hora_inicio']); // Hora inicio
        $stmt->bindParam(':hora_fin', $datos['hora_fin']); // Hora fin
        $stmt->bindParam(':modalidad', $datos['modalidad']); // Modalidad
        $stmt->bindValue(':estado', $datos['estado'] ?? 'activo'); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE disponibilidades 
                SET dia = :dia, hora_inicio = :hora_inicio, hora_fin = :hora_fin,
                    modalidad = :modalidad, estado = :estado
                WHERE id = :id"; // Actualiza disponibilidad

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':dia', $datos['dia']); // Día
        $stmt->bindParam(':hora_inicio', $datos['hora_inicio']); // Hora inicio
        $stmt->bindParam(':hora_fin', $datos['hora_fin']); // Hora fin
        $stmt->bindParam(':modalidad', $datos['modalidad']); // Modalidad
        $stmt->bindParam(':estado', $datos['estado']); // Estado
        $stmt->bindParam(':id', $datos['id']); // ID disponibilidad

        return $stmt->execute(); // Ejecuta actualización
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM disponibilidades WHERE id = :id"; // Elimina disponibilidad
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID disponibilidad

        return $stmt->execute(); // Ejecuta eliminación
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE disponibilidades SET estado = :estado WHERE id = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Nuevo estado
        $stmt->bindParam(':id', $id); // ID disponibilidad

        return $stmt->execute(); // Ejecuta cambio
    }

    public function validarCruceHorario($coachId, $dia, $horaInicio, $horaFin)
    {
        $sql = "SELECT COUNT(*) AS total FROM disponibilidades
                WHERE coach_id = :coach_id
                AND dia = :dia
                AND estado = 'activo'
                AND (:hora_inicio < hora_fin AND :hora_fin > hora_inicio)"; // Valida cruce

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->bindParam(':dia', $dia); // Día
        $stmt->bindParam(':hora_inicio', $horaInicio); // Hora inicio
        $stmt->bindParam(':hora_fin', $horaFin); // Hora fin
        $stmt->execute(); // Ejecuta consulta

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC); // Obtiene resultado

        return ($resultado['total'] ?? 0) > 0; // Retorna si hay cruce
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Disponibilidad Coach', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>