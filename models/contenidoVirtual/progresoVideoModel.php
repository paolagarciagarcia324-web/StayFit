<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class ProgresoVideoModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerPorCliente($clienteId)
    {
        try {
            $sql = "SELECT pv.*, v.titulo 
                    FROM progreso_video pv
                    INNER JOIN video v ON v.id_video = pv.id_video
                    WHERE pv.id_cliente = :cliente_id
                    ORDER BY pv.ultimo_acceso DESC"; // Progreso por cliente

            $stmt = $this->db->prepare($sql); // Prepara consulta
            $stmt->bindParam(':cliente_id', $clienteId); // Cliente
            $stmt->execute(); // Ejecuta consulta

            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna progreso
        } catch (PDOException $e) {
            return []; // Tabla aún no existe
        }
    }

    public function obtenerPorCoach($coachId)
    {
        try {
            $sql = "SELECT u.nombre AS cliente, pv.*
                    FROM progreso_video pv
                    INNER JOIN users u ON u.id_usuario = pv.id_cliente
                    INNER JOIN plan_cliente pc ON pc.id_cliente = pv.id_cliente
                    WHERE pc.id_coach = :coach_id
                    ORDER BY pv.ultimo_acceso DESC"; // Progreso de clientes del coach

            $stmt = $this->db->prepare($sql); // Prepara consulta
            $stmt->bindParam(':coach_id', $coachId); // Coach
            $stmt->execute(); // Ejecuta consulta

            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna seguimiento
        } catch (PDOException $e) {
            return []; // Tabla aún no existe
        }
    }

    public function obtenerAvanceCliente($clienteId)
    {
        try {
            // Count videos assigned via plan_cliente -> plan -> programa_virtual -> video
            $sqlTotal = "SELECT COUNT(DISTINCT v.id_video) AS total 
                         FROM video v
                         INNER JOIN programa_virtual pv ON pv.id_programa_virtual = v.id_programa_virtual
                         INNER JOIN plan pl ON pl.id_plan = pv.id_plan
                         INNER JOIN plan_cliente pc ON pc.id_plan = pl.id_plan
                         WHERE pc.id_cliente = :cliente_id AND pc.estado = 'ACTIVO'"; // Total asignados

            $stmtTotal = $this->db->prepare($sqlTotal); // Prepara total
            $stmtTotal->bindParam(':cliente_id', $clienteId); // Cliente
            $stmtTotal->execute(); // Ejecuta total

            $total = (int) ($stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0); // Total videos

            if ($total == 0) { // Valida cero
                return 0; // Sin avance
            }

            $sqlVistos = "SELECT COUNT(*) AS vistos
                          FROM progreso_video
                          WHERE id_cliente = :cliente_id
                          AND estado = 'COMPLETADO'"; // Videos vistos

            $stmtVistos = $this->db->prepare($sqlVistos); // Prepara vistos
            $stmtVistos->bindParam(':cliente_id', $clienteId); // Cliente
            $stmtVistos->execute(); // Ejecuta vistos

            $vistos = (int) ($stmtVistos->fetch(PDO::FETCH_ASSOC)['vistos'] ?? 0); // Total vistos

            return round(($vistos / $total) * 100); // Retorna porcentaje

        } catch (PDOException $e) {
            // Las tablas 'video' o 'programa_virtual' aún no existen en la BD
            return 0; // Sin avance hasta que el módulo esté implementado
        }
    }

    public function marcarVisto($clienteId, $videoId)
    {
        try {
            $existe = $this->obtenerRegistro($clienteId, $videoId); // Busca registro

            if ($existe) { // Si existe
                return $this->actualizarEstado($clienteId, $videoId, 'COMPLETADO'); // Actualiza
            }

            $sql = "INSERT INTO progreso_video 
                    (id_cliente, id_video, estado, porcentaje_avance, fecha_inicio, ultimo_acceso)
                    VALUES 
                    (:id_cliente, :id_video, 'COMPLETADO', 100, NOW(), NOW())"; // Registra visto

            $stmt = $this->db->prepare($sql); // Prepara consulta
            $stmt->bindParam(':id_cliente', $clienteId); // Cliente
            $stmt->bindParam(':id_video', $videoId); // Video

            return $stmt->execute(); // Ejecuta registro
        } catch (PDOException $e) {
            return false; // Tabla aún no existe
        }
    }

    private function obtenerRegistro($clienteId, $videoId)
    {
        try {
            $sql = "SELECT * FROM progreso_video 
                    WHERE id_cliente = :cliente_id AND id_video = :video_id
                    LIMIT 1"; // Busca registro

            $stmt = $this->db->prepare($sql); // Prepara consulta
            $stmt->bindParam(':cliente_id', $clienteId); // Cliente
            $stmt->bindParam(':video_id', $videoId); // Video
            $stmt->execute(); // Ejecuta consulta

            return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna registro
        } catch (PDOException $e) {
            return null; // Tabla aún no existe
        }
    }

    public function actualizarEstado($clienteId, $videoId, $estado)
    {
        try {
            $avance = $estado === 'COMPLETADO' ? 100 : 0;
            $sql = "UPDATE progreso_video 
                    SET estado = :estado, porcentaje_avance = :porcentaje_avance, ultimo_acceso = NOW(),
                        fecha_finalizacion = CASE WHEN :estado = 'COMPLETADO' THEN NOW() ELSE fecha_finalizacion END
                    WHERE id_cliente = :cliente_id AND id_video = :video_id"; // Actualiza progreso

            $stmt = $this->db->prepare($sql); // Prepara consulta
            $stmt->bindParam(':estado', $estado); // Estado
            $stmt->bindParam(':porcentaje_avance', $avance); // Porcentaje avance
            $stmt->bindParam(':cliente_id', $clienteId); // Cliente
            $stmt->bindParam(':video_id', $videoId); // Video

            return $stmt->execute(); // Ejecuta actualización
        } catch (PDOException $e) {
            return false; // Tabla aún no existe
        }
    }

    public function guardarObservacion($datos)
    {
        try {
            $sql = "INSERT INTO progreso_video 
                    (id_cliente, id_video, estado, porcentaje_avance, ultimo_acceso)
                    VALUES
                    (:id_cliente, :id_video, 'EN_PROGRESO', 0, NOW())"; // Guarda observación

            $stmt = $this->db->prepare($sql); // Prepara consulta
            $stmt->bindParam(':id_cliente', $datos['id_cliente']); // Cliente
            $stmt->bindValue(':id_video', $datos['id_video'] ?? null); // Video opcional

            return $stmt->execute(); // Ejecuta registro
        } catch (PDOException $e) {
            return false; // Tabla aún no existe
        }
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Progreso videos', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>