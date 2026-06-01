<?php

require_once __DIR__ . '/../../config/database.php'; // Importa la conexión

class SesionModel
{
    private $db; // Conexión a la base de datos

    public function __construct()
    {
        $database = new Database(); // Crea instancia de conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT s.*
                FROM sesion s
                INNER JOIN sesion_participante sp ON sp.id_sesion = s.id_sesion
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = sp.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id
                ORDER BY s.fecha_hora_inicio ASC"; // Sesiones del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna sesiones
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM sesion ORDER BY fecha_hora_inicio DESC"; // Consulta sesiones
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna sesiones
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT * FROM sesion WHERE id_coach = :coach_id ORDER BY fecha_hora_inicio ASC"; // Sesiones coach
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna sesiones
    }

    public function obtenerProximasPorCoach($coachId)
    {
        $sql = "SELECT * FROM sesion 
                WHERE id_coach = :coach_id AND fecha_hora_inicio >= NOW()
                ORDER BY fecha_hora_inicio ASC"; // Próximas sesiones

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna sesiones
    }

    public function obtenerPorPlanCliente($planClienteId)
    {
        $sql = "SELECT s.*
                FROM sesion s
                INNER JOIN sesion_participante sp ON sp.id_sesion = s.id_sesion
                WHERE sp.id_plan_cliente = :plan_cliente_id
                ORDER BY s.fecha_hora_inicio ASC"; // Sesiones del plan cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':plan_cliente_id', $planClienteId); // Plan cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna sesiones
    }

    public function obtenerGrupalesPorPlanCliente($planClienteId)
    {
        $sql = "SELECT s.*
                FROM sesion s
                INNER JOIN sesion_participante sp ON sp.id_sesion = s.id_sesion
                WHERE sp.id_plan_cliente = :plan_cliente_id AND s.tipo = 'GRUPAL'
                ORDER BY s.fecha_hora_inicio ASC"; // Sesiones grupales

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':plan_cliente_id', $planClienteId); // Plan cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna sesiones
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO sesion 
                (id_coach, titulo, descripcion, fecha_hora_inicio, fecha_hora_fin,
                 duracion_minutos, tipo, modalidad, estado, cupo_maximo, enlace_virtual, ubicacion)
                VALUES
                (:id_coach, :titulo, :descripcion, :fecha_hora_inicio, :fecha_hora_fin,
                 :duracion_minutos, :tipo, :modalidad, :estado, :cupo_maximo, :enlace_virtual, :ubicacion)"; // Inserta sesión

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_coach', $datos['id_coach']); // Coach
        $stmt->bindValue(':titulo', $datos['titulo'] ?? null); // Título
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? null); // Descripción
        $stmt->bindParam(':fecha_hora_inicio', $datos['fecha_hora_inicio']); // Inicio
        $stmt->bindParam(':fecha_hora_fin', $datos['fecha_hora_fin']); // Fin
        $stmt->bindValue(':duracion_minutos', $datos['duracion_minutos'] ?? null); // Duración
        $stmt->bindValue(':tipo', $datos['tipo'] ?? 'INDIVIDUAL'); // Tipo
        $stmt->bindValue(':modalidad', $datos['modalidad'] ?? 'VIRTUAL'); // Modalidad
        $stmt->bindValue(':estado', $datos['estado'] ?? 'PROGRAMADA'); // Estado
        $stmt->bindValue(':cupo_maximo', $datos['cupo_maximo'] ?? null); // Cupo
        $stmt->bindValue(':enlace_virtual', $datos['enlace_virtual'] ?? null); // Enlace
        $stmt->bindValue(':ubicacion', $datos['ubicacion'] ?? null); // Ubicación

        return $stmt->execute(); // Ejecuta registro
    }

    public function inscribirParticipante($datos)
    {
        $sql = "INSERT INTO sesion_participante (id_sesion, id_plan_cliente, estado_asistencia, observaciones)
                VALUES (:id_sesion, :id_plan_cliente, :estado_asistencia, :observaciones)"; // Inscribe participante

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_sesion', $datos['id_sesion']); // ID sesión
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']); // Plan cliente
        $stmt->bindValue(':estado_asistencia', $datos['estado_asistencia'] ?? 'INSCRITO'); // Estado
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? null); // Observaciones

        return $stmt->execute(); // Ejecuta registro
    }

    public function marcarAsistencia($datos)
    {
        $sql = "UPDATE sesion_participante 
                SET estado_asistencia = :estado_asistencia, observaciones = :observaciones
                WHERE id_sesion = :id_sesion AND id_plan_cliente = :id_plan_cliente"; // Marca asistencia

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado_asistencia', $datos['estado_asistencia']); // Estado asistencia
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? null); // Observación
        $stmt->bindParam(':id_sesion', $datos['id_sesion']); // ID sesión
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']); // Plan cliente

        return $stmt->execute(); // Ejecuta actualización
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE sesion SET estado = :estado WHERE id_sesion = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Nuevo estado
        $stmt->bindParam(':id', $id); // ID sesión

        return $stmt->execute(); // Ejecuta actualización
    }

    public function reportePorCoach($coachId)
    {
        $sql = "SELECT estado, COUNT(*) AS total
                FROM sesion
                WHERE id_coach = :coach_id
                GROUP BY estado"; // Reporte sesiones

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna reporte
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Sesiones', :accion, NOW())"; // Inserta historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Guarda trazabilidad
    }
}

?>
