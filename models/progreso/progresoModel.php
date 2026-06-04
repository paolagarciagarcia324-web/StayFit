<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class ProgresoModel
{
    private $db; // Conexión BD
    private $rutaBase; // Ruta para fotos

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
        $this->rutaBase = __DIR__ . '/../../public/uploads/progresos/'; // Carpeta de fotos
    }

    public function obtenerTodos()
    {
        $sql = "SELECT rp.*, u.nombre AS cliente
                FROM registro_progreso rp
                LEFT JOIN plan_cliente pc ON pc.id_plan_cliente = rp.id_plan_cliente
                LEFT JOIN users u ON u.id_usuario = pc.id_cliente
                ORDER BY rp.fecha DESC"; // Consulta progresos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM registro_progreso WHERE id_registro_progreso = :id LIMIT 1"; // Busca progreso
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID progreso
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna progreso
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT rp.* FROM registro_progreso rp
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = rp.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id
                ORDER BY rp.fecha DESC"; // Progresos del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna historial
    }

    public function obtenerUltimoPorCliente($clienteId)
    {
        $sql = "SELECT rp.* FROM registro_progreso rp
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = rp.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id
                ORDER BY rp.fecha DESC
                LIMIT 1"; // Último progreso

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna último registro
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT rp.*, u.nombre AS cliente
                FROM registro_progreso rp
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = rp.id_plan_cliente
                INNER JOIN users u ON u.id_usuario = pc.id_cliente
                WHERE pc.id_coach = :coach_id
                ORDER BY rp.fecha DESC"; // Progresos de clientes del coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna progresos
    }

    public function registrar($datos)
    {
        $foto = $this->guardarFoto($datos); // Guarda foto si existe

        $sql = "INSERT INTO registro_progreso 
                (id_plan_cliente, fecha, peso, cintura, cadera, brazos, piernas, fotos_evolucion, observacion_cliente)
                VALUES
                (:id_plan_cliente, :fecha, :peso, :cintura, :cadera, :brazos, :piernas, :fotos_evolucion, :observacion_cliente)"; // Registra progreso

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']); // Plan cliente
        $stmt->bindValue(':fecha', $datos['fecha'] ?? date('Y-m-d')); // Fecha
        $stmt->bindParam(':peso', $datos['peso']); // Peso
        $stmt->bindValue(':cintura', $datos['cintura'] ?? null); // Cintura
        $stmt->bindValue(':cadera', $datos['cadera'] ?? null); // Cadera
        $stmt->bindValue(':brazos', $datos['brazos'] ?? null); // Brazos
        $stmt->bindValue(':piernas', $datos['piernas'] ?? null); // Piernas
        $stmt->bindValue(':fotos_evolucion', $foto); // Fotos
        $stmt->bindValue(':observacion_cliente', $datos['observacion'] ?? ''); // Observación

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $foto = $this->guardarFoto($datos); // Guarda nueva foto si llega

        $sql = "UPDATE registro_progreso 
                SET peso = :peso, cintura = :cintura, cadera = :cadera, 
                    brazos = :brazos, piernas = :piernas,
                    fotos_evolucion = COALESCE(:fotos_evolucion, fotos_evolucion),
                    observacion_cliente = :observacion_cliente
                WHERE id_registro_progreso = :id"; // Actualiza progreso

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':peso', $datos['peso']); // Peso
        $stmt->bindValue(':cintura', $datos['cintura'] ?? null); // Cintura
        $stmt->bindValue(':cadera', $datos['cadera'] ?? null); // Cadera
        $stmt->bindValue(':brazos', $datos['brazos'] ?? null); // Brazos
        $stmt->bindValue(':piernas', $datos['piernas'] ?? null); // Piernas
        $stmt->bindValue(':fotos_evolucion', $foto); // Fotos
        $stmt->bindValue(':observacion_cliente', $datos['observacion'] ?? ''); // Observación
        $stmt->bindParam(':id', $datos['id']); // ID progreso

        return $stmt->execute(); // Ejecuta actualización
    }

    public function guardarObservacionCoach($datos)
    {
        $sql = "INSERT INTO registro_progreso 
                (id_plan_cliente, fecha, observacion_coach)
                VALUES
                (:id_plan_cliente, :fecha, :observacion_coach)"; // Guarda observación coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_plan_cliente', $datos['id_plan_cliente']); // Plan cliente
        $stmt->bindValue(':fecha', $datos['fecha'] ?? date('Y-m-d')); // Fecha
        $stmt->bindParam(':observacion_coach', $datos['observacion']); // Observación

        return $stmt->execute(); // Ejecuta registro
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE registro_progreso SET estado = :estado WHERE id_registro_progreso = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID progreso

        return $stmt->execute(); // Ejecuta cambio
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM registro_progreso WHERE id_registro_progreso = :id"; // Elimina progreso
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID progreso

        return $stmt->execute(); // Ejecuta eliminación
    }

    public function reporteGeneral()
    {
        $tabla = $this->tablaExiste('registros_progreso') ? 'registros_progreso' : 'registro_progreso';

        if ($tabla === 'registros_progreso') {
            $sql = "SELECT 'registrado' AS estado, COUNT(*) AS total FROM {$tabla}";
        } else {
            $sql = "SELECT estado, COUNT(*) AS total FROM {$tabla} GROUP BY estado";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function tablaExiste(string $nombre): bool
    {
        try {
            $stmt = $this->db->query('SHOW TABLES LIKE ' . $this->db->quote($nombre));

            return (bool) $stmt->fetch(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function reportePorCoach($coachId)
    {
        $sql = "SELECT rp.estado, COUNT(*) AS total
                FROM registro_progreso rp
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = rp.id_plan_cliente
                WHERE pc.id_coach = :coach_id
                GROUP BY rp.estado"; // Reporte por coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna reporte
    }

    private function guardarFoto($datos)
    {
        if (empty($datos['foto_tmp']) || empty($datos['foto_nombre'])) { // Valida foto
            return null; // Sin foto nueva
        }

        if (!is_dir($this->rutaBase)) { // Valida carpeta
            mkdir($this->rutaBase, 0777, true); // Crea carpeta
        }

        $extension = pathinfo($datos['foto_nombre'], PATHINFO_EXTENSION); // Obtiene extensión
        $nombreSeguro = 'progreso_' . time() . '_' . uniqid() . '.' . $extension; // Nombre único
        $rutaCompleta = $this->rutaBase . $nombreSeguro; // Ruta física
        $rutaRelativa = 'public/uploads/progresos/' . $nombreSeguro; // Ruta BD

        if (is_uploaded_file($datos['foto_tmp'])) { // Valida archivo subido
            move_uploaded_file($datos['foto_tmp'], $rutaCompleta); // Mueve archivo
            return $rutaRelativa; // Retorna ruta
        }

        return null; // No guarda foto
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Progreso', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>