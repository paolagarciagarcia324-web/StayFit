<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';
require_once __DIR__ . '/../../config/helpers.php';

class SolicitudIngresoModel
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

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_solicitud'] ?? $fila['id'] ?? null;
        $fila['nombre'] = $fila['nombre_completo']
            ?? trim(($fila['nombres'] ?? '') . ' ' . ($fila['apellidos'] ?? ''));
        if (isset($fila['estado_solicitud'])) {
            $estadoBd = strtoupper((string) $fila['estado_solicitud']);
            if ($estadoBd === 'PENDIENTE' && !empty($fila['fecha_revision'])) {
                $fila['estado'] = 'en_revision';
            } elseif ($estadoBd === 'VALIDADA') {
                $fila['estado'] = 'aprobada';
            } else {
                $fila['estado'] = strtolower($estadoBd);
            }
        } else {
            $fila['estado'] = strtolower($fila['estado'] ?? 'pendiente');
        }
        $fila['plan_interes'] = $fila['plan_interes'] ?? $fila['nombre_plan'] ?? null;
        $fila['celular'] = $fila['telefono'] ?? $fila['celular'] ?? null;
        $fila['identificacion'] = $fila['documento_identidad'] ?? $fila['identificacion'] ?? null;
        $fila['correo'] = $fila['correo'] ?? null;

        $observacion = trim((string) ($fila['observacion_usuario'] ?? ''));
        if ($observacion !== '' && ($observacion[0] === '{' || $observacion[0] === '[')) {
            $extra = json_decode($observacion, true);
            if (is_array($extra)) {
                foreach (['edad', 'modalidad', 'tipo_cuenta', 'numero_cuenta'] as $campo) {
                    if (!isset($fila[$campo]) && isset($extra[$campo])) {
                        $fila[$campo] = $extra[$campo];
                    }
                }
            }
        }

        $url = trim((string) ($fila['url_comprobante'] ?? ''));
        if ($url === '') {
            $url = trim((string) ($fila['comprobante_pago'] ?? $fila['comprobante_url'] ?? ''));
        }
        $fila['url_comprobante'] = $url !== '' ? $url : null;

        return $fila;
    }

    private function normalizarLista($filas)
    {
        $resultado = [];
        foreach ($filas as $fila) {
            $resultado[] = $this->normalizarFila($fila);
        }
        return $resultado;
    }

    private function sqlListadoBase(): string
    {
        if ($this->usaEsquemaNuevo()) {
            return "SELECT s.*,
                           CONCAT(s.nombres, ' ', s.apellidos) AS nombre_completo,
                           pl.nombre AS plan_interes,
                           p.comprobante_url AS comprobante_pago,
                           p.id_pago,
                           p.monto AS monto_pago
                    FROM solicitudes_compra s
                    LEFT JOIN pagos p ON p.id_solicitud = s.id_solicitud
                    LEFT JOIN planes pl ON pl.id_plan = s.id_plan";
        }

        return "SELECT s.*, p.url_comprobante AS comprobante_pago, p.id_pago, p.monto AS monto_pago
                FROM solicitud_ingreso s
                LEFT JOIN pago p ON p.id_solicitud = s.id_solicitud";
    }

    public function obtenerTodas()
    {
        $sql = $this->sqlListadoBase() . ' ORDER BY s.id_solicitud DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerTodos()
    {
        return $this->obtenerTodas();
    }

    public function obtenerPendientes()
    {
        return $this->obtenerPorEstado('pendiente');
    }

    public function obtenerPorEstado($estado)
    {
        $estado = strtoupper($estado);
        $campoEstado = $this->usaEsquemaNuevo() ? 's.estado_solicitud' : 's.estado';

        $sql = $this->sqlListadoBase() . " WHERE {$campoEstado} = :estado ORDER BY s.id_solicitud DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerPorId($id)
    {
        $sql = $this->sqlListadoBase() . ' WHERE s.id_solicitud = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila && $this->usaEsquemaNuevo()) {
            $fila['estado_pago'] = $fila['estado_pago'] ?? null;
        }

        return $this->normalizarFila($fila);
    }

    private function generarCodigoSolicitud(): string
    {
        do {
            $codigo = 'FF-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $stmt = $this->db->prepare('SELECT 1 FROM solicitudes_compra WHERE codigo_solicitud = :codigo LIMIT 1');
            $stmt->bindValue(':codigo', $codigo);
            $stmt->execute();
            $existe = (bool) $stmt->fetchColumn();
        } while ($existe);

        return $codigo;
    }

    private function armarObservacionUsuario(array $datos): ?string
    {
        $extra = array_filter([
            'edad' => $datos['edad'] ?? null,
            'modalidad' => $datos['modalidad'] ?? null,
            'tipo_cuenta' => $datos['tipo_cuenta'] ?? null,
            'numero_cuenta' => $datos['numero_cuenta'] ?? null,
        ], static fn($v) => $v !== null && $v !== '');

        if (!empty($datos['password_hash'])) {
            $extra['password_hash'] = $datos['password_hash'];
        }

        if ($extra === []) {
            return $datos['observacion'] ?? null;
        }

        return json_encode($extra, JSON_UNESCAPED_UNICODE);
    }

    public function resolverPasswordHashRegistro(array $solicitud): ?string
    {
        $observacion = trim((string) ($solicitud['observacion_usuario'] ?? ''));
        if ($observacion === '' || ($observacion[0] !== '{' && $observacion[0] !== '[')) {
            return null;
        }

        $extra = json_decode($observacion, true);
        if (!is_array($extra) || empty($extra['password_hash'])) {
            return null;
        }

        return (string) $extra['password_hash'];
    }

    public function resolverPasswordPlanoRegistro(array $solicitud): string
    {
        $hash = $this->resolverPasswordHashRegistro($solicitud);
        if ($hash) {
            return $hash;
        }

        return trim((string) ($solicitud['identificacion'] ?? $solicitud['documento_identidad'] ?? ''));
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $partes = dividirNombreCompleto($datos['nombre'] ?? '');
            $planId = is_numeric($datos['plan_id'] ?? null) ? (int) $datos['plan_id'] : 0;
            $correo = trim((string) ($datos['correo'] ?? ''));

            if ($planId < 1) {
                throw new InvalidArgumentException('Debe seleccionar un plan válido.');
            }

            if ($correo === '') {
                throw new InvalidArgumentException('El correo electrónico es obligatorio.');
            }

            $sql = "INSERT INTO solicitudes_compra
                    (codigo_solicitud, nombres, apellidos, documento_identidad, correo, telefono, id_plan,
                     tipo_cliente, observacion_usuario, estado_solicitud, creado_en)
                    VALUES
                    (:codigo_solicitud, :nombres, :apellidos, :identificacion, :correo, :telefono, :id_plan,
                     :tipo_cliente, :observacion, :estado, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':codigo_solicitud', $this->generarCodigoSolicitud());
            $stmt->bindValue(':nombres', $partes['nombre']);
            $stmt->bindValue(':apellidos', $partes['apellido']);
            $stmt->bindValue(':identificacion', $datos['identificacion'] ?? null);
            $stmt->bindValue(':correo', $correo);
            $stmt->bindValue(':telefono', $datos['celular'] ?? $datos['telefono'] ?? null);
            $stmt->bindValue(':id_plan', $planId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_cliente', strtoupper($datos['tipo_cliente'] ?? 'INDIVIDUAL'));
            $stmt->bindValue(':observacion', $this->armarObservacionUsuario($datos));
            $stmt->bindValue(':estado', strtoupper($datos['estado'] ?? 'PENDIENTE'));
            $stmt->execute();

            return $this->db->lastInsertId();
        }

        $sql = "INSERT INTO solicitud_ingreso 
                (nombre_completo, edad, identificacion, celular, plan_interes, modalidad,
                 tipo_cuenta, numero_cuenta, url_comprobante, estado)
                VALUES
                (:nombre, :edad, :identificacion, :celular, :plan_interes, :modalidad,
                 :tipo_cuenta, :numero_cuenta, :url_comprobante, :estado)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':edad', $datos['edad']);
        $stmt->bindParam(':identificacion', $datos['identificacion']);
        $stmt->bindParam(':celular', $datos['celular']);
        $planInteres = $datos['plan_interes'] ?? null;
        if ($planInteres === null && !empty($datos['plan_id'])) {
            $planInteres = (string) $datos['plan_id'];
        }
        $stmt->bindValue(':plan_interes', $planInteres);
        $stmt->bindValue(':modalidad', strtoupper($datos['modalidad'] ?? 'VIRTUAL'));
        $stmt->bindValue(':tipo_cuenta', $datos['tipo_cuenta'] ?? '');
        $stmt->bindValue(':numero_cuenta', $datos['numero_cuenta'] ?? '');
        $stmt->bindValue(':url_comprobante', $datos['url_comprobante'] ?? null);
        $stmt->bindValue(':estado', strtoupper($datos['estado'] ?? 'PENDIENTE'));
        $stmt->execute();

        return $this->db->lastInsertId();
    }

    private function normalizarEstadoParaBd(string $estado): string
    {
        $estado = strtoupper(trim($estado));

        if (!$this->usaEsquemaNuevo()) {
            return $estado;
        }

        $mapa = [
            'APROBADA' => 'VALIDADA',
            'APROBADO' => 'VALIDADA',
            'VALIDADA' => 'VALIDADA',
            'PENDIENTE' => 'PENDIENTE',
            'RECHAZADA' => 'RECHAZADA',
            'CANCELADA' => 'CANCELADA',
        ];

        return $mapa[$estado] ?? $estado;
    }

    public function actualizarEstado($id, $estado)
    {
        return $this->cambiarEstado($id, $estado);
    }

    public function cambiarEstado($id, $estado)
    {
        $estado = $this->normalizarEstadoParaBd((string) $estado);
        $tabla = $this->usaEsquemaNuevo() ? 'solicitudes_compra' : 'solicitud_ingreso';
        $campo = $this->usaEsquemaNuevo() ? 'estado_solicitud' : 'estado';

        $sql = "UPDATE {$tabla} SET {$campo} = :estado WHERE id_solicitud = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function marcarRevision($id, $usuarioId = null)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "UPDATE solicitudes_compra
                    SET fecha_revision = NOW(),
                        revisado_por = :revisado_por,
                        observacion_admin = CASE
                            WHEN observacion_admin IS NULL OR observacion_admin = '' THEN 'En revisión'
                            ELSE observacion_admin
                        END
                    WHERE id_solicitud = :id
                      AND estado_solicitud = 'PENDIENTE'";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':revisado_por', $usuarioId, $usuarioId ? PDO::PARAM_INT : PDO::PARAM_NULL);

            return $stmt->execute();
        }

        return $this->cambiarEstado($id, 'EN_REVISION');
    }

    public function aprobar($id, $usuarioId = null)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "UPDATE solicitudes_compra
                    SET estado_solicitud = 'VALIDADA',
                        revisado_por = COALESCE(:revisado_por, revisado_por),
                        fecha_revision = COALESCE(fecha_revision, NOW())
                    WHERE id_solicitud = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':revisado_por', $usuarioId, $usuarioId ? PDO::PARAM_INT : PDO::PARAM_NULL);

            return $stmt->execute();
        }

        return $this->cambiarEstado($id, 'APROBADA');
    }

    public function rechazar($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "UPDATE solicitudes_compra
                    SET estado_solicitud = 'RECHAZADA', observacion_admin = :observacion
                    WHERE id_solicitud = :id";
        } else {
            $sql = "UPDATE solicitud_ingreso 
                    SET estado = 'RECHAZADA', observacion_admin = :observacion
                    WHERE id_solicitud = :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':observacion', $datos['observacion'] ?? 'Solicitud rechazada');
        $stmt->bindParam(':id', $datos['id']);

        return $stmt->execute();
    }

    public function contarPendientes()
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT COUNT(*) AS total FROM solicitudes_compra WHERE estado_solicitud = 'PENDIENTE'";
        } else {
            $sql = "SELECT COUNT(*) AS total FROM solicitud_ingreso WHERE estado = 'PENDIENTE'";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Solicitudes de ingreso', $accion);
    }
}

?>