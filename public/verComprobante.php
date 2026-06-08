<?php

session_start();

require_once __DIR__ . '/../config/roles.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/schemaHelper.php';
require_once __DIR__ . '/../config/helpers.php';

if (!isset($_SESSION['usuario_id']) || !esAdministrador()) {
    http_response_code(403);
    exit('Acceso denegado.');
}

$rutaArchivo = null;
$db = (new Database())->conectar();
$schema = new SchemaHelper($db);
$esquemaNuevo = $schema->usaEsquemaNuevo();

if (!empty($_GET['solicitud_id'])) {
    $solicitudId = (int) $_GET['solicitud_id'];

    if ($esquemaNuevo) {
        $sql = "SELECT p.comprobante_url AS comprobante_pago
                FROM solicitudes_compra s
                LEFT JOIN pagos p ON p.id_solicitud = s.id_solicitud
                WHERE s.id_solicitud = :id
                LIMIT 1";
    } else {
        $sql = "SELECT s.url_comprobante, p.url_comprobante AS comprobante_pago
                FROM solicitud_ingreso s
                LEFT JOIN pago p ON p.id_solicitud = s.id_solicitud
                WHERE s.id_solicitud = :id
                LIMIT 1";
    }

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $solicitudId, PDO::PARAM_INT);
    $stmt->execute();
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    $rutaRelativa = trim((string) ($fila['url_comprobante'] ?? ''));
    if ($rutaRelativa === '') {
        $rutaRelativa = trim((string) ($fila['comprobante_pago'] ?? ''));
    }

    $rutaArchivo = rutaFisicaComprobante($rutaRelativa);
} elseif (!empty($_GET['pago_id'])) {
    $pagoId = (int) $_GET['pago_id'];
    $tablaPago = $esquemaNuevo ? 'pagos' : 'pago';
    $columnaComprobante = $esquemaNuevo ? 'comprobante_url' : 'url_comprobante';

    $stmt = $db->prepare("SELECT {$columnaComprobante} AS url_comprobante FROM {$tablaPago} WHERE id_pago = :id LIMIT 1");
    $stmt->bindValue(':id', $pagoId, PDO::PARAM_INT);
    $stmt->execute();
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    $rutaArchivo = rutaFisicaComprobante($fila['url_comprobante'] ?? null);
} elseif (!empty($_GET['archivo'])) {
    $nombre = basename((string) $_GET['archivo']);

    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $nombre)) {
        http_response_code(400);
        exit('Nombre de archivo no válido.');
    }

    $rutaArchivo = rutaFisicaComprobante('public/uploads/comprobantes/' . $nombre);
}

if (!$rutaArchivo) {
    http_response_code(404);
    exit('Comprobante no encontrado.');
}

$extension = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));
$tipos = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'bmp' => 'image/bmp',
    'pdf' => 'application/pdf',
    'txt' => 'text/plain',
];

header('Content-Type: ' . ($tipos[$extension] ?? 'application/octet-stream'));
header('Content-Length: ' . filesize($rutaArchivo));
header('Content-Disposition: inline; filename="' . basename($rutaArchivo) . '"');
readfile($rutaArchivo);
exit;
