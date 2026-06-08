<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/schemaHelper.php';

class ComidaModel
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
        return $this->schema->tablaExiste('comidas');
    }

    private function tabla(): string
    {
        return $this->usaEsquemaNuevo() ? 'comidas' : 'comida';
    }

    private function normalizarFila($fila)
    {
        if (!$fila) {
            return false;
        }

        $fila['id'] = $fila['id_comida'] ?? $fila['id'] ?? null;
        $fila['nombre'] = $fila['nombre'] ?? $fila['tiempo_comida'] ?? 'Comida';
        $fila['descripcion'] = $fila['descripcion'] ?? $fila['grupos_alimenticios'] ?? '';
        $fila['hora'] = $fila['hora_sugerida'] ?? $fila['hora'] ?? '';
        $fila['calorias'] = $fila['calorias_aprox'] ?? $fila['calorias'] ?? null;
        $fila['tiempo_comida'] = $fila['tipo_comida'] ?? $fila['tiempo_comida'] ?? $fila['nombre'] ?? '';

        return $fila;
    }

    public function obtenerTodas()
    {
        $tabla = $this->tabla();
        $orden = $this->usaEsquemaNuevo() ? 'orden ASC, id_comida ASC' : 'id_comida DESC';
        $sql = "SELECT * FROM {$tabla} ORDER BY {$orden}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPorId($id)
    {
        $tabla = $this->tabla();
        $sql = "SELECT * FROM {$tabla} WHERE id_comida = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function obtenerPorPlan($planNutricionalId)
    {
        $tabla = $this->tabla();
        $orden = $this->usaEsquemaNuevo()
            ? 'orden ASC, id_comida ASC'
            : 'tiempo_comida ASC, id_comida ASC';

        $sql = "SELECT * FROM {$tabla}
                WHERE id_plan_nutricional = :plan_nutricional_id
                ORDER BY {$orden}";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':plan_nutricional_id', $planNutricionalId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPorCliente($clienteId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT c.*
                    FROM comidas c
                    INNER JOIN planes_nutricionales pn ON pn.id_plan_nutricional = c.id_plan_nutricional
                    WHERE pn.id_cliente = :cliente_id
                      AND pn.estado_nutricional = 'ACTIVO'
                    ORDER BY c.orden ASC, c.id_comida ASC";
        } else {
            $sql = "SELECT c.*
                    FROM comida c
                    INNER JOIN plan_nutricional pn ON pn.id_plan_nutricional = c.id_plan_nutricional
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                    WHERE pc.id_cliente = :cliente_id
                      AND pn.estado_plan = 'ACTIVO'
                    ORDER BY c.tiempo_comida ASC, c.id_comida ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function obtenerPorCoach($coachId)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = "SELECT c.*, pn.titulo AS plan_nutricional
                    FROM comidas c
                    INNER JOIN planes_nutricionales pn ON pn.id_plan_nutricional = c.id_plan_nutricional
                    WHERE pn.id_coach = :coach_id
                    ORDER BY c.id_comida DESC";
        } else {
            $sql = "SELECT c.*, pn.nombre AS plan_nutricional
                    FROM comida c
                    INNER JOIN plan_nutricional pn ON pn.id_plan_nutricional = c.id_plan_nutricional
                    INNER JOIN plan_cliente pc ON pc.id_plan_cliente = pn.id_plan_cliente
                    WHERE pc.id_coach = :coach_id
                    ORDER BY c.id_comida DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();

        $lista = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $lista[] = $this->normalizarFila($fila);
        }

        return $lista;
    }

    public function crear($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'INSERT INTO comidas
                    (id_plan_nutricional, tipo_comida, nombre, descripcion, hora_sugerida, calorias_aprox, orden)
                    VALUES
                    (:id_plan_nutricional, :tipo_comida, :nombre, :descripcion, :hora_sugerida, :calorias_aprox, :orden)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_plan_nutricional', $datos['id_plan_nutricional'] ?? $datos['plan_nutricional_id']);
            $stmt->bindValue(':tipo_comida', strtoupper($datos['tipo_comida'] ?? $datos['tiempo_comida'] ?? 'OTRO'));
            $stmt->bindValue(':nombre', $datos['nombre'] ?? $datos['tiempo_comida'] ?? 'Comida');
            $stmt->bindValue(':descripcion', $datos['descripcion'] ?? $datos['grupos_alimenticios'] ?? '');
            $stmt->bindValue(':hora_sugerida', $datos['hora_sugerida'] ?? $datos['hora'] ?? null);
            $stmt->bindValue(':calorias_aprox', $datos['calorias_aprox'] ?? $datos['calorias'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':orden', (int) ($datos['orden'] ?? 1), PDO::PARAM_INT);

            return $stmt->execute();
        }

        $sql = 'INSERT INTO comida
                (id_plan_nutricional, tiempo_comida, grupos_alimenticios, porciones, calorias_aprox, observaciones)
                VALUES
                (:id_plan_nutricional, :tiempo_comida, :grupos_alimenticios, :porciones, :calorias_aprox, :observaciones)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_plan_nutricional', $datos['id_plan_nutricional'] ?? $datos['plan_nutricional_id']);
        $stmt->bindValue(':tiempo_comida', $datos['tiempo_comida'] ?? $datos['nombre'] ?? null);
        $stmt->bindValue(':grupos_alimenticios', $datos['grupos_alimenticios'] ?? $datos['descripcion'] ?? null);
        $stmt->bindValue(':porciones', $datos['porciones'] ?? null);
        $stmt->bindValue(':calorias_aprox', $datos['calorias_aprox'] ?? $datos['calorias'] ?? null);
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? null);

        return $stmt->execute();
    }

    public function actualizar($datos)
    {
        if ($this->usaEsquemaNuevo()) {
            $sql = 'UPDATE comidas
                    SET tipo_comida = :tipo_comida, nombre = :nombre, descripcion = :descripcion,
                        hora_sugerida = :hora_sugerida, calorias_aprox = :calorias_aprox, orden = :orden
                    WHERE id_comida = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':tipo_comida', strtoupper($datos['tipo_comida'] ?? $datos['tiempo_comida'] ?? 'OTRO'));
            $stmt->bindValue(':nombre', $datos['nombre'] ?? $datos['tiempo_comida'] ?? '');
            $stmt->bindValue(':descripcion', $datos['descripcion'] ?? $datos['grupos_alimenticios'] ?? '');
            $stmt->bindValue(':hora_sugerida', $datos['hora_sugerida'] ?? $datos['hora'] ?? null);
            $stmt->bindValue(':calorias_aprox', $datos['calorias_aprox'] ?? $datos['calorias'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':orden', (int) ($datos['orden'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':id', $datos['id'] ?? $datos['id_comida'], PDO::PARAM_INT);

            return $stmt->execute();
        }

        $sql = 'UPDATE comida
                SET tiempo_comida = :tiempo_comida, grupos_alimenticios = :grupos_alimenticios,
                    porciones = :porciones, calorias_aprox = :calorias_aprox, observaciones = :observaciones
                WHERE id_comida = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tiempo_comida', $datos['tiempo_comida'] ?? $datos['nombre'] ?? null);
        $stmt->bindValue(':grupos_alimenticios', $datos['grupos_alimenticios'] ?? $datos['descripcion'] ?? null);
        $stmt->bindValue(':porciones', $datos['porciones'] ?? null);
        $stmt->bindValue(':calorias_aprox', $datos['calorias_aprox'] ?? $datos['calorias'] ?? null);
        $stmt->bindValue(':observaciones', $datos['observaciones'] ?? null);
        $stmt->bindParam(':id', $datos['id'] ?? $datos['id_comida']);

        return $stmt->execute();
    }

    public function eliminar($id)
    {
        $tabla = $this->tabla();
        $sql = "DELETE FROM {$tabla} WHERE id_comida = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        return registrarBitacora($this->db, $usuarioId ? (int) $usuarioId : null, 'Comidas', $accion);
    }
}

?>
