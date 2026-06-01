<?php

/**
 * Normaliza el nombre del rol de la BD al formato usado en sesión y validaciones.
 */
function normalizarRol($rol)
{
    $rol = strtolower(trim((string) $rol)); // Minúsculas sin espacios

    $mapa = [
        'administrador' => 'administrador',
        'admin' => 'administrador',
        'coach' => 'coach',
        'cliente' => 'cliente',
        'cliente-institucional' => 'cliente_institucional',
        'cliente_institucional' => 'cliente_institucional',
        'clienteins' => 'cliente_institucional',
    ];

    return $mapa[$rol] ?? $rol; // Retorna rol normalizado
}

function esAdministrador()
{
    $rol = normalizarRol($_SESSION['rol'] ?? ''); // Rol en sesión

    return $rol === 'administrador'; // Solo administrador
}

function esCoach()
{
    return normalizarRol($_SESSION['rol'] ?? '') === 'coach'; // Valida coach
}

function esCliente()
{
    return normalizarRol($_SESSION['rol'] ?? '') === 'cliente'; // Valida cliente
}

function esClienteInstitucional()
{
    return normalizarRol($_SESSION['rol'] ?? '') === 'cliente_institucional'; // Valida institucional
}

function validarAccesoAdministrador()
{
    if (!isset($_SESSION['usuario_id']) || !esAdministrador()) { // Sesión y rol admin
        header('Location: ../../views/auth/accesoDenegado.php'); // Sin permiso
        exit; // Detiene ejecución
    }
}

?>
