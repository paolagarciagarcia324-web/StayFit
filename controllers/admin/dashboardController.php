<?php

require_once __DIR__ . '/../../config/database.php'; // Importa la conexión
require_once __DIR__ . '/../../config/roles.php'; // Helpers de roles

class DashboardController
{
    private $db; // Variable para la conexión

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $database = new Database(); // Crea instancia de la base de datos
        $this->db = $database->conectar(); // Guarda la conexión
    }

    public function index()
    {
        $this->validarAdministrador(); // Valida acceso del administrador

        $datos = [
            'clientesActivos' => $this->contarClientesActivos(), // Total de clientes activos
            'solicitudesPendientes' => $this->contarSolicitudesPendientes(), // Solicitudes sin aprobar
            'pagosPendientes' => $this->contarPagosPendientes(), // Pagos por validar
            'planesVirtuales' => $this->contarPlanesVirtuales(), // Clientes con modalidad virtual
            'accesosVencidos' => $this->contarAccesosVencidos() // Clientes con acceso vencido
        ];

        require_once __DIR__ . '/../../views/admin/dashboard.php'; // Carga la vista
    }

    private function validarAdministrador()
    {
        validarAccesoAdministrador(); // Valida sesión admin
    }

    private function ejecutarConteo($sql)
    {
        $stmt = $this->db->prepare($sql); // Prepara la consulta
        $stmt->execute(); // Ejecuta la consulta

        return (int) $stmt->fetchColumn(); // Retorna el total
    }

    private function contarClientesActivos()
    {
        $sql = "SELECT COUNT(*)
                FROM cliente c
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                WHERE u.estado = 'ACTIVO'"; // Clientes con usuario activo

        return $this->ejecutarConteo($sql); // Retorna total
    }

    private function contarSolicitudesPendientes()
    {
        try { // Tabla opcional (puede no existir aún en la BD)
            $sql = "SELECT COUNT(*) FROM solicitud_ingreso WHERE estado = 'PENDIENTE'";

            return $this->ejecutarConteo($sql);
        } catch (PDOException $e) {
            return 0; // Sin tabla de solicitudes
        }
    }

    private function contarPagosPendientes()
    {
        $sql = "SELECT COUNT(*) FROM pago WHERE estado_pago = 'PENDIENTE'"; // Pagos por validar

        return $this->ejecutarConteo($sql); // Retorna total
    }

    private function contarPlanesVirtuales()
    {
        $sql = "SELECT COUNT(*) FROM plan_cliente WHERE estado = 'ACTIVO'"; // Planes activos asignados

        return $this->ejecutarConteo($sql); // Retorna total
    }

    private function contarAccesosVencidos()
    {
        $sql = "SELECT COUNT(*) FROM plan_cliente WHERE estado = 'VENCIDO'"; // Planes vencidos

        return $this->ejecutarConteo($sql); // Retorna total
    }
}

$controller = new DashboardController(); // Crea el controlador
$controller->index(); // Ejecuta el dashboard

?>