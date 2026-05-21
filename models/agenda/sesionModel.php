<?php

require_once __DIR__ . '/../../config/database.php'; // Importa la conexión

class SesionModel
{
    private $db; // Conexión a la base de datos

    public function __construct()
    {
        $database = new Database(); // Crea instancia de conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM sesiones ORDER BY fecha DESC, hora DESC"; // Consulta sesiones
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna sesiones
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT * FROM sesiones WHERE cliente_id = :cliente_id ORDER BY fecha ASC, hora ASC"; // Sesiones cliente
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna sesiones
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT * FROM sesiones WHERE coach_id = :coach_id ORDER BY fecha ASC, hora ASC"; // Sesiones coach
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna sesiones
    }

    public function obtenerProximasPorCoach($coachId)
    {
        $sql = "SELECT * FROM sesiones 
                WHERE coach_id = :coach_id AND fecha >= CURDATE()
                ORDER BY fecha ASC, hora ASC"; // Próximas sesiones

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna sesiones
    }

    public function obtenerEventosPorCliente($clienteId)
    {
        $sql = "SELECT * FROM sesiones 
                WHERE cliente_id = :cliente_id AND tipo = 'evento'
                ORDER BY fecha ASC, hora ASC"; // Eventos cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna eventos
    }

    public function obtenerEventosPorCoach($coachId)
    {
        $sql = "SELECT * FROM sesiones 
                WHERE coach_id = :coach_id AND tipo = 'evento'
                ORDER BY fecha ASC, hora ASC"; // Eventos coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna eventos
    }

    public function obtenerGrupalesPorCliente($clienteId)
    {
        $sql = "SELECT s.* FROM sesiones s
                INNER JOIN sesion_participantes sp ON sp.sesion_id = s.id
                WHERE sp.cliente_id = :cliente_id AND s.tipo = 'grupal'
                ORDER BY s.fecha ASC, s.hora ASC"; // Sesiones grupales

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna sesiones
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO sesiones 
                (coach_id, cliente_id, titulo, descripcion, fecha, hora, modalidad, tipo, estado)
                VALUES
                (:coach_id, :cliente_id, :titulo, :descripcion, :fecha, :hora, :modalidad, :tipo, :estado)"; // Inserta sesión

        $stmt = $this->db->prepare($sql); // Prepara consulta

        $stmt->bindParam(':coach_id', $datos['coach_id']); // Coach
        $stmt->bindParam(':cliente_id', $datos['cliente_id']); // Cliente
        $stmt->bindParam(':titulo', $datos['titulo']); // Título
        $stmt->bindParam(':descripcion', $datos['descripcion']); // Descripción
        $stmt->bindParam(':fecha', $datos['fecha']); // Fecha
        $stmt->bindParam(':hora', $datos['hora']); // Hora
        $stmt->bindParam(':modalidad', $datos['modalidad']); // Modalidad
        $stmt->bindParam(':tipo', $datos['tipo']); // Tipo
        $stmt->bindParam(':estado', $datos['estado']); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function crearEvento($datos)
    {
        $datos['tipo'] = 'evento'; // Define tipo evento
        $datos['cliente_id'] = $datos['cliente_id'] ?? null; // Cliente opcional

        return $this->crear($datos); // Reutiliza crear
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE sesiones SET estado = :estado WHERE id = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Nuevo estado
        $stmt->bindParam(':id', $id); // ID sesión

        return $stmt->execute(); // Ejecuta actualización
    }

    public function actualizarEstado($datos)
    {
        $sql = "UPDATE sesiones 
                SET estado = :estado, observacion = :observacion
                WHERE id = :id"; // Actualiza sesión

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $datos['estado']); // Estado
        $stmt->bindParam(':observacion', $datos['observacion']); // Observación
        $stmt->bindParam(':id', $datos['id']); // ID sesión

        return $stmt->execute(); // Ejecuta actualización
    }

    public function inscribirClienteEvento($datos)
    {
        $sql = "INSERT INTO sesion_participantes (sesion_id, cliente_id, estado)
                VALUES (:sesion_id, :cliente_id, :estado)"; // Inscribe cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':sesion_id', $datos['evento_id']); // ID evento
        $stmt->bindParam(':cliente_id', $datos['cliente_id']); // ID cliente
        $stmt->bindParam(':estado', $datos['estado']); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function confirmarAsistencia($datos)
    {
        $sql = "UPDATE sesion_participantes 
                SET estado = :estado
                WHERE sesion_id = :sesion_id AND cliente_id = :cliente_id"; // Confirma asistencia

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $datos['estado']); // Estado
        $stmt->bindParam(':sesion_id', $datos['sesion_id']); // ID sesión
        $stmt->bindParam(':cliente_id', $datos['cliente_id']); // ID cliente

        return $stmt->execute(); // Ejecuta actualización
    }

    public function marcarAsistencia($datos)
    {
        $sql = "UPDATE sesion_participantes 
                SET estado = :estado, observacion = :observacion
                WHERE sesion_id = :sesion_id AND cliente_id = :cliente_id"; // Marca asistencia

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $datos['estado']); // Estado asistencia
        $stmt->bindParam(':observacion', $datos['observacion']); // Observación
        $stmt->bindParam(':sesion_id', $datos['sesion_id']); // ID sesión
        $stmt->bindParam(':cliente_id', $datos['cliente_id']); // ID cliente

        return $stmt->execute(); // Ejecuta actualización
    }

    public function reportePorCoach($coachId)
    {
        $sql = "SELECT estado, COUNT(*) AS total
                FROM sesiones
                WHERE coach_id = :coach_id
                GROUP BY estado"; // Reporte sesiones

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna reporte
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Sesiones', :accion, NOW())"; // Inserta historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Guarda trazabilidad
    }
}

?>
