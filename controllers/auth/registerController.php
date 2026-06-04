<?php

session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../models/usuario/usuarioModel.php';
require_once __DIR__ . '/../../models/cliente/clienteModel.php';

class RegisterController
{
    private PDO $db;
    private UsuarioModel $usuarioModel;
    private ClienteModel $clienteModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
        $this->usuarioModel = new UsuarioModel($this->db);
        $this->clienteModel = new ClienteModel($this->db);
    }

    public function registrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../views/auth/register.php');
            exit;
        }

        $nombreCompleto = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $documento = trim($_POST['documento_identidad'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirmarPassword = (string) ($_POST['password_confirm'] ?? '');
        $rol = 'cliente';

        if ($nombreCompleto === '' || $correo === '' || $password === '' || $confirmarPassword === '') {
            $this->alerta('warning', 'Campos incompletos', 'Completa nombre, correo y contraseña.');
            header('Location: ../../views/auth/register.php');
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->alerta('error', 'Correo inválido', 'Ingresa un correo electrónico válido.');
            header('Location: ../../views/auth/register.php');
            exit;
        }

        if (strlen($password) < 6) {
            $this->alerta('warning', 'Contraseña corta', 'La contraseña debe tener al menos 6 caracteres.');
            header('Location: ../../views/auth/register.php');
            exit;
        }

        if ($password !== $confirmarPassword) {
            $this->alerta('error', 'Contraseñas diferentes', 'Confirma la misma contraseña para continuar.');
            header('Location: ../../views/auth/register.php');
            exit;
        }

        if ($this->usuarioModel->obtenerPorCorreo($correo)) {
            $this->alerta('error', 'Correo registrado', 'Ya existe una cuenta con este correo.');
            header('Location: ../../views/auth/register.php');
            exit;
        }

        if ($documento !== '' && $this->usuarioModel->obtenerPorDocumentoIdentidad($documento)) {
            $this->alerta('error', 'Documento registrado', 'Ya existe una cuenta con este documento.');
            header('Location: ../../views/auth/register.php');
            exit;
        }

        $rolDb = $this->obtenerRolCliente();

        if (!$rolDb) {
            $this->alerta('error', 'Rol no disponible', 'No se encontró el rol Cliente en la base de datos.');
            header('Location: ../../views/auth/register.php');
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
                'origen_registro' => 'SELF_SERVICE',
                'telefono' => $telefono !== '' ? $telefono : null,
                'documento_identidad' => $documento !== '' ? $documento : null,
            ]);

            $this->usuarioModel->asignarRol($usuarioId, (int) $rolDb['id_rol']);
            $this->clienteModel->crearClienteFijo([
                'id_cliente' => $usuarioId,
                'tipo_cliente' => 'INDIVIDUAL',
            ]);

            $this->db->commit();

            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuarioId;
            $_SESSION['nombre'] = $partes['nombre'];
            $_SESSION['correo'] = $correo;
            $_SESSION['rol'] = normalizarRol($rol);

            header('Location: ../cliente/dashboardController.php');
            exit;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $this->alerta('error', 'Registro no completado', 'No se pudo crear la cuenta. Revisa los datos e intenta nuevamente.');
            header('Location: ../../views/auth/register.php');
            exit;
        }
    }

    private function obtenerRolCliente(): ?array
    {
        $usaTablaVieja = (bool) $this->db->query("SHOW TABLES LIKE 'rol'")->fetch();

        if ($usaTablaVieja) {
            $stmt = $this->db->prepare("SELECT id_rol, nombre FROM rol WHERE nombre = 'Cliente' AND activo = 1 LIMIT 1");
            $stmt->execute();
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);

            return $rol ?: null;
        }

        $stmt = $this->db->prepare("SELECT id_rol, nombre FROM roles WHERE (codigo = 'cliente' OR nombre = 'Cliente') AND estado = 'ACTIVO' LIMIT 1");
        $stmt->execute();
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);

        return $rol ?: null;
    }

    private function alerta(string $icono, string $titulo, string $texto): void
    {
        $_SESSION['alert'] = [
            'icon' => $icono,
            'title' => $titulo,
            'text' => $texto
        ];
    }
}

$controller = new RegisterController();
$controller->registrar();

?>
