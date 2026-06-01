<?php

session_start();

require_once __DIR__ . '/../config/roles.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = (new Database())->conectar();
$rutaArchivo = null;
$urlRelativa = null;

function usuarioPuedeVerMaterial(PDO $db, ?string $urlRelativa, ?int $videoId): bool
{
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }

    $rol = normalizarRol($_SESSION['rol'] ?? '');

    if ($rol === 'administrador' || $rol === 'coach') {
        return true;
    }

    if ($rol !== 'cliente') {
        return false;
    }

    $clienteId = (int) ($_SESSION['cliente_id'] ?? 0);

    if ($clienteId < 1) {
        return false;
    }

    if ($videoId) {
        $sql = "SELECT 1 FROM video v
                INNER JOIN programa_virtual pv ON pv.id_programa_virtual = v.id_programa_virtual
                INNER JOIN plan pl ON pl.id_plan = pv.id_plan
                INNER JOIN plan_cliente pc ON pc.id_plan = pl.id_plan
                WHERE v.id_video = :video_id AND pc.id_cliente = :cliente_id AND pc.estado = 'ACTIVO'
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':video_id', $videoId, PDO::PARAM_INT);
        $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }

    if ($urlRelativa) {
        $sql = "SELECT 1 FROM video v
                INNER JOIN programa_virtual pv ON pv.id_programa_virtual = v.id_programa_virtual
                INNER JOIN plan pl ON pl.id_plan = pv.id_plan
                INNER JOIN plan_cliente pc ON pc.id_plan = pl.id_plan
                WHERE v.url_video = :url AND pc.id_cliente = :cliente_id AND pc.estado = 'ACTIVO'
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':url', $urlRelativa);
        $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }

    return false;
}

if (!empty($_GET['video_id'])) {
    $videoId = (int) $_GET['video_id'];

    if (!usuarioPuedeVerMaterial($db, null, $videoId)) {
        http_response_code(403);
        exit('Acceso denegado.');
    }

    $stmt = $db->prepare('SELECT url_video FROM video WHERE id_video = :id LIMIT 1');
    $stmt->bindValue(':id', $videoId, PDO::PARAM_INT);
    $stmt->execute();
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    $urlRelativa = $fila['url_video'] ?? null;

    if (esUrlExternaVideo($urlRelativa)) {
        header('Location: ' . $urlRelativa);
        exit;
    }

    $rutaArchivo = rutaFisicaMaterialVirtual($urlRelativa);
} elseif (!empty($_GET['archivo'])) {
    $nombre = basename((string) $_GET['archivo']);

    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $nombre)) {
        http_response_code(400);
        exit('Archivo no válido.');
    }

    $urlRelativa = 'public/uploads/contenido_virtual/' . $nombre;

    if (!usuarioPuedeVerMaterial($db, $urlRelativa, null)) {
        http_response_code(403);
        exit('Acceso denegado.');
    }

    $rutaArchivo = rutaFisicaMaterialVirtual($urlRelativa);
} else {
    http_response_code(400);
    exit('Parámetros inválidos.');
}

if (!$rutaArchivo) {
    http_response_code(404);
    exit('Material no encontrado.');
}

$extension = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));
$tipos = [
    'mp4' => 'video/mp4',
    'webm' => 'video/webm',
    'mov' => 'video/quicktime',
    'avi' => 'video/x-msvideo',
    'mkv' => 'video/x-matroska',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'pdf' => 'application/pdf',
];

header('Content-Type: ' . ($tipos[$extension] ?? 'application/octet-stream'));
header('Content-Length: ' . filesize($rutaArchivo));
header('Content-Disposition: inline; filename="' . basename($rutaArchivo) . '"');
readfile($rutaArchivo);
exit;
