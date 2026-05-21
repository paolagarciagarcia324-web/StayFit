<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class PlanModel extends StayFitModel
{
    protected string $table = 'plan';
    protected string $primaryKey = 'id_plan';
    protected array $fillable = ['nombre', 'descripcion', 'precio', 'duracion_dias', 'modalidad', 'requiere_coach', 'incluye_entrenamiento', 'incluye_nutricion', 'incluye_videos', 'incluye_sesiones', 'estado_plan'];

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
