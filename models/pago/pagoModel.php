<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class PagoModel
{
    private $db;
    private SchemaHelper $schema;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
        $this->schema = new SchemaHelper($this->db);
    }

    private function usaEsquemaNuevo(): bool
    {
        return $this->schema->usaEsquemaNuevo();
    }

    private function mapearMetodoPago(?string $tipo): string
    {
        $mapa = [
            'nequi' => 'NEQUI',
            'daviplata' => 'DAVIPLATA',
            'ahorros' => 'TRANSFERENCIA',
            'corriente' => 'TRANSFERENCIA',
            'transferencia' => 'TRANSFERENCIA',
            'tarjeta' => 'TARJETA',
            'efectivo' => 'EFECTIVO',
        ];

        return $mapa[strtolower((string) $tipo)] ?? 'OTRO';
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_pago'] ?? $fila['id'] ?? null;
        $fila['estado'] = strtolower($fila['estado_pago'] ?? $fila['estado'] ?? 'pendiente');
        $fila['fecha'] = $fila['fecha_pago'] ?? $fila['fecha'] ?? '';
        $fila['solicitante'] = $fila['solicitante'] ?? $fila['cliente'] ?? 'Sin nombre';
        $fila['plan'] = $fila['plan'] ?? $fila['plan_interes'] ?? 'Sin plan';
        $fila['url_comprobante'] = $fila['url_comprobante'] ?? $fila['comprobante_url'] ?? null;

        return $fila;
    }

    private function sqlBaseListado()
    {
        if ($this->usaEsquemaNuevo()) {
            return "SELECT p.*,
                    COALESCE(
                        CONCAT(s.nombres, ' ', s.apellidos),
                        CONCAT(u.nombres, ' ', IFNULL(u.apellidos, '')),
                        'Sin nombre'
                    ) AS solicitante,
                    COALESCE(pl.nombre, 'Sin plan') AS plan
                    FROM pagos p
                    LEFT JOIN solicitudes_compra s ON s.id_solicitud = p.id_solicitud
                    LEFT JOIN planes pl ON pl.id_plan = COALESCE(p.id_plan, s.id_plan)
                    LEFT JOIN user u ON u.id_user = p.id_cliente";
        }

        return "SELECT p.*,
                COALESCE(s.nombre_completo, CONCAT(u.nombre, ' ', IFNULL(u.apellido, ''))) AS solicitante,
                COALESCE(s.plan_interes, pl.nombre, 'Sin plan') AS plan
                FROM pago p
                LEFT JOIN solicitud_ingreso s ON s.id_solicitud = p.id_solicitud
                LEFT JOIN plan_cliente pc ON pc.id_plan_cliente = p.id_plan_cliente
                LEFT JOIN plan pl ON pl.id_plan = pc.id_plan
                LEFT JOIN users u ON u.id_usuario = pc.id_cliente";
    }

    public function obtenerTodos()
    {
        $sql = $this->sqlBaseListado() . ' ORDER BY p.id_pago DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPendientes()
    {
        $sql = $this->sqlBaseListado() . " WHERE p.estado_pago = 'PENDIENTE' ORDER BY p.id_pago DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPorId($id)
    {
        $sql = $this->sqlBaseListado() . ' WHERE p.id_pago = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function obtenerPorSolicitud($solicitudId)
    {
        $tabla = $this->usaEsquemaNuevo() ? 'pagos' : 'pago';
        $sql = "SELECT * FROM {$tabla} WHERE id_solicitud = :solicitud_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':solicitud_id', $solicitudId);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function obtenerPorCliente($clienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT p.*, pl.nombre AS plan
                    FROM pagos p
                    LEFT JOIN planes pl ON pl.id_plan = p.id_plan
                    WHERE p.id_cliente = :cliente_id
                    ORDER BY p.id_pago DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId);
            $stmt->execute();

            $lista = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $lista[] = $this->normalizarFila($fila);
            }

            return $lista;
        }

        $sql = "SELECT p.*, pl.nombre AS plan
                FROM pago p
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = p.id_plan_cliente
                LEFT JOIN plan pl ON pl.id_plan = pc.id_plan
                WHERE pc.id_cliente = :cliente_id
                ORDER BY p.id_pago DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function crearDesdeSolicitud($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "INSERT INTO pagos (id_solicitud, id_plan, monto, metodo_pago, referencia_pago, estado_pago, comprobante_url, fecha_pago, creado_en)
                    VALUES (:id_solicitud, :id_plan, :monto, :metodo_pago, :referencia_pago, 'PENDIENTE', :url_comprobante, NOW(), NOW())";
        } else {
            $sql = "INSERT INTO pago (id_solicitud, id_plan_cliente, monto, estado_pago, url_comprobante, fecha_pago)
                    VALUES (:id_solicitud, NULL, :monto, 'PENDIENTE', :url_comprobante, NOW())";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_solicitud', $datos['solicitud_id']);
        $stmt->bindValue(':monto', $datos['monto'] ?? 0);
        $stmt->bindValue(':url_comprobante', $datos['url_comprobante'] ?? null);

        if ($this->usaEsquemaNuevo()) {
            $planId = (int) ($datos['plan_id'] ?? 0);
            $stmt->bindValue(':id_plan', $planId, PDO::PARAM_INT);
            $stmt->bindValue(':metodo_pago', $this->mapearMetodoPago($datos['metodo_pago'] ?? null));
            $stmt->bindValue(':referencia_pago', $datos['referencia_pago'] ?? $datos['numero_cuenta'] ?? null);
        }

        $stmt->execute();

        return $this->db->lastInsertId();
    }

    public function aprobar($id, $usuarioId = null)
    {
        return $this->cambiarEstado($id, 'PAGADO');
    }

    public function aprobarPorSolicitud($solicitudId, $usuarioId = null)
    {
        $pago = $this->obtenerPorSolicitud($solicitudId);
        if (!$pago || empty($pago['id'])) {
            return false;
        }

        return $this->aprobar($pago['id'], $usuarioId);
    }

    public function vincularPlanClientePorSolicitud($solicitudId, $planClienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $pago = $this->obtenerPorSolicitud($solicitudId);
            if (!$pago || empty($pago['id'])) {
                return false;
            }

            $sqlPlanCliente = 'UPDATE planes_cliente SET id_pago = :pago_id WHERE id_plan_cliente = :plan_cliente_id';
            $stmtPlan = $this->db->prepare($sqlPlanCliente);
            $stmtPlan->bindValue(':pago_id', (int) $pago['id'], PDO::PARAM_INT);
            $stmtPlan->bindValue(':plan_cliente_id', (int) $planClienteId, PDO::PARAM_INT);
            $stmtPlan->execute();

            $sqlCliente = 'SELECT id_cliente FROM planes_cliente WHERE id_plan_cliente = :plan_cliente_id LIMIT 1';
            $stmtCliente = $this->db->prepare($sqlCliente);
            $stmtCliente->bindValue(':plan_cliente_id', (int) $planClienteId, PDO::PARAM_INT);
            $stmtCliente->execute();
            $clienteId = $stmtCliente->fetchColumn();

            if ($clienteId) {
                $sqlPago = 'UPDATE pagos SET id_cliente = :cliente_id WHERE id_pago = :pago_id';
                $stmtPago = $this->db->prepare($sqlPago);
                $stmtPago->bindValue(':cliente_id', (int) $clienteId, PDO::PARAM_INT);
                $stmtPago->bindValue(':pago_id', (int) $pago['id'], PDO::PARAM_INT);
                $stmtPago->execute();
            }

            return true;
        }

        $sql = 'UPDATE pago SET id_plan_cliente = :plan_cliente_id WHERE id_solicitud = :solicitud_id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':plan_cliente_id', $planClienteId);
        $stmt->bindParam(':solicitud_id', $solicitudId);

        return $stmt->execute();
    }

    public function rechazarPorSolicitud($datos)
    {
        $pago = $this->obtenerPorSolicitud($datos['solicitud_id'] ?? 0);
        if (!$pago || empty($pago['id'])) {
            return false;
        }

        $datos['id'] = $pago['id'];
        return $this->rechazar($datos);
    }

    public function rechazar($datos)
    {
        $tabla = $this->usaEsquemaNuevo() ? 'pagos' : 'pago';
        $campoObs = $this->usaEsquemaNuevo() ? 'observacion_admin' : 'observacion';

        $estadoPago = $this->usaEsquemaNuevo() ? 'RECHAZADO' : 'FALLIDO';

        $sql = "UPDATE {$tabla}
                SET estado_pago = '{$estadoPago}', {$campoObs} = :observacion
                WHERE id_pago = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':observacion', $datos['observacion'] ?? 'Pago rechazado');
        $stmt->bindParam(':id', $datos['id']);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        $mapa = [
            'aprobado' => 'PAGADO',
            'pendiente' => 'PENDIENTE',
            'rechazado' => 'FALLIDO',
            'pagado' => 'PAGADO',
            'validado' => 'VALIDADO',
        ];

        $estadoBd = $mapa[strtolower($estado)] ?? strtoupper($estado);

        if ($this->usaEsquemaNuevo()) {
            $mapaNuevo = [
                'PAGADO' => 'VALIDADO',
                'APROBADO' => 'VALIDADO',
                'FALLIDO' => 'RECHAZADO',
                'RECHAZADO' => 'RECHAZADO',
            ];
            $estadoBd = $mapaNuevo[$estadoBd] ?? $estadoBd;
        }

        $tabla = $this->usaEsquemaNuevo() ? 'pagos' : 'pago';

        $sql = "UPDATE {$tabla} SET estado_pago = :estado, fecha_pago = NOW() WHERE id_pago = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estadoBd);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function reporteGeneral()
    {
        $tabla = $this->usaEsquemaNuevo() ? 'pagos' : 'pago';
        $sql = "SELECT estado_pago AS estado, COUNT(*) AS total FROM {$tabla} GROUP BY estado_pago";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Pagos', $accion);
    }
}

?>
