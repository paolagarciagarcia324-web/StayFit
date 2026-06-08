<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class DatosFisicosModel
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

    public function obtenerPorCliente($clienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT
                        CASE
                            WHEN c.estatura_cm IS NOT NULL AND c.estatura_cm > 0 THEN c.estatura_cm / 100
                            ELSE NULL
                        END AS estatura_m,
                        c.estatura_cm AS estatura,
                        c.peso_inicial_kg AS peso_inicial,
                        c.peso_inicial_kg AS peso,
                        c.objetivo_principal AS objetivos,
                        c.objetivo_principal AS objetivo,
                        '' AS restricciones_medicas,
                        '' AS restricciones,
                        u.nombres AS nombre,
                        u.apellidos AS apellido,
                        u.correo,
                        u.telefono,
                        u.foto_perfil_url AS foto_perfil
                    FROM clientes c
                    INNER JOIN user u ON u.id_user = c.id_user
                    WHERE c.id_cliente = :cliente_id
                    LIMIT 1";
        } else {
            $sql = "SELECT c.estatura_m, c.peso_inicial, c.objetivos, c.restricciones_medicas,
                           c.objetivos AS objetivo, c.restricciones_medicas AS restricciones,
                           u.nombre, u.apellido, u.correo, u.telefono, u.foto_perfil
                    FROM cliente c
                    INNER JOIN users u ON u.id_usuario = c.id_cliente
                    WHERE c.id_cliente = :cliente_id
                    LIMIT 1";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fila && $this->usaEsquemaNuevo()) {
            $fila['estatura'] = $fila['estatura_m'] ?? (
                !empty($fila['estatura']) ? round((float) $fila['estatura'] / 100, 2) : ''
            );
        }

        return $fila;
    }

    private function estaturaACm($estatura): ?float
    {
        if ($estatura === null || $estatura === '') {
            return null;
        }

        $valor = (float) $estatura;

        return $valor < 3 ? round($valor * 100, 2) : $valor;
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE clientes
                    SET estatura_cm = :estatura_cm,
                        peso_inicial_kg = :peso,
                        objetivo_principal = :objetivo
                    WHERE id_cliente = :cliente_id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':estatura_cm', $this->estaturaACm($datos['estatura'] ?? null));
            $stmt->bindValue(':peso', $datos['peso'] ?? null);
            $stmt->bindValue(':objetivo', $datos['objetivo'] ?? $datos['objetivos'] ?? null);
            $stmt->bindParam(':cliente_id', $datos['cliente_id']);

            return $stmt->execute();
        }

        $sql = 'UPDATE cliente
                SET estatura_m = :estatura, peso_inicial = :peso,
                    objetivos = :objetivo, restricciones_medicas = :restricciones
                WHERE id_cliente = :cliente_id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':estatura', $datos['estatura'] ?? null);
        $stmt->bindValue(':peso', $datos['peso'] ?? null);
        $stmt->bindValue(':objetivo', $datos['objetivo'] ?? null);
        $stmt->bindValue(':restricciones', $datos['restricciones'] ?? null);
        $stmt->bindParam(':cliente_id', $datos['cliente_id']);

        return $stmt->execute();
    }

    public function actualizar($datos)
    {
        return $this->crear($datos);
    }

    public function guardarOActualizar($datos)
    {
        $ok = $this->actualizar($datos);

        if ($ok && !empty($datos['peso'])) {
            $this->registrarPesoEnProgreso($datos);
        }

        return $ok;
    }

    private function registrarPesoEnProgreso($datos): void
    {
        $clienteId = $datos['cliente_id'] ?? null;

        if (!$clienteId) {
            return;
        }

        if ($this->usaEsquemaNuevo()) {
            $sql = 'INSERT INTO registros_progreso (id_cliente, fecha_registro, peso_kg, observaciones_cliente)
                    VALUES (:id_cliente, CURDATE(), :peso, :observaciones)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_cliente', $clienteId);
            $stmt->bindValue(':peso', $datos['peso']);
            $stmt->bindValue(':observaciones', $datos['observaciones'] ?? '');
            $stmt->execute();

            return;
        }

        $stmtPlan = $this->db->prepare(
            'SELECT id_plan_cliente FROM plan_cliente WHERE id_cliente = :id ORDER BY id_plan_cliente DESC LIMIT 1'
        );
        $stmtPlan->bindParam(':id', $clienteId);
        $stmtPlan->execute();
        $planClienteId = $stmtPlan->fetchColumn();

        if (!$planClienteId) {
            return;
        }

        $sql = 'INSERT INTO registro_progreso (id_plan_cliente, fecha, peso, observacion_cliente)
                VALUES (:id_plan_cliente, CURDATE(), :peso, :observaciones)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_plan_cliente', $planClienteId, PDO::PARAM_INT);
        $stmt->bindValue(':peso', $datos['peso']);
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? '');
        $stmt->execute();
    }

    public function historialPorCliente($clienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'SELECT rp.*,
                           rp.fecha_registro AS fecha,
                           rp.peso_kg AS peso,
                           rp.cintura_cm AS cintura,
                           rp.cadera_cm AS cadera,
                           rp.brazo_cm AS brazos,
                           rp.pierna_cm AS piernas,
                           rp.foto_url AS fotos_evolucion,
                           rp.observaciones_cliente AS observacion_cliente
                    FROM registros_progreso rp
                    WHERE rp.id_cliente = :cliente_id
                    ORDER BY rp.fecha_registro DESC';
        } else {
            $sql = 'SELECT rp.*
                    FROM registro_progreso rp
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = rp.id_plan_cliente
                    WHERE pc.id_cliente = :cliente_id
                    ORDER BY rp.fecha DESC';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Datos físicos', $accion);
    }
}

?>
