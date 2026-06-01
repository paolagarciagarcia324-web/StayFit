<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class PagoModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    private function normalizarFila($fila)
    {
        if (!$fila) { // Sin datos
            return false; // Retorna falso
        }

        $fila['id'] = $fila['id_pago'] ?? $fila['id'] ?? null; // ID para vistas
        $fila['estado'] = strtolower($fila['estado_pago'] ?? $fila['estado'] ?? 'pendiente'); // Estado
        $fila['fecha'] = $fila['fecha_pago'] ?? $fila['fecha'] ?? ''; // Fecha
        $fila['solicitante'] = $fila['solicitante'] ?? $fila['cliente'] ?? 'Sin nombre'; // Solicitante
        $fila['plan'] = $fila['plan'] ?? $fila['plan_interes'] ?? 'Sin plan'; // Plan

        return $fila; // Fila normalizada
    }

    private function sqlBaseListado()
    {
        return "SELECT p.*,
                COALESCE(s.nombre_completo, CONCAT(u.nombre, ' ', IFNULL(u.apellido, ''))) AS solicitante,
                COALESCE(s.plan_interes, pl.nombre, 'Sin plan') AS plan
                FROM pago p
                LEFT JOIN solicitud_ingreso s ON s.id_solicitud = p.id_solicitud
                LEFT JOIN plan_cliente pc ON pc.id_plan_cliente = p.id_plan_cliente
                LEFT JOIN plan pl ON pl.id_plan = pc.id_plan
                LEFT JOIN users u ON u.id_usuario = pc.id_cliente"; // Join solicitud o cliente
    }

    public function obtenerTodos()
    {
        $sql = $this->sqlBaseListado() . " ORDER BY p.id_pago DESC"; // Todos los pagos
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene filas
        $lista = []; // Lista normalizada

        foreach ($filas as $fila) { // Recorre
            $lista[] = $this->normalizarFila($fila); // Normaliza
        }

        return $lista; // Retorna lista
    }

    public function obtenerPendientes()
    {
        $sql = $this->sqlBaseListado() . " WHERE p.estado_pago = 'PENDIENTE' ORDER BY p.id_pago DESC"; // Pendientes
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene filas
        $lista = []; // Lista normalizada

        foreach ($filas as $fila) { // Recorre
            $lista[] = $this->normalizarFila($fila); // Normaliza
        }

        return $lista; // Retorna pendientes
    }

    public function obtenerPorId($id)
    {
        $sql = $this->sqlBaseListado() . " WHERE p.id_pago = :id LIMIT 1"; // Busca pago
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID pago
        $stmt->execute(); // Ejecuta consulta

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC)); // Retorna pago
    }

    public function obtenerPorSolicitud($solicitudId)
    {
        $sql = "SELECT * FROM pago WHERE id_solicitud = :solicitud_id LIMIT 1"; // Pago de solicitud
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':solicitud_id', $solicitudId); // Solicitud
        $stmt->execute(); // Ejecuta consulta

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC)); // Retorna pago
    }

    public function crearDesdeSolicitud($datos)
    {
        $sql = "INSERT INTO pago (id_solicitud, id_plan_cliente, monto, estado_pago, url_comprobante, fecha_pago)
                VALUES (:id_solicitud, NULL, :monto, 'PENDIENTE', :url_comprobante, NOW())"; // Crea pago

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_solicitud', $datos['solicitud_id']); // Solicitud
        $stmt->bindValue(':monto', $datos['monto'] ?? 0); // Monto
        $stmt->bindValue(':url_comprobante', $datos['url_comprobante'] ?? null); // Comprobante

        $stmt->execute(); // Ejecuta registro

        return $this->db->lastInsertId(); // Retorna ID pago
    }

    public function aprobar($id, $usuarioId = null)
    {
        return $this->cambiarEstado($id, 'PAGADO'); // Aprueba pago
    }

    public function aprobarPorSolicitud($solicitudId, $usuarioId = null)
    {
        $pago = $this->obtenerPorSolicitud($solicitudId); // Busca pago

        if (!$pago || empty($pago['id'])) { // Sin pago
            return false; // No aplica
        }

        return $this->aprobar($pago['id'], $usuarioId); // Aprueba por ID
    }

    public function vincularPlanClientePorSolicitud($solicitudId, $planClienteId)
    {
        $sql = "UPDATE pago SET id_plan_cliente = :plan_cliente_id WHERE id_solicitud = :solicitud_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':plan_cliente_id', $planClienteId);
        $stmt->bindParam(':solicitud_id', $solicitudId);

        return $stmt->execute();
    }

    public function rechazarPorSolicitud($datos)
    {
        $pago = $this->obtenerPorSolicitud($datos['solicitud_id'] ?? 0); // Busca pago

        if (!$pago || empty($pago['id'])) { // Sin pago
            return false; // No aplica
        }

        $datos['id'] = $pago['id']; // ID del pago

        return $this->rechazar($datos); // Rechaza por ID
    }

    public function rechazar($datos)
    {
        $sql = "UPDATE pago 
                SET estado_pago = 'FALLIDO', observacion = :observacion
                WHERE id_pago = :id"; // Rechaza pago

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindValue(':observacion', $datos['observacion'] ?? 'Pago rechazado'); // Motivo
        $stmt->bindParam(':id', $datos['id']); // ID pago

        return $stmt->execute(); // Ejecuta rechazo
    }

    public function cambiarEstado($id, $estado)
    {
        $mapa = [
            'aprobado' => 'PAGADO',
            'pendiente' => 'PENDIENTE',
            'rechazado' => 'FALLIDO',
            'pagado' => 'PAGADO',
        ];

        $estadoBd = $mapa[strtolower($estado)] ?? strtoupper($estado); // Estado ENUM

        $sql = "UPDATE pago SET estado_pago = :estado, fecha_pago = NOW() WHERE id_pago = :id"; // Actualiza
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estadoBd); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID pago

        return $stmt->execute(); // Ejecuta cambio
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Pagos', :accion, NOW())"; // Historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindValue(':usuario_id', $usuarioId); // Usuario
        $stmt->bindParam(':accion', $accion); // Acción

        return $stmt->execute(); // Ejecuta registro
    }
}

?>
