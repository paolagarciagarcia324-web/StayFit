<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class PlanEntrenamientoModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM plan_entrenamiento ORDER BY id_plan_entrenamiento DESC"; // Consulta planes
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM plan_entrenamiento WHERE id_plan_entrenamiento = :id LIMIT 1"; // Busca plan
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID plan
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna plan
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT pe.* FROM plan_entrenamiento pe
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id 
                ORDER BY pe.id_plan_entrenamiento DESC"; // Planes del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna planes
    }

    public function obtenerActivoPorCliente($clienteId)
    {
        $sql = "SELECT pe.* FROM plan_entrenamiento pe
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id 
                AND pe.estado_plan = 'ACTIVO'
                ORDER BY pe.id_plan_entrenamiento DESC
                LIMIT 1"; // Plan activo del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna plan activo
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT pe.*, u.nombre AS cliente
                FROM plan_entrenamiento pe
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                INNER JOIN users u ON u.id_usuario = pc.id_cliente
                WHERE pc.id_coach = :coach_id
                ORDER BY pe.id_plan_entrenamiento DESC"; // Planes del coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna planes
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO plan_entrenamiento 
                (id_plan_cliente, nombre, objetivo, nivel_dificultad, duracion_total_dias, estado_plan)
                VALUES
                (:id_plan_cliente, :nombre, :objetivo, :nivel_dificultad, :duracion_total_dias, :estado_plan)"; // Crea plan

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']); // Plan cliente
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':objetivo', $datos['objetivo']); // Objetivo
        $stmt->bindValue(':nivel_dificultad', $datos['nivel_dificultad'] ?? $datos['nivel'] ?? ''); // Nivel
        $stmt->bindValue(':duracion_total_dias', $datos['duracion_total_dias'] ?? $datos['duracion'] ?? null); // Duración
        $stmt->bindValue(':estado_plan', strtoupper($datos['estado_plan'] ?? $datos['estado'] ?? 'ACTIVO')); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE plan_entrenamiento 
                SET nombre = :nombre, objetivo = :objetivo, nivel_dificultad = :nivel_dificultad,
                    duracion_total_dias = :duracion_total_dias, estado_plan = :estado_plan
                WHERE id_plan_entrenamiento = :id"; // Actualiza plan

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':objetivo', $datos['objetivo']); // Objetivo
        $stmt->bindParam(':nivel_dificultad', $datos['nivel_dificultad'] ?? $datos['nivel']); // Nivel
        $stmt->bindValue(':duracion_total_dias', $datos['duracion_total_dias'] ?? $datos['duracion']); // Duración
        $stmt->bindParam(':estado_plan', $datos['estado_plan'] ?? $datos['estado']); // Estado
        $stmt->bindParam(':id', $datos['id']); // ID plan

        return $stmt->execute(); // Ejecuta actualización
    }

    public function cambiarEstado($id, $estado)
    {
        $estadoBd = strtoupper($estado);
        $sql = "UPDATE plan_entrenamiento SET estado_plan = :estado_plan WHERE id_plan_entrenamiento = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado_plan', $estadoBd); // Nuevo estado
        $stmt->bindParam(':id', $id); // ID plan

        return $stmt->execute(); // Ejecuta cambio
    }

    public function finalizarPlanesCliente($clienteId)
    {
        $sql = "UPDATE plan_entrenamiento pe
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pe.id_plan_cliente
                SET pe.estado_plan = 'FINALIZADO'
                WHERE pc.id_cliente = :cliente_id 
                AND pe.estado_plan = 'ACTIVO'"; // Finaliza activos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente

        return $stmt->execute(); // Ejecuta actualización
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Plan entrenamiento', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>