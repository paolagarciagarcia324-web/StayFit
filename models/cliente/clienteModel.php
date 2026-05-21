<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class ClienteModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM clientes ORDER BY id DESC"; // Consulta clientes
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM clientes WHERE id = :id LIMIT 1"; // Busca cliente
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna cliente
    }

    public function obtenerPorUsuario($usuarioId)
    {
        $sql = "SELECT * FROM clientes WHERE usuario_id = :usuario_id LIMIT 1"; // Busca por usuario
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Asigna usuario
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna cliente
    }

    public function obtenerPorCoach($coachId)
    {
        $sql = "SELECT * FROM clientes WHERE coach_id = :coach_id ORDER BY nombre ASC"; // Clientes del coach
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna clientes
    }

    public function obtenerVirtualesPorCoach($coachId)
    {
        $sql = "SELECT * FROM clientes 
                WHERE coach_id = :coach_id AND modalidad = 'virtual'
                ORDER BY nombre ASC"; // Clientes virtuales

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Asigna coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna clientes
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO clientes 
                (usuario_id, nombre, identificacion, edad, celular, tipo_cliente, modalidad, estado)
                VALUES 
                (:usuario_id, :nombre, :identificacion, :edad, :celular, :tipo_cliente, :modalidad, :estado)"; // Crea cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $datos['usuario_id']); // Usuario
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindParam(':identificacion', $datos['identificacion']); // Documento
        $stmt->bindParam(':edad', $datos['edad']); // Edad
        $stmt->bindParam(':celular', $datos['celular']); // Celular
        $stmt->bindParam(':tipo_cliente', $datos['tipo_cliente']); // Tipo cliente
        $stmt->bindParam(':modalidad', $datos['modalidad']); // Modalidad
        $stmt->bindParam(':estado', $datos['estado']); // Estado

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizarPerfil($datos)
    {
        $sql = "UPDATE clientes 
                SET identificacion = :identificacion, edad = :edad, celular = :celular
                WHERE id = :id"; // Actualiza perfil

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':identificacion', $datos['identificacion']); // Documento
        $stmt->bindParam(':edad', $datos['edad']); // Edad
        $stmt->bindParam(':celular', $datos['celular']); // Celular
        $stmt->bindParam(':id', $datos['id']); // ID cliente

        return $stmt->execute(); // Ejecuta actualización
    }

    public function asignarCoach($clienteId, $coachId)
    {
        $sql = "UPDATE clientes SET coach_id = :coach_id WHERE id = :cliente_id"; // Asigna coach
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente

        return $stmt->execute(); // Ejecuta asignación
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE clientes SET estado = :estado WHERE id = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID cliente

        return $stmt->execute(); // Ejecuta cambio
    }

    public function reportePorCoach($coachId)
    {
        $sql = "SELECT estado, COUNT(*) AS total
                FROM clientes
                WHERE coach_id = :coach_id
                GROUP BY estado"; // Reporte clientes coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna reporte
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Clientes', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>
