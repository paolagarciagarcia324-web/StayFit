<?php

require_once __DIR__ . '/../../config/database.php'; // Importa la conexión

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
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { // Verifica el rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige si no tiene permiso
            exit; // Detiene la ejecución
        }
    }

    private function contarClientesActivos()
    {
        $sql = "SELECT COUNT(*) FROM clientes WHERE estado = 'activo'"; // Consulta clientes activos
        $stmt = $this->db->prepare($sql); // Prepara la consulta
        $stmt->execute(); // Ejecuta la consulta

        return $stmt->fetchColumn(); // Retorna el total
    }

    private function contarSolicitudesPendientes()
    {
        $sql = "SELECT COUNT(*) FROM solicitudes_ingreso WHERE estado = 'pendiente'"; // Consulta solicitudes pendientes
        $stmt = $this->db->prepare($sql); // Prepara la consulta
        $stmt->execute(); // Ejecuta la consulta

        return $stmt->fetchColumn(); // Retorna el total
    }

    private function contarPagosPendientes()
    {
        $sql = "SELECT COUNT(*) FROM pagos WHERE estado = 'pendiente'"; // Consulta pagos pendientes
        $stmt = $this->db->prepare($sql); // Prepara la consulta
        $stmt->execute(); // Ejecuta la consulta

        return $stmt->fetchColumn(); // Retorna el total
    }

    private function contarPlanesVirtuales()
    {
        $sql = "SELECT COUNT(*) FROM planes_clientes WHERE modalidad = 'virtual' AND estado = 'activo'"; // Consulta planes virtuales activos
        $stmt = $this->db->prepare($sql); // Prepara la consulta
        $stmt->execute(); // Ejecuta la consulta

        return $stmt->fetchColumn(); // Retorna el total
    }

    private function contarAccesosVencidos()
    {
        $sql = "SELECT COUNT(*) FROM accesos WHERE estado = 'vencido'"; // Consulta accesos vencidos
        $stmt = $this->db->prepare($sql); // Prepara la consulta
        $stmt->execute(); // Ejecuta la consulta

        return $stmt->fetchColumn(); // Retorna el total
    }
}

$controller = new DashboardController(); // Crea el controlador
$controller->index(); // Ejecuta el dashboard

?>