<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class RutinaModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM rutina ORDER BY id_rutina DESC"; // Consulta rutinas
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM rutina WHERE id_rutina = :id LIMIT 1"; // Busca rutina
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID rutina
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna rutina
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT r.*, pe.nombre AS plan_nombre
                FROM rutina r
                INNER JOIN plan_entrenamiento pe ON pe.id_plan_entrenamiento = r.id_plan_entrenamiento
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id
                ORDER BY r.id_rutina DESC"; // Rutinas del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna rutinas
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT r.*, u.nombre AS cliente
                FROM rutina r
                INNER JOIN plan_entrenamiento pe ON pe.id_plan_entrenamiento = r.id_plan_entrenamiento
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                INNER JOIN users u ON u.id_usuario = pc.id_cliente
                WHERE pc.id_coach = :coach_id
                ORDER BY r.id_rutina DESC"; // Rutinas del coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna rutinas
    }

    public function obtenerPendientesPorCoach($coachId)
    {
        $sql = "SELECT r.*, u.nombre AS cliente, rr.estado AS estado_registro
                FROM rutina r
                INNER JOIN plan_entrenamiento pe ON pe.id_plan_entrenamiento = r.id_plan_entrenamiento
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                INNER JOIN users u ON u.id_usuario = pc.id_cliente
                LEFT JOIN registro_rutina rr ON rr.id_rutina = r.id_rutina
                    AND rr.fecha = CURDATE()
                WHERE pc.id_coach = :coach_id
                AND pc.estado = 'ACTIVO'
                AND (rr.estado IS NULL OR rr.estado = 'PENDIENTE')
                ORDER BY r.id_rutina ASC"; // Rutinas pendientes del día

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna rutinas pendientes
    }

    public function obtenerPorPlanEntrenamiento($planEntrenamientoId)
    {
        $sql = "SELECT * FROM rutina 
                WHERE id_plan_entrenamiento = :plan_entrenamiento_id
                ORDER BY id_rutina ASC"; // Rutinas de un plan de entrenamiento

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':plan_entrenamiento_id', $planEntrenamientoId); // Plan entrenamiento
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna rutinas
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO rutina 
                (id_plan_entrenamiento, nombre, dias_semana, duracion_minutos, version, observaciones)
                VALUES
                (:id_plan_entrenamiento, :nombre, :dias_semana, :duracion_minutos, :version, :observaciones)"; // Crea rutina

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_plan_entrenamiento', $datos['id_plan_entrenamiento'] ?? $datos['plan_entrenamiento_id']); // Plan entrenamiento
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindValue(':dias_semana', $datos['dias_semana'] ?? ''); // Días semana
        $stmt->bindValue(':duracion_minutos', $datos['duracion_minutos'] ?? $datos['duracion'] ?? null); // Duración
        $stmt->bindValue(':version', $datos['version'] ?? 1); // Versión
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? ''); // Observaciones

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE rutina 
                SET nombre = :nombre, dias_semana = :dias_semana, duracion_minutos = :duracion_minutos,
                    version = :version, observaciones = :observaciones
                WHERE id_rutina = :id"; // Actualiza rutina

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindValue(':dias_semana', $datos['dias_semana'] ?? ''); // Días semana
        $stmt->bindValue(':duracion_minutos', $datos['duracion_minutos'] ?? $datos['duracion'] ?? null); // Duración
        $stmt->bindValue(':version', $datos['version'] ?? 1); // Versión
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? ''); // Observaciones
        $stmt->bindParam(':id', $datos['id']); // ID rutina

        return $stmt->execute(); // Ejecuta actualización
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM rutina WHERE id_rutina = :id"; // Elimina rutina
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID rutina

        return $stmt->execute(); // Ejecuta eliminación
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Rutinas', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>