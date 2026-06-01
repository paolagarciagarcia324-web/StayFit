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
        // Los datos físicos están almacenados en la tabla 'cliente' del schema
        $sql = "SELECT c.estatura_m, c.peso_inicial, c.objetivos, c.restricciones_medicas,
                       u.nombre, u.apellido, u.correo, u.telefono, u.foto_perfil
                FROM cliente c
                INNER JOIN users u ON u.id_usuario = c.id_cliente
                WHERE c.id_cliente = :cliente_id
                LIMIT 1"; // Datos físicos del cliente

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Asigna cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna datos
    }

    public function crear($datos)
    {
        // Actualiza los datos físicos directamente en la tabla 'cliente'
        $sql = "UPDATE cliente 
                SET estatura_m = :estatura, peso_inicial = :peso,
                    objetivos = :objetivo, restricciones_medicas = :restricciones
                WHERE id_cliente = :cliente_id"; // Actualiza datos físicos

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindValue(':estatura', $datos['estatura'] ?? null); // Estatura
        $stmt->bindValue(':peso', $datos['peso'] ?? null); // Peso
        $stmt->bindValue(':objetivo', $datos['objetivo'] ?? null); // Objetivo
        $stmt->bindValue(':restricciones', $datos['restricciones'] ?? null); // Restricciones
        $stmt->bindParam(':cliente_id', $datos['cliente_id']); // Cliente

        return $stmt->execute(); // Ejecuta registro
    }

    public function actualizar($datos)
    {
        return $this->crear($datos); // Reutiliza crear (UPDATE en tabla cliente)
    }

    public function historialPorCliente($clienteId)
    {
        // El historial de progreso físico está en registro_progreso
        $sql = "SELECT rp.*
                FROM registro_progreso rp
                INNER JOIN plan_cliente pc ON pc.id_plan_cliente = rp.id_plan_cliente
                WHERE pc.id_cliente = :cliente_id
                ORDER BY rp.fecha DESC"; // Historial físico

        $stmt = $this->db->prepare($sql); // Prepara consulta
        $stmt->bindParam(':cliente_id', $clienteId); // Cliente
        $stmt->execute(); // Ejecuta consulta

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna historial
    }

    public function registrarTrazabilidad($usuarioId, $accion)
    {
        try {
            $sql = "INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                    VALUES (:usuario_id, 'Datos físicos', :accion, NOW())"; // Guarda historial

            $stmt = $this->db->prepare($sql); // Prepara consulta
            $stmt->bindParam(':usuario_id', $usuarioId); // Usuario responsable
            $stmt->bindParam(':accion', $accion); // Acción realizada

            return $stmt->execute(); // Ejecuta registro
        } catch (PDOException $e) {
            return false; // Error al registrar
        }
    }
}

?>
