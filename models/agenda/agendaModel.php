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
        $sql = "SELECT * FROM agenda WHERE cliente_id = :cliente_id ORDER BY fecha ASC, hora ASC"; // Consulta agenda cliente
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna registros
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT * FROM agenda WHERE coach_id = :coach_id ORDER BY fecha ASC, hora ASC"; // Consulta agenda coach
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna registros
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO agenda 
                (cliente_id, coach_id, titulo, descripcion, fecha, hora, modalidad, estado)
                VALUES
                (:cliente_id, :coach_id, :titulo, :descripcion, :fecha, :hora, :modalidad, :estado)"; // Inserta agenda

        $stmt = $this->db->prepare($sql); // Prepara consulta

        $stmt->bindParam(':cliente_id', $datos['cliente_id']); // Cliente relacionado
        $stmt->bindParam(':coach_id', $datos['coach_id']); // Coach relacionado
        $stmt->bindParam(':titulo', $datos['titulo']); // Título
        $stmt->bindParam(':descripcion', $datos['descripcion']); // Descripción
        $stmt->bindParam(':fecha', $datos['fecha']); // Fecha
        $stmt->bindParam(':hora', $datos['hora']); // Hora
        $stmt->bindParam(':modalidad', $datos['modalidad']); // Modalidad
        $stmt->bindParam(':estado', $datos['estado']); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE agenda SET estado = :estado WHERE id = :id"; // Actualiza estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Nuevo estado
        $stmt->bindParam(':id', $id); // ID agenda

        return $stmt->execute(); // Ejecuta actualización
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Agenda', :accion, NOW())"; // Inserta historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Guarda trazabilidad
    }
}

?>
