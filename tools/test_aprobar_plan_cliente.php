<?php

require_once __DIR__ . '/../models/cliente/clienteModel.php';

$model = new ClienteModel();
$id = $model->crearPlanCliente(7, 1, null, 99);
echo $id ? "OK plan_cliente ID: {$id}\n" : "FALLO\n";
