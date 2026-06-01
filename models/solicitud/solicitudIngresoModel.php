<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class SolicitudIngresoModel
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

        $fila['id'] = $fila['id_solicitud'] ?? $fila['id'] ?? null;
        $fila['nombre'] = $fila['nombre_completo'] ?? $fila['nombre'] ?? '';
        $fila['estado'] = strtolower($fila['estado'] ?? 'pendiente');
        $url = trim((string) ($fila['url_comprobante'] ?? ''));

        if ($url === '') {
            $url = trim((string) ($fila['comprobante_pago'] ?? ''));
        }

        $fila['url_comprobante'] = $url !== '' ? $url : null;

        return $fila;
    }

    private function normalizarLista($filas)
    {
        $resultado = []; // Lista final

        foreach ($filas as $fila) { // Recorre filas
            $resultado[] = $this->normalizarFila($fila); // Normaliza cada una
        }

        return $resultado; // Retorna lista
    }

    public function obtenerTodas()
    {
        $sql = "SELECT s.*, p.url_comprobante AS comprobante_pago, p.id_pago, p.monto AS monto_pago
                FROM solicitud_ingreso s
                LEFT JOIN pago p ON p.id_solicitud = s.id_solicitud
                ORDER BY s.id_solicitud DESC";
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC)); // Retorna lista
    }

    public function obtenerTodos()
    {
        return $this->obtenerTodas(); // Alias para controladores
    }

    public function obtenerPendientes()
    {
        return $this->obtenerPorEstado('pendiente'); // Solicitudes pendientes
    }

    public function obtenerPorEstado($estado)
    {
        $estado = strtoupper($estado); // Estado en mayúsculas (ENUM BD)

        $sql = "SELECT s.*, p.url_comprobante AS comprobante_pago, p.id_pago
                FROM solicitud_ingreso s
                LEFT JOIN pago p ON p.id_solicitud = s.id_solicitud
                WHERE s.estado = :estado
                ORDER BY s.id_solicitud DESC";

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Estado
        $stmt->execute(); // Ejecuta consulta

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC)); // Retorna lista
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT s.*, p.url_comprobante AS comprobante_pago, p.id_pago, p.monto AS monto_pago, p.estado_pago
                FROM solicitud_ingreso s
                LEFT JOIN pago p ON p.id_solicitud = s.id_solicitud
                WHERE s.id_solicitud = :id LIMIT 1";
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID solicitud
        $stmt->execute(); // Ejecuta consulta

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC)); // Retorna solicitud
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO solicitud_ingreso 
                (nombre_completo, edad, identificacion, celular, plan_interes, modalidad,
                 tipo_cuenta, numero_cuenta, url_comprobante, estado)
                VALUES
                (:nombre, :edad, :identificacion, :celular, :plan_interes, :modalidad,
                 :tipo_cuenta, :numero_cuenta, :url_comprobante, :estado)"; // Crea solicitud

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':edad', $datos['edad']); // Edad
        $stmt->bindParam(':identificacion', $datos['identificacion']); // Documento
        $stmt->bindParam(':celular', $datos['celular']); // Celular
        $planInteres = $datos['plan_interes'] ?? null;
        if ($planInteres === null && !empty($datos['plan_id'])) {
            $planInteres = (string) $datos['plan_id'];
        }
        $stmt->bindValue(':plan_interes', $planInteres); // Plan
        $stmt->bindValue(':modalidad', strtoupper($datos['modalidad'] ?? 'VIRTUAL')); // Modalidad
        $stmt->bindValue(':tipo_cuenta', $datos['tipo_cuenta'] ?? ''); // Tipo cuenta
        $stmt->bindValue(':numero_cuenta', $datos['numero_cuenta'] ?? ''); // Número cuenta
        $stmt->bindValue(':url_comprobante', $datos['url_comprobante'] ?? null); // Comprobante
        $stmt->bindValue(':estado', strtoupper($datos['estado'] ?? 'PENDIENTE')); // Estado

        $stmt->execute(); // Ejecuta registro

        return $this->db->lastInsertId(); // Retorna ID creado
    }

    public function actualizarEstado($id, $estado)
    {
        return $this->cambiarEstado($id, $estado); // Alias
    }

    public function cambiarEstado($id, $estado)
    {
        $estado = strtoupper($estado); // Estado ENUM

        $sql = "UPDATE solicitud_ingreso SET estado = :estado WHERE id_solicitud = :id"; // Actualiza
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID solicitud

        return $stmt->execute(); // Ejecuta actualización
    }

    public function marcarRevision($id)
    {
        return $this->cambiarEstado($id, 'EN_REVISION'); // Marca en revisión
    }

    public function aprobar($id, $usuarioId = null)
    {
        return $this->cambiarEstado($id, 'APROBADA'); // Aprueba solicitud
    }

    public function rechazar($datos)
    {
        $sql = "UPDATE solicitud_ingreso 
                SET estado = 'RECHAZADA', observacion_admin = :observacion
                WHERE id_solicitud = :id"; // Rechaza solicitud

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindValue(':observacion', $datos['observacion'] ?? 'Solicitud rechazada'); // Motivo
        $stmt->bindParam(':id', $datos['id']); // ID solicitud

        return $stmt->execute(); // Ejecuta rechazo
    }

    public function contarPendientes()
    {
        $sql = "SELECT COUNT(*) AS total FROM solicitud_ingreso WHERE estado = 'PENDIENTE'"; // Cuenta
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna total
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        $id = $usuarioId ? (int) $usuarioId : null;

        return registrarBitacora($this->db, $id, 'Solicitudes de ingreso', $accion);
    }
}

?>
