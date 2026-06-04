<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../plan/planModel.php';

class ClienteModel
{
    private $db; // Conexión BD

    public function __construct(?PDO $db = null)
    {
        if ($db instanceof PDO) {
            $this->db = $db;
            return;
        }

        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    private function usaEsquemaNuevo()
    {
        static $usaNuevo = null;

        if ($usaNuevo !== null) {
            return $usaNuevo;
        }

        $tablaVieja = $this->db->query("SHOW TABLES LIKE 'cliente'")->fetch();
        $tablaNueva = $this->db->query("SHOW TABLES LIKE 'clientes'")->fetch();

        $usaNuevo = !$tablaVieja && (bool) $tablaNueva;

        return $usaNuevo;
    }

    public function obtenerTodos()
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado, u.telefono,
                       u.documento_identidad AS identificacion,
                       c.tipo_cliente, c.fecha_nacimiento, c.estatura_m, c.peso_inicial, c.objetivos
                FROM cliente c
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                ORDER BY u.id_usuario DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($lista as &$fila) {
            $fila = $this->normalizarFilaCliente($fila);
        }

        return $lista;
    }

    public function obtenerPorId($id)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT c.id_cliente AS id, u.id_user AS id_usuario, u.nombres AS nombre,
                           u.apellidos AS apellido, u.correo, u.estado, u.telefono,
                           u.documento_identidad AS identificacion,
                           'INDIVIDUAL' AS tipo_cliente, c.fecha_nacimiento,
                           c.estatura_cm AS estatura_m, c.peso_inicial_kg AS peso_inicial,
                           c.objetivo_principal AS objetivos, NULL AS restricciones_medicas
                    FROM clientes c
                    INNER JOIN user u ON u.id_user = c.id_user
                    WHERE c.id_cliente = :id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fila ? $this->normalizarFilaCliente($fila) : null;
        }

        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado, u.telefono,
                       u.documento_identidad AS identificacion,
                       c.tipo_cliente, c.fecha_nacimiento, c.estatura_m, c.peso_inicial,
                       c.objetivos, c.restricciones_medicas
                FROM cliente c
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                WHERE c.id_cliente = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->normalizarFilaCliente($fila) : null;
    }

    public function obtenerPorUsuario($usuarioId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT c.id_cliente AS id, u.id_user AS id_usuario, u.nombres AS nombre,
                           u.apellidos AS apellido, u.correo, u.estado, u.telefono,
                           u.documento_identidad AS identificacion,
                           'INDIVIDUAL' AS tipo_cliente, c.fecha_nacimiento,
                           c.estatura_cm AS estatura_m, c.peso_inicial_kg AS peso_inicial,
                           c.objetivo_principal AS objetivos, NULL AS restricciones_medicas
                    FROM clientes c
                    INNER JOIN user u ON u.id_user = c.id_user
                    WHERE c.id_user = :usuario_id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fila ? $this->normalizarFilaCliente($fila) : null;
        }

        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado, u.telefono,
                       u.documento_identidad AS identificacion,
                       c.tipo_cliente, c.fecha_nacimiento, c.estatura_m, c.peso_inicial,
                       c.objetivos, c.restricciones_medicas
                FROM cliente c
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                WHERE c.id_cliente = :usuario_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->normalizarFilaCliente($fila) : null;
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT u.id_usuario AS id, u.nombre, u.apellido, u.correo, u.estado,
                       c.tipo_cliente, c.objetivos, pc.estado AS estado_plan
                FROM cliente c
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                INNER JOIN plan_cliente pc ON pc.id_cliente = c.id_cliente
                WHERE pc.id_coach = :coach_id AND pc.estado = 'ACTIVO'
                GROUP BY c.id_cliente
                ORDER BY u.nombre ASC"; // Clientes del coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna clientes
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "INSERT INTO clientes
                    (id_user, fecha_nacimiento, objetivo_principal, fecha_alta)
                    VALUES
                    (:id_user, :fecha_nacimiento, :objetivo_principal, CURDATE())";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_user', $datos['id_cliente']);
            $stmt->bindValue(':fecha_nacimiento', $datos['fecha_nacimiento'] ?? null);
            $stmt->bindValue(':objetivo_principal', $datos['objetivos'] ?? null);

            return $stmt->execute();
        }

        $sql = "INSERT INTO cliente 
                (id_cliente, tipo_cliente, fecha_nacimiento, estatura_m, peso_inicial, objetivos, restricciones_medicas)
                VALUES 
                (:id_cliente, :tipo_cliente, :fecha_nacimiento, :estatura_m, :peso_inicial, :objetivos, :restricciones_medicas)"; // Crea cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_cliente', $datos['id_cliente']); // ID usuario (FK)
        $stmt->bindValue(':tipo_cliente', $datos['tipo_cliente'] ?? 'INDIVIDUAL'); // Tipo cliente
        $stmt->bindValue(':fecha_nacimiento', $datos['fecha_nacimiento'] ?? null); // Fecha nacimiento
        $stmt->bindValue(':estatura_m', $datos['estatura_m'] ?? null); // Estatura
        $stmt->bindValue(':peso_inicial', $datos['peso_inicial'] ?? null); // Peso inicial
        $stmt->bindValue(':objetivos', $datos['objetivos'] ?? null); // Objetivos
        $stmt->bindValue(':restricciones_medicas', $datos['restricciones_medicas'] ?? null); // Restricciones

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizarPerfil($datos)
    {
        $sql = "UPDATE cliente 
                SET fecha_nacimiento = :fecha_nacimiento,
                    estatura_m = :estatura_m,
                    objetivos = :objetivos,
                    restricciones_medicas = :restricciones_medicas
                WHERE id_cliente = :id"; // Actualiza perfil

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindValue(':fecha_nacimiento', $datos['fecha_nacimiento'] ?? null); // Fecha nacimiento
        $stmt->bindValue(':estatura_m', $datos['estatura_m'] ?? null); // Estatura
        $stmt->bindValue(':objetivos', $datos['objetivos'] ?? null); // Objetivos
        $stmt->bindValue(':restricciones_medicas', $datos['restricciones_medicas'] ?? null); // Restricciones
        $stmt->bindParam(':id', $datos['id_cliente']); // ID cliente

        return $stmt->execute(); // Ejecuta actualización
    }

    public function cambiarEstado($id, $estado)
    {
        $mapa = ['activo' => 'ACTIVO', 'inactivo' => 'INACTIVO', 'suspendido' => 'SUSPENDIDO']; // Estados
        $estadoBd = $mapa[strtolower($estado)] ?? strtoupper($estado); // Estado BD

        $sql = "UPDATE users SET estado = :estado WHERE id_usuario = :id"; // Cambia estado en users
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estadoBd); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID usuario

        return $stmt->execute(); // Ejecuta cambio
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Clientes', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }

    public function obtenerClientesActivos()
    {
        $sql = "SELECT u.id_usuario AS id, u.nombre, u.apellido, u.correo, u.estado, u.telefono,
                       c.tipo_cliente, c.objetivos
                FROM cliente c
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                WHERE u.estado = 'ACTIVO'
                ORDER BY u.nombre ASC"; // Clientes activos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function crearClienteFijo($datos)
    {
        $idCliente = $datos['usuario_id'] ?? $datos['id_cliente'];

        if ($this->obtenerPorId($idCliente)) {
            return true;
        }

        try {
            return $this->crear([
                'id_cliente' => $idCliente,
                'tipo_cliente' => strtoupper($datos['tipo_cliente'] ?? 'INDIVIDUAL'),
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? edadAFechaNacimiento($datos['edad'] ?? null),
                'objetivos' => $datos['objetivos'] ?? null,
            ]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return true;
            }

            throw $e;
        }
    }

    private function normalizarFilaCliente(array $fila)
    {
        $fila['id'] = $fila['id'] ?? $fila['id_cliente'] ?? $fila['id_usuario'] ?? null;
        $fila['estado'] = strtolower($fila['estado'] ?? 'activo');
        $fila['celular'] = $fila['telefono'] ?? $fila['celular'] ?? null;
        $fila['edad'] = calcularEdadDesdeFecha($fila['fecha_nacimiento'] ?? null);

        if (!empty($fila['apellido'])) {
            $fila['nombre'] = trim(($fila['nombre'] ?? '') . ' ' . ($fila['apellido'] ?? ''));
        }

        return $fila;
    }

    public function crearDesdeSolicitud($datos)
    {
        return $this->crearClienteFijo($datos); // Alias solicitud → cliente
    }

    public function actualizar($datos)
    {
        $id = $datos['id'] ?? $datos['id_cliente']; // ID cliente

        if (isset($datos['apellido'])) {
            $partesNombre = [
                'nombre' => trim($datos['nombre'] ?? ''),
                'apellido' => trim($datos['apellido'] ?? ''),
            ];
        } else {
            $partesNombre = dividirNombreCompleto($datos['nombre'] ?? '');
        }

        $sqlUser = "UPDATE users SET nombre = :nombre, apellido = :apellido, correo = :correo,
                    telefono = :telefono, documento_identidad = :documento_identidad, estado = :estado
                    WHERE id_usuario = :id";
        $stmtUser = $this->db->prepare($sqlUser);
        $stmtUser->bindParam(':nombre', $partesNombre['nombre']);
        $stmtUser->bindValue(':apellido', $partesNombre['apellido']);
        $stmtUser->bindParam(':correo', $datos['correo']);
        $stmtUser->bindValue(':telefono', $datos['celular'] ?? $datos['telefono'] ?? null);
        $stmtUser->bindValue(':documento_identidad', $datos['identificacion'] ?? null);
        $stmtUser->bindValue(':estado', strtoupper($datos['estado'] ?? 'ACTIVO'));
        $stmtUser->bindParam(':id', $id);
        $stmtUser->execute();

        $sqlCliente = "UPDATE cliente SET tipo_cliente = :tipo_cliente, fecha_nacimiento = :fecha_nacimiento
                         WHERE id_cliente = :id";
        $stmtCliente = $this->db->prepare($sqlCliente);
        $stmtCliente->bindValue(':tipo_cliente', strtoupper($datos['tipo_cliente'] ?? 'INDIVIDUAL'));
        $stmtCliente->bindValue(':fecha_nacimiento', $datos['fecha_nacimiento'] ?? edadAFechaNacimiento($datos['edad'] ?? null));
        $stmtCliente->bindParam(':id', $id);

        return $stmtCliente->execute();
    }

    public function obtenerPagos($clienteId)
    {
        $sql = "SELECT p.id_pago AS id, p.monto, p.estado_pago AS estado, p.fecha_pago AS fecha, pl.nombre AS plan
                FROM pago p
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = p.id_plan_cliente
                INNER JOIN plan pl ON pl.id_plan = pc.id_plan
                WHERE pc.id_cliente = :cliente_id
                ORDER BY p.id_pago DESC"; // Pagos del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna pagos
    }

    public function obtenerPlanActivo($clienteId)
    {
        $sql = "SELECT pc.*, pl.nombre, pl.precio, pl.descripcion, pl.duracion_dias,
                       'VIRTUAL' AS modalidad
                FROM plan_cliente pc
                INNER JOIN plan pl ON pl.id_plan = pc.id_plan
                WHERE pc.id_cliente = :cliente_id AND pc.estado = 'ACTIVO'
                ORDER BY pc.id_plan_cliente DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna plan
    }

    public function obtenerCoachAsignado($clienteId)
    {
        $sql = "SELECT pc.id_coach, u.nombre, u.apellido, u.correo, c.especialidad,
                       CONCAT(u.nombre, ' ', IFNULL(u.apellido, '')) AS nombre_completo
                FROM plan_cliente pc
                INNER JOIN coach c ON c.id_coach = pc.id_coach
                INNER JOIN users u ON u.id_usuario = c.id_coach
                WHERE pc.id_cliente = :cliente_id AND pc.estado = 'ACTIVO' AND pc.id_coach IS NOT NULL
                LIMIT 1"; // Coach asignado

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna coach
    }

    public function obtenerIdCoachAsignado($clienteId)
    {
        $coach = $this->obtenerCoachAsignado($clienteId);

        return $coach ? (int) ($coach['id_coach'] ?? 0) : null;
    }

    public function obtenerAsignaciones()
    {
        $subPrograma = $this->tablaExiste('programa_virtual')
            ? '(SELECT pv.nombre FROM programa_virtual pv WHERE pv.id_plan = pl.id_plan LIMIT 1)'
            : 'NULL';

        $selectModalidad = $this->columnaExiste('plan', 'modalidad')
            ? 'pl.modalidad'
            : "'VIRTUAL' AS modalidad";

        $sql = "SELECT pc.id_plan_cliente, pc.id_cliente, pc.id_coach, pc.estado, pc.fecha_inicio,
                       CONCAT(u.nombre, ' ', IFNULL(u.apellido, '')) AS cliente,
                       CONCAT(uc.nombre, ' ', IFNULL(uc.apellido, '')) AS coach,
                       pl.nombre AS plan,
                       {$selectModalidad},
                       {$subPrograma} AS programa_virtual
                FROM plan_cliente pc
                INNER JOIN users u ON u.id_usuario = pc.id_cliente
                INNER JOIN plan pl ON pl.id_plan = pc.id_plan
                LEFT JOIN users uc ON uc.id_usuario = pc.id_coach
                ORDER BY pc.id_plan_cliente DESC"; // Historial: todos los vínculos plan–cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($filas as &$fila) {
            if (empty(trim($fila['coach'] ?? ''))) {
                $fila['coach'] = 'Sin coach';
            }
            if (empty($fila['programa_virtual'])) {
                $fila['programa_virtual'] = ($fila['modalidad'] ?? '') === 'VIRTUAL' ? ($fila['plan'] ?? 'No asignado') : 'No asignado';
            }
        }

        return $filas; // Retorna asignaciones
    }

    public function resolverIdPlan($clienteId, $planIdPreferido = null)
    {
        if ($planIdPreferido) {
            if ($this->columnaExiste('plan', 'estado_plan')) {
                $sql = "SELECT id_plan FROM plan WHERE id_plan = :id AND estado_plan = 'ACTIVO' LIMIT 1";
            } else {
                $sql = "SELECT id_plan FROM plan WHERE id_plan = :id LIMIT 1";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $planIdPreferido);
            $stmt->execute();
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($plan) {
                return (int) $plan['id_plan'];
            }
        }

        $sql = "SELECT id_plan FROM plan_cliente WHERE id_cliente = :cliente_id ORDER BY id_plan_cliente DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();
        $previo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($previo) {
            return (int) $previo['id_plan'];
        }

        if ($this->columnaExiste('plan', 'estado_plan')) {
            $sql = "SELECT id_plan FROM plan WHERE estado_plan = 'ACTIVO' ORDER BY id_plan ASC LIMIT 1";
        } else {
            $sql = "SELECT id_plan FROM plan ORDER BY id_plan ASC LIMIT 1";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($plan) {
            return (int) $plan['id_plan'];
        }

        $planModel = new PlanModel();

        return $planModel->asegurarPlanesBase();
    }

    public function resolverIdPlanDesdeSolicitud($solicitud)
    {
        $planInteres = $solicitud['plan_interes'] ?? $solicitud['plan_id'] ?? null;

        if (!$planInteres) {
            return null;
        }

        if (is_numeric($planInteres)) {
            return $this->resolverIdPlan(null, (int) $planInteres);
        }

        $sql = "SELECT id_plan FROM plan WHERE nombre = :nombre AND estado_plan = 'ACTIVO' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $planInteres);
        $stmt->execute();
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($plan) {
            return (int) $plan['id_plan'];
        }

        $sql = "SELECT id_plan FROM plan WHERE estado_plan = 'ACTIVO' ORDER BY id_plan ASC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        return $plan ? (int) $plan['id_plan'] : null;
    }

    public function crearPlanCliente($clienteId, $planId, $coachId = null, $solicitudId = null)
    {
        $plan = $this->obtenerPlanCatalogo($planId);
        if (!$plan) {
            return false;
        }

        $duracion = (int) ($plan['duracion_dias'] ?? 30);
        $fechaInicio = date('Y-m-d');
        $fechaFin = date('Y-m-d', strtotime('+' . max($duracion, 1) . ' days'));

        $columnas = ['id_plan', 'id_cliente', 'fecha_inicio', 'fecha_fin', 'estado'];
        $marcadores = [':id_plan', ':cliente_id', ':fecha_inicio', ':fecha_fin', "'ACTIVO'"];

        if ($coachId || $this->columnaPermiteNull('plan_cliente', 'id_coach')) {
            array_splice($columnas, 2, 0, ['id_coach']);
            array_splice($marcadores, 2, 0, [':coach_id']);
        }

        if ($this->columnaExiste('plan_cliente', 'id_solicitud')) {
            $columnas[] = 'id_solicitud';
            $marcadores[] = ':solicitud_id';
        }

        $sql = 'INSERT INTO plan_cliente (' . implode(', ', $columnas) . ')
                VALUES (' . implode(', ', $marcadores) . ')';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_plan', $planId);
        $stmt->bindParam(':cliente_id', $clienteId);

        if (in_array('id_coach', $columnas, true)) {
            $stmt->bindValue(':coach_id', $coachId ?: null, $coachId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        }

        $stmt->bindParam(':fecha_inicio', $fechaInicio);
        $stmt->bindParam(':fecha_fin', $fechaFin);

        if ($this->columnaExiste('plan_cliente', 'id_solicitud')) {
            $stmt->bindValue(':solicitud_id', $solicitudId ?: null, $solicitudId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        }

        if (!$stmt->execute()) {
            return false;
        }

        return (int) $this->db->lastInsertId();
    }

    public function crearPlanClienteDesdeSolicitud($clienteId, $solicitud)
    {
        $activo = $this->obtenerPlanClienteActivoId($clienteId);
        if ($activo) {
            return $activo;
        }

        $planId = $this->resolverIdPlanDesdeSolicitud($solicitud);
        if (!$planId) {
            return false;
        }

        return $this->crearPlanCliente(
            $clienteId,
            $planId,
            null,
            $solicitud['id_solicitud'] ?? $solicitud['id'] ?? null
        );
    }

    public function obtenerPlanClienteActivoId($clienteId)
    {
        $sql = "SELECT id_plan_cliente FROM plan_cliente
                WHERE id_cliente = :cliente_id AND estado = 'ACTIVO'
                ORDER BY id_plan_cliente DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? (int) $fila['id_plan_cliente'] : null;
    }

    private function obtenerPlanCatalogo($planId)
    {
        $sql = "SELECT id_plan, duracion_dias FROM plan WHERE id_plan = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $planId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function asignarPlanCliente($clienteId, $planId, $coachId = null)
    {
        $planId = $this->resolverIdPlan($clienteId, $planId);
        if (!$planId) {
            return false;
        }

        $sqlUpdate = "UPDATE plan_cliente SET id_plan = :plan_id, id_coach = :coach_id
                      WHERE id_cliente = :cliente_id AND estado = 'ACTIVO'";
        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':plan_id', $planId);
        $stmtUpdate->bindValue(':coach_id', $coachId, $coachId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmtUpdate->bindParam(':cliente_id', $clienteId);
        $stmtUpdate->execute();

        if ($stmtUpdate->rowCount() > 0) {
            return true;
        }

        return (bool) $this->crearPlanCliente($clienteId, $planId, $coachId);
    }

    public function asignarCoach($clienteId, $coachId)
    {
        $clienteId = (int) $clienteId;
        $coachId = (int) $coachId;

        if ($clienteId < 1 || $coachId < 1 || !$this->coachExiste($coachId)) {
            return false;
        }

        if ($this->actualizarCoachEnPlanCliente($clienteId, $coachId, "estado = 'ACTIVO'")) {
            return true;
        }

        if ($this->actualizarCoachEnPlanCliente($clienteId, $coachId, '1 = 1')) {
            return true;
        }

        $planId = $this->resolverIdPlan($clienteId);
        if (!$planId) {
            return false;
        }

        return (bool) $this->crearPlanCliente($clienteId, $planId, $coachId);
    }

    private function coachExiste(int $coachId): bool
    {
        if (!$this->tablaExiste('coach')) {
            return true;
        }

        $sql = 'SELECT 1 FROM coach WHERE id_coach = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $coachId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    private function actualizarCoachEnPlanCliente(int $clienteId, int $coachId, string $condicionExtra): bool
    {
        $sql = "UPDATE plan_cliente SET id_coach = :coach_id
                WHERE id_cliente = :cliente_id AND ({$condicionExtra})";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':coach_id', $coachId, PDO::PARAM_INT);
        $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }

        $sqlCheck = "SELECT id_coach FROM plan_cliente
                     WHERE id_cliente = :cliente_id AND ({$condicionExtra})
                     ORDER BY id_plan_cliente DESC LIMIT 1";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmtCheck->execute();
        $fila = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $fila && (int) ($fila['id_coach'] ?? 0) === $coachId;
    }

    public function cambiarCoach($clienteId, $coachId)
    {
        return $this->asignarCoach($clienteId, $coachId); // Reasigna coach
    }

    private function tablaExiste(string $nombre): bool
    {
        static $cache = [];

        if (array_key_exists($nombre, $cache)) {
            return $cache[$nombre];
        }

        try {
            $stmt = $this->db->query('SHOW TABLES LIKE ' . $this->db->quote($nombre));
            $cache[$nombre] = (bool) $stmt->fetch(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            $cache[$nombre] = false;
        }

        return $cache[$nombre];
    }

    private function columnaExiste(string $tabla, string $columna): bool
    {
        return $this->obtenerInfoColumna($tabla, $columna) !== null;
    }

    private function columnaPermiteNull(string $tabla, string $columna): bool
    {
        $info = $this->obtenerInfoColumna($tabla, $columna);

        return $info && strtoupper($info['Null'] ?? 'NO') === 'YES';
    }

    private function obtenerInfoColumna(string $tabla, string $columna): ?array
    {
        static $cache = [];
        $clave = $tabla . '.' . $columna;

        if (array_key_exists($clave, $cache)) {
            return $cache[$clave];
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabla) || !preg_match('/^[a-zA-Z0-9_]+$/', $columna)) {
            return $cache[$clave] = null;
        }

        try {
            $stmt = $this->db->query(
                'SHOW COLUMNS FROM `' . $tabla . '` LIKE ' . $this->db->quote($columna)
            );
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            $cache[$clave] = $fila ?: null;
        } catch (PDOException $e) {
            $cache[$clave] = null;
        }

        return $cache[$clave];
    }
}

?>
