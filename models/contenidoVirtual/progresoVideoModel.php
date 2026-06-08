<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class ProgresoVideoModel
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
        return $this->schema->tablaExiste('progreso_videos');
    }

    private function tabla(): string
    {
        return $this->usaEsquemaNuevo() ? 'progreso_videos' : 'progreso_video';
    }

    private function normalizarFila(array $fila): array
    {
        $estado = strtoupper((string) ($fila['estado'] ?? $fila['estado_progreso'] ?? ''));
        $porcentaje = (int) round((float) ($fila['porcentaje_avance'] ?? $fila['avance'] ?? 0));

        if ($porcentaje === 0 && $estado === 'COMPLETADO') {
            $porcentaje = 100;
        }

        $fila['avance'] = $porcentaje;

        if (!isset($fila['estado']) && isset($fila['estado_progreso'])) {
            $fila['estado'] = $fila['estado_progreso'];
        }

        return $fila;
    }

    public function obtenerPorCliente($clienteId)
    {
        try {
            if ($this->usaEsquemaNuevo()) {
                $sql = "SELECT pv.*, v.titulo, pv.estado_progreso AS estado, pv.completado_en AS ultimo_acceso
                        FROM progreso_videos pv
                        INNER JOIN videos v ON v.id_video = pv.id_video
                        WHERE pv.id_cliente = :cliente_id
                        ORDER BY pv.actualizado_en DESC";
            } else {
                $sql = "SELECT pv.*, v.titulo
                        FROM progreso_video pv
                        INNER JOIN video v ON v.id_video = pv.id_video
                        WHERE pv.id_cliente = :cliente_id
                        ORDER BY pv.ultimo_acceso DESC";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId);
            $stmt->execute();

            return array_map(fn(array $fila) => $this->normalizarFila($fila), $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorCoach($coachId)
    {
        try {
            if ($this->usaEsquemaNuevo()) {
                $sql = "SELECT CONCAT(u.nombres, ' ', IFNULL(u.apellidos, '')) AS cliente, pv.*,
                               pv.estado_progreso AS estado, pv.completado_en AS ultimo_acceso
                        FROM progreso_videos pv
                        INNER JOIN clientes c ON c.id_cliente = pv.id_cliente
                        INNER JOIN user u ON u.id_user = c.id_user
                        WHERE c.id_coach = :coach_id
                        ORDER BY pv.actualizado_en DESC";
            } else {
                $sql = "SELECT u.nombre AS cliente, pv.*
                        FROM progreso_video pv
                        INNER JOIN users u ON u.id_usuario = pv.id_cliente
                        INNER JOIN plan_cliente pc ON pc.id_cliente = pv.id_cliente
                        WHERE pc.id_coach = :coach_id
                        ORDER BY pv.ultimo_acceso DESC";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':coach_id', $coachId);
            $stmt->execute();

            return array_map(fn(array $fila) => $this->normalizarFila($fila), $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerResumenPorCoach($coachId)
    {
        try {
            if ($this->usaEsquemaNuevo()) {
                $sql = "SELECT DISTINCT c.id_cliente,
                               TRIM(CONCAT(u.nombres, ' ', IFNULL(u.apellidos, ''))) AS cliente
                        FROM clientes c
                        INNER JOIN user u ON u.id_user = c.id_user
                        INNER JOIN progreso_videos pv ON pv.id_cliente = c.id_cliente
                        WHERE c.id_coach = :coach_id
                        ORDER BY cliente ASC";
            } else {
                $sql = "SELECT DISTINCT u.id_usuario AS id_cliente, u.nombre AS cliente
                        FROM progreso_video pv
                        INNER JOIN users u ON u.id_usuario = pv.id_cliente
                        INNER JOIN plan_cliente pc ON pc.id_cliente = pv.id_cliente
                        WHERE pc.id_coach = :coach_id
                        ORDER BY u.nombre ASC";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':coach_id', $coachId);
            $stmt->execute();
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resumen = [];
            foreach ($filas as $fila) {
                $clienteId = (int) ($fila['id_cliente'] ?? 0);
                if ($clienteId <= 0) {
                    continue;
                }

                $avance = $this->obtenerAvanceCliente($clienteId);
                $resumen[] = [
                    'id_cliente' => $clienteId,
                    'cliente' => trim((string) ($fila['cliente'] ?? 'Clienta')),
                    'avance' => $avance,
                    'estado' => $avance >= 100 ? 'COMPLETADO' : ($avance > 0 ? 'EN PROGRESO' : 'SIN INICIAR'),
                ];
            }

            return $resumen;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerAvanceCliente($clienteId)
    {
        try {
            if ($this->usaEsquemaNuevo()) {
                $sqlTotal = "SELECT COUNT(DISTINCT v.id_video) AS total
                             FROM videos v
                             INNER JOIN programas_virtuales pv ON pv.id_programa = v.id_programa
                             INNER JOIN planes pl ON pl.id_plan = pv.id_plan
                             INNER JOIN planes_cliente pc ON pc.id_plan = pl.id_plan AND pc.id_cliente = :cliente_id
                             WHERE pc.estado_plan_cliente = 'ACTIVO'
                               AND v.estado_video = 'ACTIVO'
                               AND pv.estado_programa = 'ACTIVO'";

                $sqlVistos = "SELECT COUNT(*) AS vistos
                              FROM progreso_videos
                              WHERE id_cliente = :cliente_id
                                AND estado_progreso = 'COMPLETADO'";
            } else {
                $sqlTotal = "SELECT COUNT(DISTINCT v.id_video) AS total
                             FROM video v
                             INNER JOIN programa_virtual pv ON pv.id_programa_virtual = v.id_programa_virtual
                             INNER JOIN plan pl ON pl.id_plan = pv.id_plan
                             INNER JOIN plan_cliente pc ON pc.id_plan = pl.id_plan
                             WHERE pc.id_cliente = :cliente_id AND pc.estado = 'ACTIVO'";

                $sqlVistos = "SELECT COUNT(*) AS vistos
                              FROM progreso_video
                              WHERE id_cliente = :cliente_id AND estado = 'COMPLETADO'";
            }

            $stmtTotal = $this->db->prepare($sqlTotal);
            $stmtTotal->bindParam(':cliente_id', $clienteId);
            $stmtTotal->execute();
            $total = (int) ($stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

            if ($total === 0) {
                return 0;
            }

            $stmtVistos = $this->db->prepare($sqlVistos);
            $stmtVistos->bindParam(':cliente_id', $clienteId);
            $stmtVistos->execute();
            $vistos = (int) ($stmtVistos->fetch(PDO::FETCH_ASSOC)['vistos'] ?? 0);

            return (int) round(($vistos / $total) * 100);
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function marcarVisto($clienteId, $videoId)
    {
        try {
            $existe = $this->obtenerRegistro($clienteId, $videoId);

            if ($existe) {
                return $this->actualizarEstado($clienteId, $videoId, 'COMPLETADO');
            }

            if ($this->usaEsquemaNuevo()) {
                $sql = "INSERT INTO progreso_videos
                        (id_cliente, id_video, estado_progreso, porcentaje_avance, iniciado_en, completado_en)
                        VALUES
                        (:id_cliente, :id_video, 'COMPLETADO', 100, NOW(), NOW())";
            } else {
                $sql = "INSERT INTO progreso_video
                        (id_cliente, id_video, estado, porcentaje_avance, fecha_inicio, ultimo_acceso)
                        VALUES
                        (:id_cliente, :id_video, 'COMPLETADO', 100, NOW(), NOW())";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_cliente', $clienteId);
            $stmt->bindParam(':id_video', $videoId);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    private function obtenerRegistro($clienteId, $videoId)
    {
        try {
            $tabla = $this->tabla();
            $sql = "SELECT * FROM {$tabla}
                    WHERE id_cliente = :cliente_id AND id_video = :video_id
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId);
            $stmt->bindParam(':video_id', $videoId);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function actualizarEstado($clienteId, $videoId, $estado)
    {
        try {
            $estadoNorm = strtoupper($estado);
            $avance = $estadoNorm === 'COMPLETADO' ? 100 : 0;

            if ($this->usaEsquemaNuevo()) {
                $estadoDb = match ($estadoNorm) {
                    'COMPLETADO' => 'COMPLETADO',
                    'EN_PROGRESO' => 'EN_PROGRESO',
                    default => 'NO_INICIADO',
                };

                $sql = "UPDATE progreso_videos
                        SET estado_progreso = :estado,
                            porcentaje_avance = :porcentaje_avance,
                            completado_en = CASE WHEN :estado_cmp = 'COMPLETADO' THEN NOW() ELSE completado_en END,
                            iniciado_en = COALESCE(iniciado_en, NOW())
                        WHERE id_cliente = :cliente_id AND id_video = :video_id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':estado', $estadoDb);
                $stmt->bindValue(':estado_cmp', $estadoDb);
            } else {
                $sql = "UPDATE progreso_video
                        SET estado = :estado,
                            porcentaje_avance = :porcentaje_avance,
                            ultimo_acceso = NOW(),
                            fecha_finalizacion = CASE WHEN :estado = 'COMPLETADO' THEN NOW() ELSE fecha_finalizacion END
                        WHERE id_cliente = :cliente_id AND id_video = :video_id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':estado', $estadoNorm);
            }

            $stmt->bindValue(':porcentaje_avance', $avance);
            $stmt->bindParam(':cliente_id', $clienteId);
            $stmt->bindParam(':video_id', $videoId);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function guardarObservacion($datos)
    {
        try {
            if ($this->usaEsquemaNuevo()) {
                $sql = "INSERT INTO progreso_videos
                        (id_cliente, id_video, estado_progreso, porcentaje_avance, iniciado_en)
                        VALUES
                        (:id_cliente, :id_video, 'EN_PROGRESO', 0, NOW())
                        ON DUPLICATE KEY UPDATE
                        estado_progreso = 'EN_PROGRESO', iniciado_en = COALESCE(iniciado_en, NOW())";
            } else {
                $sql = "INSERT INTO progreso_video
                        (id_cliente, id_video, estado, porcentaje_avance, ultimo_acceso)
                        VALUES
                        (:id_cliente, :id_video, 'EN_PROGRESO', 0, NOW())";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_cliente', $datos['id_cliente']);
            $stmt->bindValue(':id_video', $datos['id_video'] ?? null, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Progreso videos', $accion);
    }
}

?>
