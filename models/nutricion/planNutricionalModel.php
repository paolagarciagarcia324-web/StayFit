<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class PlanNutricionalModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM plan_nutricional ORDER BY id_plan_nutricional DESC"; // Consulta planes
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM plan_nutricional WHERE id_plan_nutricional = :id LIMIT 1"; // Busca plan
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna plan
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT pn.* FROM plan_nutricional pn
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id 
                ORDER BY pn.id_plan_nutricional DESC"; // Planes del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna planes
    }

    public function obtenerActivoPorCliente($clienteId)
    {
        $sql = "SELECT pn.* FROM plan_nutricional pn
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id
                AND pn.estado_plan = 'ACTIVO'
                ORDER BY pn.id_plan_nutricional DESC
                LIMIT 1"; // Plan activo del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna plan activo
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT pn.*, u.nombre AS cliente
                FROM plan_nutricional pn
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                INNER JOIN users u ON u.id_usuario = pc.id_cliente
                WHERE pc.id_coach = :coach_id
                ORDER BY pn.id_plan_nutricional DESC"; // Planes del coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna planes
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO plan_nutricional 
                (id_plan_cliente, nombre, objetivo, estado_plan, recomendaciones_adicionales)
                VALUES
                (:id_plan_cliente, :nombre, :objetivo, :estado_plan, :recomendaciones_adicionales)"; // Crea plan nutricional

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']); // Plan cliente
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':objetivo', $datos['objetivo']); // Objetivo
        $stmt->bindValue(':estado_plan', strtoupper($datos['estado_plan'] ?? $datos['estado'] ?? 'ACTIVO')); // Estado
        $stmt->bindValue(':recomendaciones_adicionales', $datos['recomendaciones_adicionales'] ?? ''); // Recomendaciones

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE plan_nutricional 
                SET nombre = :nombre, objetivo = :objetivo,
                    estado_plan = :estado_plan, recomendaciones_adicionales = :recomendaciones_adicionales
                WHERE id_plan_nutricional = :id"; // Actualiza plan

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':objetivo', $datos['objetivo']); // Objetivo
        $stmt->bindParam(':estado_plan', $datos['estado_plan']); // Estado
        $stmt->bindValue(':recomendaciones_adicionales', $datos['recomendaciones_adicionales'] ?? ''); // Recomendaciones
        $stmt->bindParam(':id', $datos['id']); // ID plan

        return $stmt->execute(); // Ejecuta actualización
    }

    public function cambiarEstado($id, $estado)
    {
        $estadoBd = strtoupper($estado);
        $sql = "UPDATE plan_nutricional SET estado_plan = :estado_plan WHERE id_plan_nutricional = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado_plan', $estadoBd); // Nuevo estado
        $stmt->bindParam(':id', $id); // ID plan

        return $stmt->execute(); // Ejecuta cambio
    }

    public function finalizarPlanesCliente($clienteId)
    {
        $sql = "UPDATE plan_nutricional pn
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                SET pn.estado_plan = 'FINALIZADO'
                WHERE pc.id_cliente = :cliente_id
                AND pn.estado_plan = 'ACTIVO'"; // Finaliza planes activos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente

        return $stmt->execute(); // Ejecuta actualización
    }

    public function reportePorCoach($coachId)
    {
        $sql = "SELECT pn.estado_plan AS estado, COUNT(*) AS total
                FROM plan_nutricional pn
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                WHERE pc.id_coach = :coach_id
                GROUP BY pn.estado_plan"; // Reporte nutricional coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna reporte
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Plan nutricional', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>