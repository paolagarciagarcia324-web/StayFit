<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class CoachModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerTodos()
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado, u.telefono,
                       c.especialidad, c.credencial, c.biografia
                FROM coach c
                INNER JOIN users u ON u.id_usuario = c.id_coach
                ORDER BY u.id_usuario DESC"; // Consulta coaches

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        $lista = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene filas

        foreach ($lista as &$fila) { // Normaliza para vistas admin
            $fila['id'] = $fila['id_usuario'] ?? $fila['id'] ?? null; // ID
            $fila['estado'] = strtolower($fila['estado'] ?? 'activo'); // Estado
        }

        return $lista; // Retorna lista
    }

    public function obtenerActivos()
    {
        $sql = "SELECT u.id_usuario AS id, u.id_usuario, u.nombre, u.apellido, u.correo, u.telefono,
                       c.especialidad, c.biografia
                FROM coach c
                INNER JOIN users u ON u.id_usuario = c.id_coach
                WHERE u.estado = 'ACTIVO'
                ORDER BY u.nombre ASC"; // Consulta activos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna coaches activos
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.estado, u.telefono,
                       c.especialidad, c.credencial, c.biografia
                FROM coach c
                INNER JOIN users u ON u.id_usuario = c.id_coach
                WHERE c.id_coach = :id
                LIMIT 1"; // Busca por ID

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna coach
    }

    public function obtenerPorUsuario($usuarioId)
    {
        $sql = "SELECT u.id_usuario AS id, u.id_usuario, u.nombre, u.apellido, u.correo, u.estado, u.telefono,
                       c.especialidad, c.credencial, c.biografia
                FROM coach c
                INNER JOIN users u ON u.id_usuario = c.id_coach
                WHERE c.id_coach = :usuario_id
                LIMIT 1"; // Busca por usuario

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Asigna usuario
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna coach
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO coach 
                (id_coach, especialidad, credencial, biografia)
                VALUES
                (:id_coach, :especialidad, :credencial, :biografia)"; // Crea coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id_coach', $datos['id_coach']); // ID usuario (FK)
        $stmt->bindValue(':especialidad', $datos['especialidad'] ?? null); // Especialidad
        $stmt->bindValue(':credencial', $datos['credencial'] ?? null); // Credencial
        $stmt->bindValue(':biografia', $datos['biografia'] ?? null); // Biografía

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE coach 
                SET especialidad = :especialidad,
                    credencial = :credencial,
                    biografia = :biografia
                WHERE id_coach = :id"; // Actualiza coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindValue(':especialidad', $datos['especialidad'] ?? null); // Especialidad
        $stmt->bindValue(':credencial', $datos['credencial'] ?? null); // Credencial
        $stmt->bindValue(':biografia', $datos['biografia'] ?? null); // Biografía
        $stmt->bindParam(':id', $datos['id_coach']); // ID coach

        return $stmt->execute(); // Ejecuta actualización
    }

    public function cambiarEstado($id, $estado)
    {
        $mapa = ['activo' => 'ACTIVO', 'inactivo' => 'INACTIVO', 'suspendido' => 'SUSPENDIDO']; // Estados
        $estadoBd = $mapa[strtolower($estado)] ?? strtoupper($estado); // Estado BD

        $sql = "UPDATE users SET estado = :estado WHERE id_usuario = :id"; // Cambia estado en users
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estadoBd); // Nuevo estado
        $stmt->bindParam(':id', $id); // ID usuario

        return $stmt->execute(); // Ejecuta cambio
    }

    public function contarActivos()
    {
        $sql = "SELECT COUNT(*) AS total
                FROM coach c
                INNER JOIN users u ON u.id_usuario = c.id_coach
                WHERE u.estado = 'ACTIVO'"; // Cuenta activos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna total
    }

    public function obtenerClientesAsignados($coachId)
    {
        $sql = "SELECT u.id_usuario AS id, u.nombre, u.correo, c.tipo_cliente, pc.estado AS estado_plan
                FROM plan_cliente pc
                INNER JOIN cliente c ON c.id_cliente = pc.id_cliente
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                WHERE pc.id_coach = :coach_id
                ORDER BY u.nombre ASC"; // Clientes del coach

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':coach_id', $coachId); // Coach
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna clientes
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Coaches', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>
