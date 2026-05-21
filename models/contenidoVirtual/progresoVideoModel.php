<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class ProgresoVideoModel extends StayFitModel
{
    protected string $table = 'progreso_video';
    protected string $primaryKey = 'id_progreso_video';
    protected array $fillable = ['id_cliente', 'id_video', 'estado', 'porcentaje_avance', 'fecha_inicio', 'fecha_finalizacion', 'ultimo_acceso'];

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
