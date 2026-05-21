<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class PagoModel extends StayFitModel
{
    protected string $table = 'pago';
    protected string $primaryKey = 'id_pago';
    protected array $fillable = ['id_plan_cliente', 'id_solicitud', 'monto', 'moneda', 'fecha_pago', 'metodo_pago', 'estado_pago', 'proveedor_pago', 'referencia_transaccion', 'codigo_aprobacion', 'url_comprobante', 'fecha_vencimiento', 'observacion'];

    public function activos(int $limit = 100): array
    {
        foreach (['activo', 'estado', 'estado_plan', 'estado_pago'] as $column) {
            if (in_array($column, sf_columns($this->table), true)) {
                return $this->where($column, in_array($column, ['activo']) ? 1 : 'ACTIVO', $limit);
            }
        }
        return $this->all($limit);
    }
}
