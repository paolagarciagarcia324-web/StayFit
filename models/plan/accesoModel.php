<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class AccesoModel
{
    private $db;
    private SchemaHelper $schema;

    public function __construct()
    {
        $this->db = (new Database())->conectar();
        $this->schema = new SchemaHelper($this->db);
    }

    private function usaEsquemaNuevo(): bool
    {
        return $this->schema->usaEsquemaNuevo();
    }

    private function sqlListadoPorCliente(bool $soloActivos = false): string
    {
        if ($this->usaEsquemaNuevo()) {
            $filtro = $soloActivos ? " AND acm.estado_acceso = 'ACTIVO'" : '';

            return "SELECT acm.id_acceso AS id,
                           acm.id_plan_cliente,
                           acm.estado_acceso,
                           acm.fecha_inicio AS fecha_habilitacion,
                           acm.fecha_fin AS fecha_expiracion,
                           ms.nombre AS modulo,
                           CASE WHEN acm.estado_acceso = 'ACTIVO' THEN 'activo' ELSE 'inactivo' END AS estado,
                           CASE WHEN acm.estado_acceso = 'ACTIVO' THEN 1 ELSE 0 END AS habilitado
                    FROM acceso_cliente_modulo acm
                    INNER JOIN planes_cliente pc ON pc.id_plan_cliente = acm.id_plan_cliente
                    INNER JOIN modulos_servicio ms ON ms.id_modulo = acm.id_modulo
                    WHERE pc.id_cliente = :cliente_id{$filtro}
                    ORDER BY acm.id_acceso DESC";
        }

        $filtro = $soloActivos ? ' AND acm.habilitado = 1' : '';

        return "SELECT acm.id_acceso_cliente_modulo AS id,
                       acm.id_plan_cliente,
                       acm.habilitado,
                       acm.fecha_habilitacion,
                       acm.fecha_expiracion,
                       ms.nombre AS modulo,
                       CASE WHEN acm.habilitado = 1 THEN 'activo' ELSE 'inactivo' END AS estado
                FROM acceso_cliente_modulo acm
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = acm.id_plan_cliente
                INNER JOIN modulo_servicio ms ON ms.id_modulo_servicio = acm.id_modulo_servicio
                WHERE pc.id_cliente = :cliente_id{$filtro}
                ORDER BY acm.id_acceso_cliente_modulo DESC";
    }

    public function obtenerPorCliente($clienteId)
    {
        $stmt = $this->db->prepare($this->sqlListadoPorCliente());
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerActivosPorCliente($clienteId)
    {
        $stmt = $this->db->prepare($this->sqlListadoPorCliente(true));
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'INSERT INTO acceso_cliente_modulo
                    (id_plan_cliente, id_cliente, id_modulo, fecha_inicio, fecha_fin, estado_acceso)
                    VALUES
                    (:id_plan_cliente, :id_cliente, :id_modulo, :fecha_inicio, :fecha_fin, :estado_acceso)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']);
            $stmt->bindValue(':id_cliente', $datos['cliente_id'] ?? $datos['id_cliente']);
            $stmt->bindValue(':id_modulo', $datos['id_modulo_servicio'] ?? $datos['id_modulo']);
            $stmt->bindValue(':fecha_inicio', $datos['fecha_habilitacion'] ?? date('Y-m-d'));
            $stmt->bindValue(':fecha_fin', $datos['fecha_expiracion'] ?? date('Y-m-d', strtotime('+30 days')));
            $stmt->bindValue(':estado_acceso', !empty($datos['habilitado']) ? 'ACTIVO' : 'INACTIVO');

            return $stmt->execute();
        }

        $sql = 'INSERT INTO acceso_cliente_modulo
                (id_plan_cliente, id_modulo_servicio, habilitado, fecha_habilitacion, fecha_expiracion)
                VALUES
                (:id_plan_cliente, :id_modulo_servicio, :habilitado, :fecha_habilitacion, :fecha_expiracion)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']);
        $stmt->bindParam(':id_modulo_servicio', $datos['id_modulo_servicio']);
        $stmt->bindValue(':habilitado', $datos['habilitado'] ?? 1);
        $stmt->bindValue(':fecha_habilitacion', $datos['fecha_habilitacion'] ?? date('Y-m-d H:i:s'));
        $stmt->bindParam(':fecha_expiracion', $datos['fecha_expiracion']);

        return $stmt->execute();
    }

    public function crearAccesosPorPlan($clienteId, $plan)
    {
        $fechaInicio = date('Y-m-d');
        $fechaFin = date('Y-m-d', strtotime('+' . ($plan['duracion'] ?? $plan['duracion_dias'] ?? 30) . ' days'));

        $modulos = ['perfil', 'plan', 'progreso', 'calendario', 'pagos'];

        if (($plan['incluye_entrenamiento'] ?? 0) == 1) {
            $modulos[] = 'entrenamiento';
        }

        if (($plan['incluye_nutricion'] ?? 0) == 1) {
            $modulos[] = 'nutricion';
        }

        $modalidad = strtolower($plan['modalidad'] ?? '');
        if (in_array($modalidad, ['virtual', 'mixta', 'mixto'], true)) {
            $modulos[] = 'contenido_virtual';
        }

        if (($plan['requiere_coach'] ?? 0) == 1) {
            $modulos[] = 'comunicacion';
        }

        foreach ($modulos as $modulo) {
            $this->crear([
                'cliente_id' => $clienteId,
                'id_plan_cliente' => $plan['id'] ?? $plan['id_plan_cliente'],
                'id_modulo_servicio' => $this->obtenerIdModulo($modulo),
                'habilitado' => 1,
                'fecha_habilitacion' => $fechaInicio,
                'fecha_expiracion' => $fechaFin,
            ]);
        }

        return true;
    }

    public function cambiarEstado($id, $estado)
    {
        if ($this->usaEsquemaNuevo()) {
            $valor = ($estado === 'activo' || $estado === 1) ? 'ACTIVO' : 'INACTIVO';
            $sql = 'UPDATE acceso_cliente_modulo SET estado_acceso = :estado WHERE id_acceso = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado', $valor);
        } else {
            $habilitado = ($estado === 'activo' || $estado === 1) ? 1 : 0;
            $sql = 'UPDATE acceso_cliente_modulo SET habilitado = :habilitado WHERE id_acceso_cliente_modulo = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':habilitado', $habilitado);
        }

        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function vencerAccesos()
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "UPDATE acceso_cliente_modulo
                    SET estado_acceso = 'VENCIDO'
                    WHERE fecha_fin < CURDATE()
                    AND estado_acceso = 'ACTIVO'";
        } else {
            $sql = 'UPDATE acceso_cliente_modulo
                    SET habilitado = 0
                    WHERE fecha_expiracion < NOW()
                    AND habilitado = 1';
        }

        $stmt = $this->db->prepare($sql);

        return $stmt->execute();
    }

    public function contarVencidos()
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT COUNT(*) AS total FROM acceso_cliente_modulo
                    WHERE estado_acceso IN ('VENCIDO','INACTIVO') AND fecha_fin < CURDATE()";
        } else {
            $sql = 'SELECT COUNT(*) AS total FROM acceso_cliente_modulo
                    WHERE habilitado = 0 AND fecha_expiracion < NOW()';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function obtenerIdModulo($nombreModulo)
    {
        $mapa = [
            'entrenamiento' => 1,
            'nutricion' => 2,
            'contenido_virtual' => 3,
            'sesiones' => 4,
            'acompanamiento' => 5,
            'comunicacion' => 5,
            'perfil' => 1,
            'plan' => 1,
            'progreso' => 1,
            'calendario' => 4,
            'pagos' => 1,
        ];

        if (isset($mapa[$nombreModulo])) {
            return $mapa[$nombreModulo];
        }

        $tabla = $this->usaEsquemaNuevo() ? 'modulos_servicio' : 'modulo_servicio';
        $colId = $this->usaEsquemaNuevo() ? 'id_modulo' : 'id_modulo_servicio';
        $sql = "SELECT {$colId} FROM {$tabla} WHERE LOWER(nombre) LIKE :nombre OR LOWER(codigo) LIKE :codigo LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $busqueda = '%' . strtolower(str_replace('_', '', $nombreModulo)) . '%';
        $stmt->bindValue(':nombre', $busqueda);
        $stmt->bindValue(':codigo', $busqueda);
        $stmt->execute();

        return $stmt->fetchColumn() ?: 1;
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Accesos', $accion);
    }
}

?>
