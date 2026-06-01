<?php
require_once __DIR__ . '/../models/solicitud/solicitudIngresoModel.php';
$m = new SolicitudIngresoModel();
echo $m->registrarTrazabilidad(null, 'test publico') ? "OK\n" : "FALLO\n";
