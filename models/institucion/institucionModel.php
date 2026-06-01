<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class InstitutionModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    private function normalizarFila($fila)
    {
        if (!$fila) { // Sin datos
            return false; // Retorna falso
        }

        $fila['id'] = $fila['id_institucion'] ?? $fila['id'] ?? null; // ID para vistas
        $fila['correo'] = $fila['correo_contacto'] ?? $fila['correo'] ?? ''; // Correo
        $fila['estado'] = ($fila['activo'] ?? 1) ? 'activo' : 'inactivo'; // Estado legible

        return $fila; // Fila normalizada
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM institucion ORDER BY id_institucion DESC"; // Instituciones
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        $lista = []; // Lista final

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) { // Recorre
            $lista[] = $this->normalizarFila($fila); // Normaliza
        }

        return $lista; // Retorna lista
    }

    public function obtenerActivas()
    {
        $sql = "SELECT * FROM institucion WHERE activo = 1 ORDER BY nombre ASC"; // Activas
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        $lista = []; // Lista final

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) { // Recorre
            $lista[] = $this->normalizarFila($fila); // Normaliza
        }

        return $lista; // Retorna activas
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM institucion WHERE id_institucion = :id LIMIT 1"; // Busca
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID
        $stmt->execute(); // Ejecuta consulta

        return $this->normalizarFila($stmt->fetch(PDO::FETCH_ASSOC)); // Retorna institución
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO institucion 
                (nombre, nit, telefono, correo_contacto, direccion, activo)
                VALUES
                (:nombre, :nit, :telefono, :correo, :direccion, :activo)"; // Crea

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindValue(':nit', $datos['nit'] ?? null); // NIT
        $stmt->bindValue(':telefono', $datos['telefono'] ?? null); // Teléfono
        $stmt->bindValue(':correo', $datos['correo'] ?? $datos['correo_contacto'] ?? null); // Correo
        $stmt->bindValue(':direccion', $datos['direccion'] ?? null); // Dirección
        $stmt->bindValue(':activo', ($datos['estado'] ?? 'activo') === 'activo' ? 1 : 0); // Activo

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $id = $datos['id'] ?? $datos['id_institucion']; // ID institución

        $sql = "UPDATE institucion 
                SET nombre = :nombre, nit = :nit, telefono = :telefono,
                    correo_contacto = :correo, direccion = :direccion, activo = :activo
                WHERE id_institucion = :id"; // Actualiza

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':nombre', $datos['nombre']); // Nombre
        $stmt->bindValue(':nit', $datos['nit'] ?? null); // NIT
        $stmt->bindValue(':telefono', $datos['telefono'] ?? null); // Teléfono
        $stmt->bindValue(':correo', $datos['correo'] ?? null); // Correo
        $stmt->bindValue(':direccion', $datos['direccion'] ?? null); // Dirección
        $stmt->bindValue(':activo', ($datos['estado'] ?? 'activo') === 'activo' ? 1 : 0); // Activo
        $stmt->bindParam(':id', $id); // ID

        return $stmt->execute(); // Ejecuta actualización
    }

    public function cambiarEstado($id, $estado)
    {
        $activo = ($estado === 'activo') ? 1 : 0; // Convierte estado

        $sql = "UPDATE institucion SET activo = :activo WHERE id_institucion = :id"; // Cambia
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':activo', $activo, PDO::PARAM_INT); // Activo
        $stmt->bindParam(':id', $id); // ID

        return $stmt->execute(); // Ejecuta cambio
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                VALUES (:usuario_id, 'Instituciones', :accion, NOW())"; // Historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindValue(':usuario_id', $usuarioId); // Usuario
        $stmt->bindParam(':accion', $accion); // Acción

        return $stmt->execute(); // Ejecuta registro
    }
}

class_alias('InstitutionModel', 'InstitucionModel'); // Alias compatible

?>
