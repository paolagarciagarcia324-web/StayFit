<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class AccesoModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT acm.id_acceso_cliente_modulo AS id,
                       acm.id_plan_cliente,
                       acm.habilitado,
                       acm.fecha_habilitacion,
                       acm.fecha_expiracion,
                       ms.nombre AS modulo,
                       CASE WHEN acm.habilitado = 1 THEN 'activo' ELSE 'inactivo' END AS estado
                FROM acceso_cliente_modulo acm
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = acm.id_plan_cliente
                INNER JOIN modulo_servicio ms ON ms.id_modulo_servicio = acm.id_modulo_servicio
                WHERE pc.id_cliente = :cliente_id
                ORDER BY acm.id_acceso_cliente_modulo DESC"; // Consulta accesos del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna accesos
    }

    public function obtenerActivosPorCliente($clienteId)
    {
        $sql = "SELECT acm.id_acceso_cliente_modulo AS id,
                       acm.id_plan_cliente,
                       acm.habilitado,
                       acm.fecha_habilitacion,
                       acm.fecha_expiracion,
                       ms.nombre AS modulo,
                       CASE WHEN acm.habilitado = 1 THEN 'activo' ELSE 'inactivo' END AS estado
                FROM acceso_cliente_modulo acm
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = acm.id_plan_cliente
                INNER JOIN modulo_servicio ms ON ms.id_modulo_servicio = acm.id_modulo_servicio
                WHERE pc.id_cliente = :cliente_id AND acm.habilitado = 1
                ORDER BY acm.id_acceso_cliente_modulo DESC"; // Consulta accesos activos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna accesos activos
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO acceso_cliente_modulo 
                (id_plan_cliente, id_modulo_servicio, habilitado, fecha_habilitacion, fecha_expiracion)
                VALUES
                (:id_plan_cliente, :id_modulo_servicio, :habilitado, :fecha_habilitacion, :fecha_expiracion)"; // Crea acceso

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']); // Plan cliente
        $stmt->bindParam(':id_modulo_servicio', $datos['id_modulo_servicio']); // Módulo
        $stmt->bindValue(':habilitado', $datos['habilitado'] ?? 1); // Habilitado (BOOLEAN)
        $stmt->bindValue(':fecha_habilitacion', $datos['fecha_habilitacion'] ?? date('Y-m-d H:i:s')); // Fecha habilitación
        $stmt->bindParam(':fecha_expiracion', $datos['fecha_expiracion']); // Fecha expiración

        return $stmt->execute(); // Ejecuta registro
    }

    public function crearAccesosPorPlan($clienteId, $plan)
    {
        $fechaInicio = date('Y-m-d'); // Fecha actual
        $fechaFin = date('Y-m-d', strtotime('+' . ($plan['duracion'] ?? 30) . ' days')); // Calcula vencimiento

        $modulos = ['perfil', 'plan', 'progreso', 'calendario', 'pagos']; // Accesos base

        if (($plan['incluye_entrenamiento'] ?? 0) == 1) { // Valida entrenamiento
            $modulos[] = 'entrenamiento'; // Agrega entrenamiento
        }

        if (($plan['incluye_nutricion'] ?? 0) == 1) { // Valida nutrición
            $modulos[] = 'nutricion'; // Agrega nutrición
        }

        if (($plan['modalidad'] ?? '') === 'virtual' || ($plan['modalidad'] ?? '') === 'mixta') { // Valida virtual
            $modulos[] = 'contenido_virtual'; // Agrega contenido virtual
        }

        if (($plan['requiere_coach'] ?? 0) == 1) { // Valida coach
            $modulos[] = 'comunicacion'; // Agrega comunicación
        }

        foreach ($modulos as $modulo) { // Recorre módulos
            $this->crear([
                'cliente_id' => $clienteId, // Cliente
                'id_plan_cliente' => $plan['id'], // Plan cliente
                'id_modulo_servicio' => $this->obtenerIdModulo($modulo), // Módulo
                'habilitado' => 1, // Estado inicial
                'fecha_habilitacion' => $fechaInicio, // Inicio
                'fecha_expiracion' => $fechaFin // Vencimiento
            ]);
        }

        return true; // Finaliza proceso
    }

    public function cambiarEstado($id, $estado)
    {
        $habilitado = $estado === 'activo' ? 1 : 0;
        $sql = "UPDATE acceso_cliente_modulo SET habilitado = :habilitado WHERE id_acceso_cliente_modulo = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':habilitado', $habilitado); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID acceso

        return $stmt->execute(); // Ejecuta cambio
    }

    public function vencerAccesos()
    {
        $sql = "UPDATE acceso_cliente_modulo 
                SET habilitado = 0
                WHERE fecha_expiracion < NOW()
                AND habilitado = 1"; // Marca accesos vencidos

        $stmt = $this->db->prepare($sql); // Prepara consulta

        return $stmt->execute(); // Ejecuta actualización
    }

    public function contarVencidos()
    {
        $sql = "SELECT COUNT(*) AS total FROM acceso_cliente_modulo WHERE habilitado = 0 AND fecha_expiracion < NOW()"; // Cuenta vencidos
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna total
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

        $nombreBd = $mapa[$nombreModulo] ?? null;

        if ($nombreBd !== null) {
            return $nombreBd;
        }

        $sql = "SELECT id_modulo_servicio FROM modulo_servicio WHERE LOWER(nombre) = :nombre LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nombre', strtoupper(str_replace('_', '', $nombreModulo)));
        $stmt->execute();

        return $stmt->fetchColumn() ?: 1;
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Accesos', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>
