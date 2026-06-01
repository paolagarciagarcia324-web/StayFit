<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class PlanModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM plan ORDER BY id_plan DESC"; // Consulta planes
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        $lista = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene filas

        foreach ($lista as &$fila) { // Normaliza para vistas
            $fila['id'] = $fila['id_plan']; // ID para enlaces
            $fila['estado'] = strtolower($fila['estado_plan'] ?? 'activo'); // Estado legible
        }

        return $lista; // Retorna lista
    }

    public function obtenerActivos()
    {
        $sql = "SELECT * FROM planes 
                WHERE estado_plan = 'ACTIVO'
                ORDER BY precio ASC"; // Consulta planes activos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($lista as &$fila) {
            $fila['id'] = $fila['id_plan'] ?? $fila['id'] ?? null;
        }

        return $lista; // Retorna activos
    }

    public function obtenerPlanesVirtuales()
    {
        $sql = "SELECT * FROM plan
                WHERE estado_plan = 'ACTIVO'
                AND modalidad IN ('VIRTUAL', 'MIXTA')
                ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($lista as &$fila) {
            $fila['id'] = $fila['id_plan'];
        }

        return $lista;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM plan WHERE id_plan = :id LIMIT 1"; // Busca plan
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna plan
    }

    public function obtenerPlanActivoCliente($clienteId)
    {
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
        $stmt->bindParam(':cliente_id', $clienteId);
        $stmt->execute();

        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($plan) {
            $plan['duracion'] = $plan['duracion_dias'] ?? null;
        }

        return $plan ?: null;
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO plan 
                (nombre, descripcion, precio, duracion_dias, dias_previos_recordatorio_default, estado_plan)
                VALUES
                (:nombre, :descripcion, :precio, :duracion_dias, :dias_previos_recordatorio_default, :estado_plan)"; // Crea plan

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':descripcion', $datos['descripcion']); // Descripción
        $stmt->bindParam(':precio', $datos['precio']); // Precio
        $stmt->bindValue(':duracion_dias', $datos['duracion_dias'] ?? null); // Duración en días
        $stmt->bindValue(':dias_previos_recordatorio_default', $datos['dias_previos_recordatorio_default'] ?? 5); // Días recordatorio
        $stmt->bindValue(':estado_plan', $datos['estado_plan'] ?? 'ACTIVO'); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE plan 
                SET nombre = :nombre,
                    descripcion = :descripcion,
                    precio = :precio,
                    duracion_dias = :duracion_dias,
                    dias_previos_recordatorio_default = :dias_previos_recordatorio_default,
                    estado_plan = :estado_plan
                WHERE id_plan = :id_plan"; // Actualiza plan

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':descripcion', $datos['descripcion']); // Descripción
        $stmt->bindParam(':precio', $datos['precio']); // Precio
        $stmt->bindValue(':duracion_dias', $datos['duracion_dias'] ?? null); // Duración en días
        $stmt->bindValue(':dias_previos_recordatorio_default', $datos['dias_previos_recordatorio_default'] ?? 5); // Días recordatorio
        $stmt->bindParam(':estado_plan', $datos['estado_plan']); // Estado
        $stmt->bindParam(':id_plan', $datos['id_plan']); // ID plan

        return $stmt->execute(); // Ejecuta actualización
    }

    public function cambiarEstado($id, $estado)
    {
        $mapa = ['activo' => 'ACTIVO', 'inactivo' => 'INACTIVO']; // Estados ENUM
        $estadoBd = $mapa[strtolower($estado)] ?? strtoupper($estado); // Estado BD

        $sql = "UPDATE plan SET estado_plan = :estado WHERE id_plan = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estadoBd); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID plan

        return $stmt->execute(); // Ejecuta cambio
    }

    public function obtenerUltimoId()
    {
        return $this->db->lastInsertId(); // Retorna último ID insertado
    }

    public function contar()
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM plan')->fetchColumn();
    }

    public function asegurarPlanesBase()
    {
        if ($this->contar() > 0) {
            $fila = $this->db->query('SELECT id_plan FROM plan ORDER BY id_plan ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);

            return $fila ? (int) $fila['id_plan'] : null;
        }

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
                'descripcion' => 'Coach asignado, entrenamiento y nutrición.',
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
                'modalidad' => 'MIXTA',
                'requiere_coach' => 1,
                'incluye_entrenamiento' => 1,
                'incluye_nutricion' => 1,
                'incluye_videos' => 1,
                'incluye_sesiones' => 1,
            ],
        ];

        $primerId = null;

        foreach ($planes as $plan) {
            $sql = "INSERT INTO plan
                    (nombre, descripcion, precio, duracion_dias, dias_previos_recordatorio_default,
                     estado_plan, modalidad, requiere_coach, incluye_entrenamiento, incluye_nutricion,
                     incluye_videos, incluye_sesiones)
                    VALUES
                    (:nombre, :descripcion, :precio, :duracion_dias, 5, 'ACTIVO',
                     :modalidad, :requiere_coach, :incluye_entrenamiento, :incluye_nutricion,
                     :incluye_videos, :incluye_sesiones)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $plan['nombre'],
                ':descripcion' => $plan['descripcion'],
                ':precio' => $plan['precio'],
                ':duracion_dias' => $plan['duracion_dias'],
                ':modalidad' => $plan['modalidad'],
                ':requiere_coach' => $plan['requiere_coach'],
                ':incluye_entrenamiento' => $plan['incluye_entrenamiento'],
                ':incluye_nutricion' => $plan['incluye_nutricion'],
                ':incluye_videos' => $plan['incluye_videos'],
                ':incluye_sesiones' => $plan['incluye_sesiones'],
            ]);

            if ($primerId === null) {
                $primerId = (int) $this->db->lastInsertId();
            }
        }

        return $primerId;
    }

    public function reporteGeneral()
    {
        $sql = "SELECT estado_plan, COUNT(*) AS total
                FROM plan
                GROUP BY estado_plan"; // Reporte por estado

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna reporte
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Planes', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>
