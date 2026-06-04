<?php

function dividirNombreCompleto($nombreCompleto)
{
    $nombreCompleto = trim(preg_replace('/\s+/', ' ', (string) $nombreCompleto));
    $partes = explode(' ', $nombreCompleto, 2);

    return [
        'nombre' => $partes[0] ?? $nombreCompleto,
        'apellido' => $partes[1] ?? '',
    ];
}

function edadAFechaNacimiento($edad)
{
    $edad = (int) $edad;

    if ($edad < 1 || $edad > 120) {
        return null;
    }

    $anio = (int) date('Y') - $edad;

    return sprintf('%d-01-01', $anio);
}

function calcularEdadDesdeFecha($fechaNacimiento)
{
    if (empty($fechaNacimiento)) {
        return null;
    }

    try {
        $nacimiento = new DateTime($fechaNacimiento);
        $hoy = new DateTime('today');

        return $nacimiento->diff($hoy)->y;
    } catch (Exception $e) {
        return null;
    }
}

function contrasenaYaHasheada($valor)
{
    return is_string($valor) && preg_match('/^\$2[ayb]\$.{56}$/', $valor);
}

function registrarBitacora(PDO $db, ?int $usuarioId, string $modulo, string $accion): bool
{
    static $usaBitacoraSistema = null;

    if ($usaBitacoraSistema === null) {
        try {
            $usaBitacoraSistema = (bool) $db->query("SHOW TABLES LIKE 'bitacora_sistema'")->fetch(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            $usaBitacoraSistema = false;
        }
    }

    try {
        if ($usaBitacoraSistema) {
            $stmt = $db->prepare(
                'INSERT INTO bitacora_sistema (id_user, modulo, accion, descripcion, creado_en)
                 VALUES (:usuario_id, :modulo, :accion, :descripcion, NOW())'
            );
            $stmt->bindValue(':accion', mb_substr($accion, 0, 120), PDO::PARAM_STR);
            $stmt->bindValue(':descripcion', $accion, PDO::PARAM_STR);
        } else {
            $stmt = $db->prepare(
                'INSERT INTO bitacora_busqueda (id_usuario, modulo, accion, fecha_hora)
                 VALUES (:usuario_id, :modulo, :accion, NOW())'
            );
            $stmt->bindValue(':accion', $accion, PDO::PARAM_STR);
        }

        if ($usuarioId !== null) {
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':usuario_id', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':modulo', mb_substr($modulo, 0, 80), PDO::PARAM_STR);

        return $stmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}

function rutaBaseProyecto(): string
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    if (preg_match('#^(.*?)/controllers/#', $script, $coincidencia)) {
        $base = $coincidencia[1];
    } elseif (preg_match('#^(.*?)/controller/#', $script, $coincidencia)) {
        $base = $coincidencia[1];
    } elseif (preg_match('#^(.*?)/public/#', $script, $coincidencia)) {
        $base = $coincidencia[1];
    } elseif (preg_match('#^(.*?)/views/#', $script, $coincidencia)) {
        $base = $coincidencia[1];
    } else {
        $base = rtrim(dirname($script), '/');
    }

    return $base;
}

function rutaFisicaComprobante(?string $rutaRelativa): ?string
{
    $ruta = trim((string) $rutaRelativa);

    if ($ruta === '') {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $ruta)) {
        return null;
    }

    $ruta = str_replace('\\', '/', $ruta);
    $nombre = basename($ruta);
    $carpeta = realpath(dirname(__DIR__) . '/public/uploads/comprobantes');

    if (!$carpeta || $nombre === '' || $nombre === '.' || $nombre === '..') {
        return null;
    }

    $archivo = realpath($carpeta . DIRECTORY_SEPARATOR . $nombre);

    if (!$archivo || strpos($archivo, $carpeta) !== 0 || !is_file($archivo)) {
        return null;
    }

    return $archivo;
}

function urlPublicaComprobante(?string $rutaRelativa, ?int $solicitudId = null, ?int $pagoId = null): ?string
{
    $ruta = trim((string) $rutaRelativa);

    if ($ruta === '' && !$solicitudId && !$pagoId) {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $ruta)) {
        return $ruta;
    }

    $parametros = [];

    if ($solicitudId) {
        $parametros['solicitud_id'] = $solicitudId;
    } elseif ($pagoId) {
        $parametros['pago_id'] = $pagoId;
    } else {
        $nombre = basename(str_replace('\\', '/', $ruta));

        if ($nombre === '') {
            return null;
        }

        $parametros['archivo'] = $nombre;
    }

    return rutaBaseProyecto() . '/public/verComprobante.php?' . http_build_query($parametros);
}

function esComprobanteImagen(?string $ruta): bool
{
    if (empty($ruta)) {
        return false;
    }

    $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
}

function esComprobantePdf(?string $ruta): bool
{
    return !empty($ruta) && strtolower(pathinfo($ruta, PATHINFO_EXTENSION)) === 'pdf';
}

function guardarComprobanteIngreso(array $archivo): ?string
{
    if (empty($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
        return null;
    }

    $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

    if (!in_array($extension, $permitidas, true)) {
        return null;
    }

    $pesoMaximo = 5 * 1024 * 1024;

    if (($archivo['size'] ?? 0) > $pesoMaximo) {
        return null;
    }

    $directorio = dirname(__DIR__) . '/public/uploads/comprobantes/';

    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $nombreSeguro = 'comprobante_' . time() . '_' . uniqid('', true) . '.' . $extension;
    $rutaCompleta = $directorio . $nombreSeguro;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        return null;
    }

    return 'public/uploads/comprobantes/' . $nombreSeguro;
}

function rutaFisicaMaterialVirtual(?string $rutaRelativa): ?string
{
    $ruta = trim((string) $rutaRelativa);

    if ($ruta === '' || preg_match('/^https?:\/\//i', $ruta)) {
        return null;
    }

    $nombre = basename(str_replace('\\', '/', $ruta));
    $carpeta = realpath(dirname(__DIR__) . '/public/uploads/contenido_virtual');

    if (!$carpeta || $nombre === '' || $nombre === '.' || $nombre === '..') {
        return null;
    }

    $archivo = realpath($carpeta . DIRECTORY_SEPARATOR . $nombre);

    if (!$archivo || strpos($archivo, $carpeta) !== 0 || !is_file($archivo)) {
        return null;
    }

    return $archivo;
}

function guardarMaterialVirtual(array $archivo, string $prefijo = 'material'): ?string
{
    if (empty($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
        return null;
    }

    $directorio = dirname(__DIR__) . '/public/uploads/contenido_virtual/';

    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));

    $permitidas = [
        'mp4',
        'webm',
        'mov',
        'avi',
        'mkv',
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'pdf'
    ];

    if (!in_array($extension, $permitidas, true)) {
        return null;
    }

    $pesoMaximo = 100 * 1024 * 1024;

    if (($archivo['size'] ?? 0) > $pesoMaximo) {
        return null;
    }

    $prefijoSeguro = preg_replace('/[^a-zA-Z0-9_-]/', '_', $prefijo);
    $nombreSeguro = $prefijoSeguro . '_' . time() . '_' . uniqid('', true) . '.' . $extension;
    $rutaCompleta = $directorio . $nombreSeguro;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        return null;
    }

    return 'public/uploads/contenido_virtual/' . $nombreSeguro;
}

function urlPublicaMaterialVirtual(?string $rutaRelativa, ?int $videoId = null): ?string
{
    $ruta = trim((string) $rutaRelativa);

    if ($ruta === '' && !$videoId) {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $ruta)) {
        return $ruta;
    }

    $parametros = [];

    if ($videoId) {
        $parametros['video_id'] = $videoId;
    } else {
        $nombre = basename(str_replace('\\', '/', $ruta));

        if ($nombre === '') {
            return null;
        }

        $parametros['archivo'] = $nombre;
    }

    return rutaBaseProyecto() . '/public/verMaterialVirtual.php?' . http_build_query($parametros);
}

function esUrlExternaVideo(?string $url): bool
{
    return !empty($url) && (bool) preg_match('/^https?:\/\//i', trim($url));
}

function embedUrlVideo(?string $url): ?string
{
    if (!esUrlExternaVideo($url)) {
        return null;
    }

    $url = trim($url);

    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{6,})/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }

    return $url;
}

function tipoMediaDesdeArchivo(string $nombreArchivo): string
{
    $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true)) {
        return 'IMAGEN';
    }

    if ($ext === 'pdf') {
        return 'PDF';
    }

    return 'VIDEO';
}