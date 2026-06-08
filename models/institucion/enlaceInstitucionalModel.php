<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';
require_once __DIR__ . '/../../config/helpers.php';

class EnlaceInstitucionalModel
{
    private PDO $db;
    private SchemaHelper $schema;

    public function __construct(?PDO $db = null)
    {
        if ($db instanceof PDO) {
            $this->db = $db;
        } else {
            $this->db = (new Database())->conectar();
        }

        $this->schema = new SchemaHelper($this->db);
    }

    public function tablaExiste(): bool
    {
        return $this->schema->tablaExiste('enlaces_registro_institucional');
    }

    private function generarToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function normalizarFila(?array $fila): ?array
    {
        if (!$fila) {
            return null;
        }

        $fila['id'] = $fila['id_enlace'] ?? $fila['id'] ?? null;
        $fila['activo'] = (int) ($fila['activo'] ?? 0) === 1;
        $fila['url_registro'] = !empty($fila['token']) ? urlRegistroInstitucion($fila['token']) : '';

        return $fila;
    }

    public function obtenerPorToken(string $token): ?array
    {
        if (!$this->tablaExiste() || trim($token) === '') {
            return null;
        }

        try {
            $sql = "SELECT e.*,
                           i.nombre AS institucion_nombre,
                           i.estado AS institucion_estado,
                           p.nombre AS plan_nombre,
                           p.descripcion AS plan_descripcion,
                           p.modalidad AS plan_modalidad,
                           p.duracion_dias AS plan_duracion,
                           p.incluye_entrenamiento,
                           p.incluye_nutricion,
                           p.requiere_coach
                    FROM enlaces_registro_institucional e
                    INNER JOIN instituciones i ON i.id_institucion = e.id_institucion
                    INNER JOIN planes p ON p.id_plan = e.id_plan
                    WHERE e.token = :token
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':token', trim($token));
            $stmt->execute();

            $fila = $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC) ?: null);

            if (!$fila || !$fila['activo']) {
                return null;
            }

            $estadoInst = strtoupper((string) ($fila['institucion_estado'] ?? ''));

            if ($estadoInst !== 'ACTIVA') {
                return null;
            }

            return $fila;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function obtenerPorInstitucion(int $idInstitucion): ?array
    {
        if (!$this->tablaExiste() || $idInstitucion <= 0) {
            return null;
        }

        try {
            $sql = "SELECT e.*,
                           i.nombre AS institucion_nombre,
                           p.nombre AS plan_nombre
                    FROM enlaces_registro_institucional e
                    INNER JOIN instituciones i ON i.id_institucion = e.id_institucion
                    INNER JOIN planes p ON p.id_plan = e.id_plan
                    WHERE e.id_institucion = :id_institucion
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_institucion', $idInstitucion, PDO::PARAM_INT);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->normalizarFila($fila ?: null);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function obtenerTodosConDetalle(): array
    {
        if (!$this->tablaExiste()) {
            return [];
        }

        try {
            $sql = "SELECT e.*,
                           i.nombre AS institucion_nombre,
                           i.estado AS institucion_estado,
                           p.nombre AS plan_nombre
                    FROM enlaces_registro_institucional e
                    INNER JOIN instituciones i ON i.id_institucion = e.id_institucion
                    INNER JOIN planes p ON p.id_plan = e.id_plan
                    ORDER BY i.nombre ASC";

            $stmt = $this->db->query($sql);
            $lista = [];

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $lista[] = $this->normalizarFila($fila);
            }

            return $lista;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function sincronizarEnlace(int $idInstitucion, int $idPlan, ?int $adminUserId = null, bool $regenerarToken = false): ?array
    {
        if (!$this->tablaExiste() || $idInstitucion <= 0 || $idPlan <= 0) {
            return null;
        }

        $existente = $this->obtenerPorInstitucion($idInstitucion);

        try {
            if ($existente) {
                if ($regenerarToken) {
                    $token = $this->generarToken();
                    $sql = "UPDATE enlaces_registro_institucional
                            SET id_plan = :id_plan,
                                token = :token,
                                activo = 1,
                                creado_por = COALESCE(:creado_por, creado_por),
                                actualizado_en = NOW()
                            WHERE id_institucion = :id_institucion";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindValue(':id_plan', $idPlan, PDO::PARAM_INT);
                    $stmt->bindValue(':token', $token);
                } else {
                    $sql = "UPDATE enlaces_registro_institucional
                            SET id_plan = :id_plan,
                                activo = 1,
                                actualizado_en = NOW()
                            WHERE id_institucion = :id_institucion";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindValue(':id_plan', $idPlan, PDO::PARAM_INT);
                }

                $stmt->bindValue(':creado_por', $adminUserId, $adminUserId ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->bindValue(':id_institucion', $idInstitucion, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $token = $this->generarToken();
                $sql = "INSERT INTO enlaces_registro_institucional
                        (id_institucion, id_plan, token, activo, creado_por)
                        VALUES
                        (:id_institucion, :id_plan, :token, 1, :creado_por)";

                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id_institucion', $idInstitucion, PDO::PARAM_INT);
                $stmt->bindValue(':id_plan', $idPlan, PDO::PARAM_INT);
                $stmt->bindValue(':token', $token);
                $stmt->bindValue(':creado_por', $adminUserId, $adminUserId ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->execute();
            }

            return $this->obtenerPorInstitucion($idInstitucion);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function generarOActualizar(int $idInstitucion, int $idPlan, ?int $adminUserId = null): ?array
    {
        return $this->sincronizarEnlace($idInstitucion, $idPlan, $adminUserId, true);
    }

    public function actualizarPlanEnlace(int $idInstitucion, int $idPlan, ?int $adminUserId = null): ?array
    {
        return $this->sincronizarEnlace($idInstitucion, $idPlan, $adminUserId, false);
    }

    public function regenerarToken(int $idEnlace, ?int $adminUserId = null, ?int $idPlan = null): ?array
    {
        if (!$this->tablaExiste() || $idEnlace <= 0) {
            return null;
        }

        $token = $this->generarToken();

        try {
            if ($idPlan !== null && $idPlan > 0) {
                $sql = "UPDATE enlaces_registro_institucional
                        SET token = :token,
                            id_plan = :id_plan,
                            activo = 1,
                            creado_por = COALESCE(:creado_por, creado_por),
                            actualizado_en = NOW()
                        WHERE id_enlace = :id_enlace";
            } else {
                $sql = "UPDATE enlaces_registro_institucional
                        SET token = :token,
                            activo = 1,
                            creado_por = COALESCE(:creado_por, creado_por),
                            actualizado_en = NOW()
                        WHERE id_enlace = :id_enlace";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':token', $token);
            $stmt->bindValue(':creado_por', $adminUserId, $adminUserId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':id_enlace', $idEnlace, PDO::PARAM_INT);
            if ($idPlan !== null && $idPlan > 0) {
                $stmt->bindValue(':id_plan', $idPlan, PDO::PARAM_INT);
            }
            $stmt->execute();

            $sqlGet = "SELECT e.*, i.nombre AS institucion_nombre, p.nombre AS plan_nombre
                       FROM enlaces_registro_institucional e
                       INNER JOIN instituciones i ON i.id_institucion = e.id_institucion
                       INNER JOIN planes p ON p.id_plan = e.id_plan
                       WHERE e.id_enlace = :id_enlace LIMIT 1";
            $stmtGet = $this->db->prepare($sqlGet);
            $stmtGet->bindValue(':id_enlace', $idEnlace, PDO::PARAM_INT);
            $stmtGet->execute();

            $fila = $stmtGet->fetch(PDO::FETCH_ASSOC);

            return $this->normalizarFila($fila ?: null);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function activarDesactivar(int $idEnlace, bool $activo): bool
    {
        if (!$this->tablaExiste() || $idEnlace <= 0) {
            return false;
        }

        try {
            $sql = 'UPDATE enlaces_registro_institucional SET activo = :activo WHERE id_enlace = :id_enlace';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':activo', $activo ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':id_enlace', $idEnlace, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function incrementarRegistro(int $idEnlace): bool
    {
        if (!$this->tablaExiste() || $idEnlace <= 0) {
            return false;
        }

        try {
            $sql = 'UPDATE enlaces_registro_institucional
                    SET registros_realizados = registros_realizados + 1
                    WHERE id_enlace = :id_enlace';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_enlace', $idEnlace, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function registrarTrazabilidad(?int $usuarioId, string $accion): bool
    {
        return registrarBitacora($this->db, $usuarioId, 'Enlace institucional', $accion);
    }
}

?>
