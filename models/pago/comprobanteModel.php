<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class ComprobanteModel
{
    private $db; // Conexión BD
    private $rutaBase; // Ruta para guardar comprobantes

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
        $this->rutaBase = __DIR__ . '/../../public/uploads/comprobantes/'; // Carpeta de comprobantes
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM comprobantes ORDER BY id DESC"; // Consulta comprobantes
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna lista
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM comprobantes WHERE id = :id LIMIT 1"; // Busca comprobante
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // Asigna ID
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna comprobante
    }

    public function obtenerPorPago($pagoId)
    {
        $sql = "SELECT id_pago, url_comprobante AS ruta_archivo, url_comprobante
                FROM pago WHERE id_pago = :pago_id LIMIT 1"; // Comprobante en pago
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':pago_id', $pagoId); // Asigna pago
        $stmt->execute(); // Ejecuta consulta

        $fila = $stmt->fetch(PDO::FETCH_ASSOC); // Obtiene fila

        if ($fila && !empty($fila['url_comprobante'])) { // Tiene URL
            $fila['nombre_archivo'] = basename($fila['url_comprobante']); // Nombre archivo
        }

        return $fila ?: ['nombre_archivo' => 'Sin comprobante']; // Retorna comprobante
    }

    public function registrarAdjunto(array $datos)
    {
        return $this->guardarArchivo($datos);
    }

    public function crear($datos)
    {
        $rutaFinal = $this->guardarArchivo($datos); // Guarda archivo físico

        if (!$this->tablaComprobantesExiste()) {
            return (bool) $rutaFinal;
        }

        $sql = "INSERT INTO comprobantes 
                (pago_id, nombre_archivo, ruta_archivo, tipo_archivo, estado, fecha_subida)
                VALUES 
                (:pago_id, :nombre_archivo, :ruta_archivo, :tipo_archivo, :estado, NOW())"; // Crea comprobante

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':pago_id', $datos['pago_id']); // Pago relacionado
        $stmt->bindParam(':nombre_archivo', $datos['nombre_archivo']); // Nombre original
        $stmt->bindParam(':ruta_archivo', $rutaFinal); // Ruta guardada
        $stmt->bindValue(':tipo_archivo', $datos['tipo_archivo'] ?? ''); // Tipo archivo
        $stmt->bindValue(':estado', $datos['estado'] ?? 'pendiente'); // Estado inicial

        return $stmt->execute(); // Ejecuta registro
    }

    private function tablaComprobantesExiste(): bool
    {
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'comprobantes'");

            return (bool) $stmt->fetch(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            return false;
        }
    }

    private function guardarArchivo($datos)
    {
        if (!is_dir($this->rutaBase)) { // Valida carpeta
            mkdir($this->rutaBase, 0777, true); // Crea carpeta
        }

        $nombreOriginal = basename($datos['nombre_archivo'] ?? 'comprobante'); // Limpia nombre
        $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION); // Obtiene extensión
        $nombreSeguro = 'comprobante_' . time() . '_' . uniqid() . '.' . $extension; // Nombre único
        $rutaCompleta = $this->rutaBase . $nombreSeguro; // Ruta física
        $rutaRelativa = 'public/uploads/comprobantes/' . $nombreSeguro; // Ruta BD

        if (!empty($datos['ruta_archivo']) && is_uploaded_file($datos['ruta_archivo'])) { // Valida subida real
            move_uploaded_file($datos['ruta_archivo'], $rutaCompleta); // Mueve archivo
            return $rutaRelativa; // Retorna ruta
        }

        return $datos['ruta_archivo'] ?? $rutaRelativa; // Retorna ruta recibida
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE comprobantes SET estado = :estado WHERE id = :id"; // Cambia estado
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':estado', $estado); // Estado nuevo
        $stmt->bindParam(':id', $id); // ID comprobante

        return $stmt->execute(); // Ejecuta cambio
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM comprobantes WHERE id = :id"; // Elimina comprobante
        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':id', $id); // ID comprobante

        return $stmt->execute(); // Ejecuta eliminación
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Comprobantes', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>