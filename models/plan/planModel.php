<?php

require_once __DIR__ . '/../../config/database.php';

class PlanModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
    }

    private function tablaExiste($tabla)
    {
        $stmt = $this->db->query('SHOW TABLES LIKE ' . $this->db->quote($tabla));

        return (bool) $stmt->fetch();
    }

    private function usaEsquemaNuevo()
    {
        return $this->tablaExiste('planes');
    }

    private function tablaPlanes()
    {
        return $this->usaEsquemaNuevo() ? 'planes' : 'plan';
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_plan'] ?? $fila['id'] ?? null;
        $fila['duracion'] = $fila['duracion_dias'] ?? $fila['duracion'] ?? null;
        $fila['estado'] = strtolower($fila['estado_plan'] ?? $fila['estado'] ?? 'activo');

        return $fila;
    }

    private function normalizarModalidad($modalidad)
    {
        $modalidad = strtoupper((string) $modalidad);

        return $modalidad === 'MIXTA' ? 'MIXTO' : $modalidad;
    }

    private function normalizarCupoMaximo($cupo)
    {
        if ($cupo === null || $cupo === '' || (is_string($cupo) && trim($cupo) === '')) {
            return null;
        }

        $valor = (int) $cupo;

        return $valor > 0 ? $valor : null;
    }

    private function normalizarLista($lista)
    {
        foreach ($lista as &$fila) {
            $fila = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerTodos()
    {
        $tabla = $this->tablaPlanes();
        $sql = "SELECT * FROM {$tabla} ORDER BY id_plan DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerActivos()
    {
        $tabla = $this->tablaPlanes();
        $sql = "SELECT * FROM {$tabla}
                WHERE estado_plan = 'ACTIVO'
                ORDER BY precio ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerPlanesVirtuales()
    {
        $tabla = $this->tablaPlanes();
        $sql = "SELECT * FROM {$tabla}
                WHERE estado_plan = 'ACTIVO'
                  AND modalidad IN ('VIRTUAL', 'MIXTO')
                ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerPlanesInstitucionales()
    {
        $tabla = $this->tablaPlanes();

        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT * FROM {$tabla}
                    WHERE estado_plan = 'ACTIVO'
                      AND tipo_cliente IN ('INSTITUCIONAL', 'AMBOS')
                    ORDER BY nombre ASC";
        } else {
            $sql = "SELECT * FROM {$tabla}
                    WHERE estado = 'ACTIVO'
                    ORDER BY nombre ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $this->normalizarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerPorId($id)
    {
        $tabla = $this->tablaPlanes();
        $sql = "SELECT * FROM {$tabla} WHERE id_plan = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function contarInscripciones($planId, $excluirSolicitudId = null)
    {
        if (!$this->usaEsquemaNuevo()) {
            return 0;
        }

        $planId = (int) $planId;

        $sqlActivos = "SELECT COUNT(*) FROM planes_cliente
                       WHERE id_plan = :id_plan AND estado_plan_cliente = 'ACTIVO'";
        $stmt = $this->db->prepare($sqlActivos);
        $stmt->bindValue(':id_plan', $planId, PDO::PARAM_INT);
        $stmt->execute();
        $activos = (int) $stmt->fetchColumn();

        $sqlPendientes = "SELECT COUNT(*) FROM solicitudes_compra
                          WHERE id_plan = :id_plan AND estado_solicitud = 'PENDIENTE'";
        if ($excluirSolicitudId) {
            $sqlPendientes .= ' AND id_solicitud != :excluir_solicitud';
        }

        $stmt = $this->db->prepare($sqlPendientes);
        $stmt->bindValue(':id_plan', $planId, PDO::PARAM_INT);
        if ($excluirSolicitudId) {
            $stmt->bindValue(':excluir_solicitud', (int) $excluirSolicitudId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $pendientes = (int) $stmt->fetchColumn();

        return $activos + $pendientes;
    }

    public function obtenerInfoCupo($planId, $excluirSolicitudId = null)
    {
        $plan = $this->obtenerPorId($planId);
        if (!$plan) {
            return null;
        }

        $cupoMaximo = isset($plan['cupo_maximo']) && $plan['cupo_maximo'] !== null && $plan['cupo_maximo'] !== ''
            ? (int) $plan['cupo_maximo']
            : null;
        $ocupados = $this->contarInscripciones($planId, $excluirSolicitudId);
        $disponibles = $cupoMaximo === null ? null : max(0, $cupoMaximo - $ocupados);

        $estadoPlan = strtoupper((string) ($plan['estado_plan'] ?? $plan['estado'] ?? 'ACTIVO'));
        $estadoCupo = 'SIN_LIMITE';

        if ($estadoPlan === 'INACTIVO') {
            $estadoCupo = 'INACTIVO';
        } elseif ($cupoMaximo !== null) {
            if ($disponibles <= 0) {
                $estadoCupo = 'CUPO_LLENO';
            } elseif ($disponibles <= 5) {
                $estadoCupo = 'ULTIMOS_CUPOS';
            } else {
                $estadoCupo = 'DISPONIBLE';
            }
        }

        return [
            'cupo_maximo' => $cupoMaximo,
            'ocupados' => $ocupados,
            'cupos_disponibles' => $disponibles,
            'estado_cupo' => $estadoCupo,
            'plan_activo' => $estadoPlan === 'ACTIVO',
        ];
    }

    public function puedeInscribirse($planId, $excluirSolicitudId = null)
    {
        $plan = $this->obtenerPorId($planId);
        if (!$plan) {
            return [
                'ok' => false,
                'mensaje' => 'El plan seleccionado no existe o no está disponible.',
            ];
        }

        $estadoPlan = strtoupper((string) ($plan['estado_plan'] ?? $plan['estado'] ?? 'ACTIVO'));
        if ($estadoPlan !== 'ACTIVO') {
            return [
                'ok' => false,
                'mensaje' => 'Este plan ya no acepta nuevas inscripciones.',
            ];
        }

        $info = $this->obtenerInfoCupo($planId, $excluirSolicitudId);
        if ($info['cupo_maximo'] === null) {
            return ['ok' => true, 'mensaje' => '', 'info' => $info];
        }

        if ($info['cupos_disponibles'] <= 0) {
            return [
                'ok' => false,
                'mensaje' => 'Este plan alcanzó el cupo máximo de inscripciones y ya no está disponible.',
                'info' => $info,
            ];
        }

        return ['ok' => true, 'mensaje' => '', 'info' => $info];
    }

    public function cerrarSiCupoLleno($planId)
    {
        if (!$this->usaEsquemaNuevo()) {
            return false;
        }

        $info = $this->obtenerInfoCupo($planId);
        if (!$info || $info['cupo_maximo'] === null || $info['estado_cupo'] !== 'CUPO_LLENO') {
            return false;
        }

        return $this->cambiarEstado($planId, 'INACTIVO');
    }

    public function adjuntarInfoCupo(array $planes)
    {
        foreach ($planes as &$plan) {
            $id = (int) ($plan['id'] ?? $plan['id_plan'] ?? 0);
            $plan['cupo_info'] = $id > 0 ? $this->obtenerInfoCupo($id) : null;
        }

        return $planes;
    }

    public function obtenerPlanActivoCliente($clienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT pc.id_plan_cliente, pc.id_plan, pc.id_cliente,
                           pc.estado_plan_cliente AS estado,
                           pc.fecha_inicio, pc.fecha_fin,
                           p.nombre, p.descripcion, p.precio, p.duracion_dias, p.estado_plan,
                           p.modalidad, p.requiere_coach,
                           TRIM(CONCAT(IFNULL(u.nombres, ''), ' ', IFNULL(u.apellidos, ''))) AS coach_nombre,
                           u.correo AS coach_correo,
                           co.especialidad AS coach_especialidad
                    FROM planes_cliente pc
                    INNER JOIN planes p ON p.id_plan = pc.id_plan
                    LEFT JOIN clientes cl ON cl.id_cliente = pc.id_cliente
                    LEFT JOIN coaches co ON co.id_coach = cl.id_coach
                    LEFT JOIN user u ON u.id_user = co.id_user
                    WHERE pc.id_cliente = :cliente_id
                      AND pc.estado_plan_cliente = 'ACTIVO'
                    ORDER BY pc.id_plan_cliente DESC
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->execute();

            return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC)) ?: null;
        }

        $sql = "SELECT pc.id_plan_cliente, pc.id_plan, pc.id_cliente, pc.id_coach, pc.estado,
                       pc.fecha_inicio, pc.fecha_fin,
                       pl.nombre, pl.descripcion, pl.precio, pl.duracion_dias, pl.estado_plan,
                       pl.modalidad, pl.requiere_coach,
                       CONCAT(uc.nombre, ' ', IFNULL(uc.apellido, '')) AS coach_nombre,
                       uc.correo AS coach_correo,
                       c.especialidad AS coach_especialidad
                FROM plan_cliente pc
                INNER JOIN plan pl ON pl.id_plan = pc.id_plan
                LEFT JOIN coach c ON c.id_coach = pc.id_coach
                LEFT JOIN users uc ON uc.id_usuario = c.id_coach
                WHERE pc.id_cliente = :cliente_id AND pc.estado = 'ACTIVO'
                ORDER BY pc.id_plan_cliente DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC)) ?: null;
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "INSERT INTO planes
                    (nombre, slug, descripcion, precio, duracion_dias, modalidad,
                     requiere_coach, incluye_entrenamiento, incluye_nutricion,
                     incluye_videos, incluye_sesiones, incluye_eventos, cupo_maximo, estado_plan)
                    VALUES
                    (:nombre, :slug, :descripcion, :precio, :duracion_dias, :modalidad,
                     :requiere_coach, :incluye_entrenamiento, :incluye_nutricion,
                     :incluye_videos, :incluye_sesiones, :incluye_eventos, :cupo_maximo, :estado_plan)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':slug' => $datos['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($datos['nombre']))),
                ':descripcion' => $datos['descripcion'] ?? '',
                ':precio' => $datos['precio'] ?? 0,
                ':duracion_dias' => $datos['duracion_dias'] ?? null,
                ':modalidad' => $this->normalizarModalidad($datos['modalidad'] ?? 'VIRTUAL'),
                ':requiere_coach' => !empty($datos['requiere_coach']) ? 1 : 0,
                ':incluye_entrenamiento' => !empty($datos['incluye_entrenamiento']) ? 1 : 0,
                ':incluye_nutricion' => !empty($datos['incluye_nutricion']) ? 1 : 0,
                ':incluye_videos' => !empty($datos['incluye_videos']) ? 1 : 0,
                ':incluye_sesiones' => !empty($datos['incluye_sesiones']) ? 1 : 0,
                ':incluye_eventos' => !empty($datos['incluye_eventos']) ? 1 : 0,
                ':cupo_maximo' => $this->normalizarCupoMaximo($datos['cupo_maximo'] ?? null),
                ':estado_plan' => strtoupper($datos['estado_plan'] ?? 'ACTIVO'),
            ]);

            return true;
        }

        $sql = "INSERT INTO plan
                (nombre, descripcion, precio, duracion_dias, dias_previos_recordatorio_default, estado_plan)
                VALUES
                (:nombre, :descripcion, :precio, :duracion_dias, :dias_previos_recordatorio_default, :estado_plan)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
        $stmt->bindValue(':precio', $datos['precio'] ?? 0);
        $stmt->bindValue(':duracion_dias', $datos['duracion_dias'] ?? null);
        $stmt->bindValue(':dias_previos_recordatorio_default', $datos['dias_previos_recordatorio_default'] ?? 5);
        $stmt->bindValue(':estado_plan', strtoupper($datos['estado_plan'] ?? 'ACTIVO'));

        return $stmt->execute();
    }

    public function actualizar($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "UPDATE planes
                    SET nombre = :nombre,
                        descripcion = :descripcion,
                        precio = :precio,
                        duracion_dias = :duracion_dias,
                        modalidad = :modalidad,
                        requiere_coach = :requiere_coach,
                        incluye_entrenamiento = :incluye_entrenamiento,
                        incluye_nutricion = :incluye_nutricion,
                        incluye_videos = :incluye_videos,
                        incluye_sesiones = :incluye_sesiones,
                        cupo_maximo = :cupo_maximo,
                        estado_plan = :estado_plan
                    WHERE id_plan = :id_plan";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':descripcion' => $datos['descripcion'] ?? '',
                ':precio' => $datos['precio'] ?? 0,
                ':duracion_dias' => $datos['duracion_dias'] ?? null,
                ':modalidad' => $this->normalizarModalidad($datos['modalidad'] ?? 'VIRTUAL'),
                ':requiere_coach' => !empty($datos['requiere_coach']) ? 1 : 0,
                ':incluye_entrenamiento' => !empty($datos['incluye_entrenamiento']) ? 1 : 0,
                ':incluye_nutricion' => !empty($datos['incluye_nutricion']) ? 1 : 0,
                ':incluye_videos' => !empty($datos['incluye_videos']) ? 1 : 0,
                ':incluye_sesiones' => !empty($datos['incluye_sesiones']) ? 1 : 0,
                ':cupo_maximo' => $this->normalizarCupoMaximo($datos['cupo_maximo'] ?? null),
                ':estado_plan' => strtoupper($datos['estado_plan'] ?? 'ACTIVO'),
                ':id_plan' => $datos['id_plan'] ?? $datos['id'],
            ]);

            return true;
        }

        $sql = "UPDATE plan
                SET nombre = :nombre,
                    descripcion = :descripcion,
                    precio = :precio,
                    duracion_dias = :duracion_dias,
                    dias_previos_recordatorio_default = :dias_previos_recordatorio_default,
                    estado_plan = :estado_plan
                WHERE id_plan = :id_plan";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? '');
        $stmt->bindValue(':precio', $datos['precio'] ?? 0);
        $stmt->bindValue(':duracion_dias', $datos['duracion_dias'] ?? null);
        $stmt->bindValue(':dias_previos_recordatorio_default', $datos['dias_previos_recordatorio_default'] ?? 5);
        $stmt->bindValue(':estado_plan', strtoupper($datos['estado_plan'] ?? 'ACTIVO'));
        $stmt->bindValue(':id_plan', $datos['id_plan'] ?? $datos['id'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado)
    {
        $estadoBd = ['activo' => 'ACTIVO', 'inactivo' => 'INACTIVO'][strtolower($estado)] ?? strtoupper($estado);
        $tabla = $this->tablaPlanes();
        $sql = "UPDATE {$tabla} SET estado_plan = :estado WHERE id_plan = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':estado', $estadoBd);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function obtenerUltimoId()
    {
        return $this->db->lastInsertId();
    }

    public function contar()
    {
        $tabla = $this->tablaPlanes();

        return (int) $this->db->query("SELECT COUNT(*) FROM {$tabla}")->fetchColumn();
    }

    public function asegurarPlanesBase()
    {
        $tabla = $this->tablaPlanes();
        $fila = $this->db->query("SELECT id_plan FROM {$tabla} ORDER BY id_plan ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $primerId = $fila ? (int) $fila['id_plan'] : null;

        $planes = [
            [
                'nombre' => 'Programa Virtual Fit',
                'descripcion' => 'Videos pregrabados y seguimiento de avance.',
                'precio' => 90000,
                'duracion_dias' => 30,
                'modalidad' => 'VIRTUAL',
                'requiere_coach' => 0,
                'incluye_entrenamiento' => 1,
                'incluye_nutricion' => 0,
                'incluye_videos' => 1,
                'incluye_sesiones' => 0,
            ],
            [
                'nombre' => 'Plan Presencial Integral',
                'descripcion' => 'Coach asignado, entrenamiento y nutricion.',
                'precio' => 180000,
                'duracion_dias' => 30,
                'modalidad' => 'PRESENCIAL',
                'requiere_coach' => 1,
                'incluye_entrenamiento' => 1,
                'incluye_nutricion' => 1,
                'incluye_videos' => 0,
                'incluye_sesiones' => 1,
            ],
            [
                'nombre' => 'Plan Mixto Premium',
                'descripcion' => 'Coach, sesiones y contenido virtual.',
                'precio' => 240000,
                'duracion_dias' => 30,
                'modalidad' => 'MIXTO',
                'requiere_coach' => 1,
                'incluye_entrenamiento' => 1,
                'incluye_nutricion' => 1,
                'incluye_videos' => 1,
                'incluye_sesiones' => 1,
            ],
        ];

        foreach ($planes as $plan) {
            $stmt = $this->db->prepare("SELECT id_plan FROM {$tabla} WHERE nombre = :nombre LIMIT 1");
            $stmt->bindParam(':nombre', $plan['nombre']);
            $stmt->execute();

            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                continue;
            }

            $this->crear($plan);

            if ($primerId === null) {
                $primerId = (int) $this->db->lastInsertId();
            }
        }

        return $primerId;
    }

    public function reporteGeneral()
    {
        $tabla = $this->tablaPlanes();
        $sql = "SELECT estado_plan, COUNT(*) AS total
                FROM {$tabla}
                GROUP BY estado_plan";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        try {
            if ($this->tablaExiste('bitacora_busqueda')) {
                $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                        VALUES (:usuario_id, 'Planes', :accion, NOW())";
            } elseif ($this->tablaExiste('bitacora_sistema')) {
                $sql = "INSERT INTO bitacora_sistema (id_user, modulo, accion, creado_en)
                        VALUES (:usuario_id, 'Planes', :accion, NOW())";
            } else {
                return true;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':usuario_id', $usuarioId ? (int) $usuarioId : null, $usuarioId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindParam(':accion', $accion);

            return $stmt->execute();
        } catch (PDOException $e) {
            return true;
        }
    }
}

?>
