<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class ComidaModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodas()
    {
        $sql = "SELECT * FROM comida ORDER BY id_comida DESC"; // Consulta comidas
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM comida WHERE id_comida = :id LIMIT 1"; // Busca comida
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna comida
    }

    public function obtenerPorPlan($planNutricionalId)
    {
        $sql = "SELECT * FROM comida 
                WHERE id_plan_nutricional = :plan_nutricional_id
                ORDER BY tiempo_comida ASC, id_comida ASC"; // Comidas del plan

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':plan_nutricional_id', $planNutricionalId); // Plan nutricional
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna comidas
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT c.* 
                FROM comida c
                INNER JOIN plan_nutricional pn ON pn.id_plan_nutricional = c.id_plan_nutricional
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id
                AND pn.estado_plan = 'ACTIVO'
                ORDER BY c.tiempo_comida ASC, c.id_comida ASC"; // Comidas activas del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna comidas
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT c.*, pn.nombre AS plan_nutricional
                FROM comida c
                INNER JOIN plan_nutricional pn ON pn.id_plan_nutricional = c.id_plan_nutricional
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                WHERE pc.id_coach = :coach_id
                ORDER BY c.id_comida DESC"; // Comidas creadas por coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna comidas
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO comida 
                (id_plan_nutricional, tiempo_comida, grupos_alimenticios, porciones, calorias_aprox, observaciones)
                VALUES
                (:id_plan_nutricional, :tiempo_comida, :grupos_alimenticios, :porciones, :calorias_aprox, :observaciones)"; // Crea comida

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_plan_nutricional', $datos['id_plan_nutricional'] ?? $datos['plan_nutricional_id']); // Plan nutricional
        $stmt->bindValue(':tiempo_comida', $datos['tiempo_comida'] ?? $datos['nombre'] ?? null); // Tiempo comida
        $stmt->bindValue(':grupos_alimenticios', $datos['grupos_alimenticios'] ?? $datos['descripcion'] ?? null); // Grupos
        $stmt->bindValue(':porciones', $datos['porciones'] ?? null); // Porciones
        $stmt->bindValue(':calorias_aprox', $datos['calorias_aprox'] ?? $datos['calorias'] ?? null); // Calorías
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? null); // Observaciones

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE comida 
                SET tiempo_comida = :tiempo_comida, grupos_alimenticios = :grupos_alimenticios,
                    porciones = :porciones, calorias_aprox = :calorias_aprox, observaciones = :observaciones
                WHERE id_comida = :id"; // Actualiza comida

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindValue(':tiempo_comida', $datos['tiempo_comida'] ?? $datos['nombre'] ?? null); // Tiempo comida
        $stmt->bindValue(':grupos_alimenticios', $datos['grupos_alimenticios'] ?? $datos['descripcion'] ?? null); // Grupos
        $stmt->bindValue(':porciones', $datos['porciones'] ?? null); // Porciones
        $stmt->bindValue(':calorias_aprox', $datos['calorias_aprox'] ?? $datos['calorias'] ?? null); // Calorías
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? null); // Observaciones
        $stmt->bindParam(':id', $datos['id'] ?? $datos['id_comida']); // ID comida

        return $stmt->execute(); // Ejecuta actualización
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM comida WHERE id_comida = :id"; // Elimina comida
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID comida

        return $stmt->execute(); // Ejecuta eliminación
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        try {
            $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                    VALUES (:usuario_id, 'Comidas', :accion, NOW())"; // Guarda historial

            $stmt = $this->db->prepare($sql); // Prepara consulta
            $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
            $stmt->bindParam(':accion', $accion); // Acción realizada

            return $stmt->execute(); // Ejecuta registro
        } catch (PDOException $e) {
            return false; // Error al registrar
        }
    }
}

?>
