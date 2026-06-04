<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/roles.php';

class DashboardController
{
    private $db;

    public function __construct()
    {
        session_start();

        $database = new Database();
        $this->db = $database->conectar();
    }

    public function index()
    {
        $this->validarAdministrador();

        $datos = [
            'clientesActivos' => $this->contarClientesActivos(),
            'solicitudesPendientes' => $this->contarSolicitudesPendientes(),
            'pagosPendientes' => $this->contarPagosPendientes(),
            'planesVirtuales' => $this->contarPlanesVirtuales(),
            'accesosVencidos' => $this->contarAccesosVencidos()
        ];

        require_once __DIR__ . '/../../views/admin/dashboard.php';
    }

    private function validarAdministrador()
    {
        validarAccesoAdministrador();
    }

    private function ejecutarConteo($sql)
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    private function tablaExiste($tabla)
    {
        $stmt = $this->db->query('SHOW TABLES LIKE ' . $this->db->quote($tabla));

        return (bool) $stmt->fetch();
    }

    private function contarClientesActivos()
    {
        if ($this->tablaExiste('clientes')) {
            $sql = "SELECT COUNT(*)
                    FROM clientes c
                    INNER JOIN user u ON u.id_user = c.id_user
                    WHERE c.estado_cliente = 'ACTIVO' AND u.estado = 'ACTIVO'";

            return $this->ejecutarConteo($sql);
        }

        $sql = "SELECT COUNT(*)
                FROM cliente c
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                WHERE u.estado = 'ACTIVO'";

        return $this->ejecutarConteo($sql);
    }

    private function contarSolicitudesPendientes()
    {
        if ($this->tablaExiste('solicitudes_compra')) {
            $sql = "SELECT COUNT(*)
                    FROM solicitudes_compra
                    WHERE estado_solicitud IN ('PENDIENTE', 'EN_REVISION')";

            return $this->ejecutarConteo($sql);
        }

        $sql = "SELECT COUNT(*) FROM solicitud_ingreso WHERE estado = 'PENDIENTE'";

        return $this->ejecutarConteo($sql);
    }

    private function contarPagosPendientes()
    {
        if ($this->tablaExiste('pagos')) {
            $sql = "SELECT COUNT(*) FROM pagos WHERE estado_pago = 'PENDIENTE'";

            return $this->ejecutarConteo($sql);
        }

        $sql = "SELECT COUNT(*) FROM pago WHERE estado_pago = 'PENDIENTE'";

        return $this->ejecutarConteo($sql);
    }

    private function contarPlanesVirtuales()
    {
        if ($this->tablaExiste('planes_cliente')) {
            $sql = "SELECT COUNT(*)
                    FROM planes_cliente pc
                    INNER JOIN planes p ON p.id_plan = pc.id_plan
                    WHERE pc.estado_plan_cliente = 'ACTIVO' AND p.modalidad = 'VIRTUAL'";

            return $this->ejecutarConteo($sql);
        }

        $sql = "SELECT COUNT(*) FROM plan_cliente WHERE estado = 'ACTIVO'";

        return $this->ejecutarConteo($sql);
    }

    private function contarAccesosVencidos()
    {
        if ($this->tablaExiste('planes_cliente')) {
            $sql = "SELECT COUNT(*)
                    FROM planes_cliente
                    WHERE estado_plan_cliente = 'VENCIDO'
                       OR (fecha_fin IS NOT NULL AND fecha_fin < CURDATE())";

            return $this->ejecutarConteo($sql);
        }

        $sql = "SELECT COUNT(*) FROM plan_cliente WHERE estado = 'VENCIDO'";

        return $this->ejecutarConteo($sql);
    }
}

$controller = new DashboardController();
$controller->index();

?>
