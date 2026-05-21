<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class SolicitudIngresoModel extends StayFitModel
{
    protected string $table = 'solicitud_ingreso';
    protected string $primaryKey = 'id_solicitud';
    protected array $fillable = ['nombre_completo', 'edad', 'identificacion', 'celular', 'plan_interes', 'modalidad', 'tipo_cuenta', 'numero_cuenta', 'url_comprobante', 'estado', 'observacion_admin'];

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
