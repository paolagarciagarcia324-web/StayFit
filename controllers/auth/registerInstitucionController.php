<?php

session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../models/usuario/usuarioModel.php';
require_once __DIR__ . '/../../models/cliente/clienteModel.php';
require_once __DIR__ . '/../../models/plan/planModel.php';
require_once __DIR__ . '/../../models/plan/accesoModel.php';
require_once __DIR__ . '/../../models/institucion/enlaceInstitucionalModel.php';

class RegisterInstitucionController
{
    private PDO $db;
    private UsuarioModel $usuarioModel;
    private ClienteModel $clienteModel;
    private PlanModel $planModel;
    private AccesoModel $accesoModel;
    private EnlaceInstitucionalModel $enlaceModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
        $this->usuarioModel = new UsuarioModel($this->db);
        $this->clienteModel = new ClienteModel($this->db);
        $this->planModel = new PlanModel();
        $this->accesoModel = new AccesoModel();
        $this->enlaceModel = new EnlaceInstitucionalModel($this->db);
    }

    public function registrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . urlRegistroInstitucionForm());
            exit;
        }

        $token = trim($_POST['token'] ?? '');
        $enlace = $token !== '' ? $this->enlaceModel->obtenerPorToken($token) : null;

        if (!$enlace) {
            $this->alerta('error', 'Enlace no válido', 'Este enlace de registro no está disponible.');
            header('Location: ' . urlRegistroInstitucionForm($token));
            exit;
        }

        $nombreCompleto = trim($_POST['nombre_completo'] ?? $_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $documento = trim($_POST['documento_identidad'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirmarPassword = (string) ($_POST['password_confirm'] ?? '');

        $idInstitucion = (int) ($enlace['id_institucion'] ?? 0);
        $idPlan = (int) ($enlace['id_plan'] ?? 0);
        $idEnlace = (int) ($enlace['id_enlace'] ?? $enlace['id'] ?? 0);

        if ($nombreCompleto === '' || $correo === '' || $password === '' || $confirmarPassword === '') {
            $this->guardarOld($token, $_POST);
            $this->alerta('warning', 'Campos incompletos', 'Completa nombre, correo y contraseña.');
            header('Location: ' . urlRegistroInstitucionForm($token));
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->guardarOld($token, $_POST);
            $this->alerta('error', 'Correo inválido', 'Ingresa un correo electrónico válido.');
            header('Location: ' . urlRegistroInstitucionForm($token));
            exit;
        }

        if (strlen($password) < 6) {
            $this->guardarOld($token, $_POST);
            $this->alerta('warning', 'Contraseña corta', 'La contraseña debe tener al menos 6 caracteres.');
            header('Location: ' . urlRegistroInstitucionForm($token));
            exit;
        }

        if ($password !== $confirmarPassword) {
            $this->guardarOld($token, $_POST);
            $this->alerta('error', 'Contraseñas diferentes', 'Confirma la misma contraseña para continuar.');
            header('Location: ' . urlRegistroInstitucionForm($token));
            exit;
        }

        if ($this->usuarioModel->obtenerPorCorreo($correo)) {
            $this->guardarOld($token, $_POST);
            $this->alerta('error', 'Correo registrado', 'Ya existe una cuenta con este correo.');
            header('Location: ' . urlRegistroInstitucionForm($token));
            exit;
        }

        if ($documento !== '' && $this->usuarioModel->obtenerPorDocumentoIdentidad($documento)) {
            $this->guardarOld($token, $_POST);
            $this->alerta('error', 'Documento registrado', 'Ya existe una cuenta con este documento.');
            header('Location: ' . urlRegistroInstitucionForm($token));
            exit;
        }

        $rolDb = $this->obtenerRolInstitucional();

        if (!$rolDb) {
            $this->alerta('error', 'Rol no disponible', 'No se encontró el rol Cliente Institucional en la base de datos.');
            header('Location: ' . urlRegistroInstitucionForm($token));
            exit;
        }

        $cupoCheck = $this->planModel->puedeInscribirse($idPlan);
        if (!$cupoCheck['ok']) {
            $this->guardarOld($token, $_POST);
            $this->alerta('error', 'Plan no disponible', $cupoCheck['mensaje']);
            header('Location: ' . urlRegistroInstitucionForm($token));
            exit;
        }

        try {
            $this->db->beginTransaction();

            $partes = dividirNombreCompleto($nombreCompleto);
            $usuarioId = (int) $this->usuarioModel->crear([
                'nombre' => $partes['nombre'],
                'apellido' => $partes['apellido'],
                'correo' => $correo,
                'password' => $password,
                'estado' => 'ACTIVO',
                'origen_registro' => 'INSTITUCION_ENLACE',
                'telefono' => $telefono !== '' ? $telefono : null,
                'documento_identidad' => $documento !== '' ? $documento : null,
            ]);

            $this->usuarioModel->asignarRol($usuarioId, (int) $rolDb['id_rol']);

            $this->clienteModel->crearClienteFijo([
                'id_cliente' => $usuarioId,
                'tipo_cliente' => 'INSTITUCIONAL',
                'id_institucion' => $idInstitucion,
                'objetivos' => $cargo !== '' ? 'Cargo: ' . $cargo : null,
            ]);

            $cliente = $this->clienteModel->obtenerPorUsuario($usuarioId);
            if (!$cliente || empty($cliente['id'])) {
                throw new RuntimeException('No se pudo registrar el cliente institucional.');
            }

            $clienteId = (int) $cliente['id'];
            $planClienteId = $this->clienteModel->crearPlanCliente($clienteId, $idPlan, null);

            if (!$planClienteId) {
                throw new RuntimeException('No se pudo asignar el plan de convenio.');
            }

            $planCatalogo = $this->planModel->obtenerPorId($idPlan);

            $this->db->commit();

            try {
                $this->activarAccesos($clienteId, $planClienteId, $planCatalogo);
            } catch (Throwable $e) {
            }

            $this->enlaceModel->incrementarRegistro($idEnlace);
            $this->enlaceModel->registrarTrazabilidad($usuarioId, 'Registro institucional vía enlace');

            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuarioId;
            $_SESSION['cliente_id'] = $clienteId;
            $_SESSION['nombre'] = trim($partes['nombre'] . ' ' . $partes['apellido']);
            $_SESSION['correo'] = $correo;
            $_SESSION['rol'] = 'cliente_institucional';

            header('Location: ' . urlDashboardClienteInstitucional());
            exit;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $this->guardarOld($token, $_POST);
            $this->alerta('error', 'Registro no completado', 'No se pudo crear la cuenta. Intenta nuevamente.');
            header('Location: ' . urlRegistroInstitucionForm($token));
            exit;
        }
    }

    private function activarAccesos(int $clienteId, int $planClienteId, $planCatalogo): void
    {
        if (!$planCatalogo) {
            return;
        }

        $plan = [
            'id' => $planClienteId,
            'modalidad' => strtolower($planCatalogo['modalidad'] ?? 'virtual'),
            'incluye_entrenamiento' => $planCatalogo['incluye_entrenamiento'] ?? 1,
            'incluye_nutricion' => $planCatalogo['incluye_nutricion'] ?? 0,
            'requiere_coach' => $planCatalogo['requiere_coach'] ?? 0,
            'duracion' => $planCatalogo['duracion_dias'] ?? $planCatalogo['duracion'] ?? 30,
        ];

        $this->accesoModel->crearAccesosPorPlan($clienteId, $plan);
    }

    private function obtenerRolInstitucional(): ?array
    {
        $usaTablaVieja = (bool) $this->db->query("SHOW TABLES LIKE 'rol'")->fetch();

        if ($usaTablaVieja) {
            $stmt = $this->db->prepare("SELECT id_rol, nombre FROM rol WHERE nombre LIKE '%Institucional%' AND activo = 1 LIMIT 1");
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $stmt = $this->db->prepare("SELECT id_rol, codigo, nombre FROM roles
                                    WHERE (codigo = 'CLIENTE_INSTITUCIONAL' OR nombre LIKE '%Institucional%')
                                      AND estado = 'ACTIVO'
                                    LIMIT 1");
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function guardarOld(string $token, array $post): void
    {
        $_SESSION['old_inst'] = [
            'token' => $token,
            'nombre_completo' => $post['nombre_completo'] ?? $post['nombre'] ?? '',
            'correo' => $post['correo'] ?? '',
            'telefono' => $post['telefono'] ?? '',
            'documento_identidad' => $post['documento_identidad'] ?? '',
            'cargo' => $post['cargo'] ?? '',
        ];
    }

    private function alerta(string $icono, string $titulo, string $texto): void
    {
        $_SESSION['alert'] = [
            'icon' => $icono,
            'title' => $titulo,
            'text' => $texto,
        ];
    }
}

$controller = new RegisterInstitucionController();
$controller->registrar();

?>
