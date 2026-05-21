<?php

require_once __DIR__ . '/../../config/database.php'; // Importa conexión

class DatosFisicosModel
{
    private $db; // Conexión BD

    public function __construct()
    {
        $database = new Database(); // Instancia conexión
        $this->db = $database->conectar(); // Abre conexión
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT * FROM datos_fisicos 
                WHERE cliente_id = :cliente_id
                ORDER BY id DESC
                LIMIT 1"; // Últimos datos físicos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna datos
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO datos_fisicos 
                (cliente_id, peso, estatura, objetivo, restricciones, observaciones, fecha)
                VALUES 
                (:cliente_id, :peso, :estatura, :objetivo, :restricciones, :observaciones, NOW())"; // Crea datos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $datos['cliente_id']); // Cliente
        $stmt->bindParam(':peso', $datos['peso']); // Peso
        $stmt->bindParam(':estatura', $datos['estatura']); // Estatura
        $stmt->bindParam(':objetivo', $datos['objetivo']); // Objetivo
        $stmt->bindParam(':restricciones', $datos['restricciones']); // Restricciones
        $stmt->bindParam(':observaciones', $datos['observaciones']); // Observaciones

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        $actual = $this->obtenerPorCliente($datos['cliente_id']); // Busca registro actual

        if (!$actual) { // Si no existe
            return $this->crear($datos); // Crea nuevo registro
        }

        $sql = "UPDATE datos_fisicos 
                SET peso = :peso, estatura = :estatura, objetivo = :objetivo,
                    restricciones = :restricciones, observaciones = :observaciones, fecha = NOW()
                WHERE cliente_id = :cliente_id"; // Actualiza datos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':peso', $datos['peso']); // Peso
        $stmt->bindParam(':estatura', $datos['estatura']); // Estatura
        $stmt->bindParam(':objetivo', $datos['objetivo']); // Objetivo
        $stmt->bindParam(':restricciones', $datos['restricciones']); // Restricciones
        $stmt->bindParam(':observaciones', $datos['observaciones']); // Observaciones
        $stmt->bindParam(':cliente_id', $datos['cliente_id']); // Cliente

        return $stmt->execute(); // Ejecuta actualización
    }

    public function historialPorCliente($clienteId)
    {
        $sql = "SELECT * FROM datos_fisicos 
                WHERE cliente_id = :cliente_id
                ORDER BY fecha DESC"; // Historial físico

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna historial
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        $sql = "INSERT INTO trazabilidad (usuario_id, modulo, accion, fecha)
                VALUES (:usuario_id, 'Datos físicos', :accion, NOW())"; // Guarda historial

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
        $stmt->bindParam(':accion', $accion); // Acción realizada

        return $stmt->execute(); // Ejecuta registro
    }
}

?>