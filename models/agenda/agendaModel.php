<?php

require_once __DIR__ . '/../../config/database.php'; // Importa la conexión

class AgendaModel
{
    private $db; // Conexión a la base de datos

    public function __construct()
    {
        $database = new Database(); // Crea instancia de conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT a.*
                FROM agenda a
                INNER JOIN sesion s ON s.id_coach = a.id_coach
                INNER JOIN sesion_participante sp ON sp.id_sesion = s.id_sesion
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = sp.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id
                AND a.fecha_hora_inicio >= NOW()
                ORDER BY a.fecha_hora_inicio ASC"; // Agenda del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna registros
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT * FROM agenda WHERE id_coach = :coach_id ORDER BY fecha_hora_inicio ASC"; // Consulta agenda coach
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna registros
    }

    public function obtenerDisponiblesPorCoach($coachId)
    {
        $sql = "SELECT * FROM agenda
                WHERE id_coach = :coach_id
                AND disponible = 1
                AND fecha_hora_inicio >= NOW()
                ORDER BY fecha_hora_inicio ASC"; // Slots disponibles

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna registros
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO agenda 
                (id_coach, fecha_hora_inicio, fecha_hora_fin, disponible, descripcion)
                VALUES
                (:id_coach, :fecha_hora_inicio, :fecha_hora_fin, :disponible, :descripcion)"; // Inserta agenda

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_coach', $datos['id_coach']); // Coach relacionado
        $stmt->bindParam(':fecha_hora_inicio', $datos['fecha_hora_inicio']); // Inicio
        $stmt->bindParam(':fecha_hora_fin', $datos['fecha_hora_fin']); // Fin
        $stmt->bindValue(':disponible', $datos['disponible'] ?? 1); // Disponible
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? null); // Descripción

        return $stmt->execute(); // Ejecuta registro
    }

    public function cambiarDisponibilidad($id, $disponible)
    {
        $sql = "UPDATE agenda SET disponible = :disponible WHERE id_agenda = :id"; // Actualiza disponibilidad
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':disponible', $disponible); // Nuevo valor
        $stmt->bindParam(':id', $id); // ID agenda

        return $stmt->execute(); // Ejecuta actualización
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Agenda', :accion, NOW())"; // Inserta historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Guarda trazabilidad
    }
}

?>
