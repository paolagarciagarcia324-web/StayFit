<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class AccesoModel extends StayFitModel
{
    protected string $table = 'acceso_cliente_modulo';
    protected string $primaryKey = 'id_acceso_cliente_modulo';
    protected array $fillable = ['id_plan_cliente', 'id_modulo_servicio', 'habilitado', 'fecha_habilitacion', 'fecha_expiracion'];

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
