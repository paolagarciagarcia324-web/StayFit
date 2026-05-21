<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class ProgresoModel extends StayFitModel
{
    protected string $table = 'registro_progreso';
    protected string $primaryKey = 'id_registro_progreso';
    protected array $fillable = ['id_plan_cliente', 'fecha', 'peso', 'cintura', 'cadera', 'brazos', 'piernas', 'fotos_evolucion', 'observacion_cliente', 'observacion_coach'];

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
